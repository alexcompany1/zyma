<?php

function zymaGetBaseUrl(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace('\\', '/', dirname($scriptName));
    $basePath = rtrim($basePath, '/.');

    return $scheme . '://' . $host . ($basePath !== '' ? $basePath : '');
}

function zymaGetNextId(PDO $pdo, string $table, string $column): int
{
    $allowed = [
        'pedidos' => 'id_pedido',
        'pedido_items' => 'id',
        'pagos' => 'id_pago',
    ];

    if (!isset($allowed[$table]) || $allowed[$table] !== $column) {
        throw new InvalidArgumentException('Tabla o columna no permitida para calcular IDs.');
    }

    $stmt = $pdo->query("SELECT COALESCE(MAX($column), 0) FROM $table");
    return (int) $stmt->fetchColumn() + 1;
}

function zymaCreateOrderFromCart(PDO $pdo, int $userId, array $cartItems, string $paymentMethod, string $notes = ''): array
{
    if (empty($cartItems)) {
        throw new RuntimeException('No hay productos en el carrito.');
    }

    $total = 0.0;
    foreach ($cartItems as $item) {
        $total += (float) $item['subtotal'];
    }

    $pdo->beginTransaction();

    try {
        $orderId = zymaGetNextId($pdo, 'pedidos', 'id_pedido');
        $insertOrder = $pdo->prepare(
            'INSERT INTO pedidos (id_pedido, id_mesa, id_usuario, fecha_hora, estado, total, metodo_pago, notas_cliente) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)'
        );
        $insertOrder->execute([
            $orderId,
            0,
            $userId,
            'pendiente',
            $total,
            $paymentMethod,
            $notes,
        ]);

        $insertItem = $pdo->prepare(
            'INSERT INTO pedido_items (id, id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)'
        );
        $nextItemId = zymaGetNextId($pdo, 'pedido_items', 'id');
        foreach ($cartItems as $item) {
            $insertItem->execute([
                $nextItemId++,
                $orderId,
                (int) $item['id'],
                (int) $item['quantity'],
                (float) $item['price'],
            ]);
        }

        $paymentId = zymaGetNextId($pdo, 'pagos', 'id_pago');
        $insertPayment = $pdo->prepare(
            'INSERT INTO pagos (id_pago, id_pedido, metodo, monto, estado, fecha_pago) VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $insertPayment->execute([
            $paymentId,
            $orderId,
            $paymentMethod,
            $total,
            'pendiente',
        ]);

        $pdo->commit();

        return [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'total' => $total,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function zymaCreateOrderNotification(PDO $pdo, int $userId, int $orderId, float $total): void
{
    $displayName = trim($_SESSION['nombre'] ?? '');
    if ($displayName === '') {
        $displayName = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? 'Cliente');
    }

    $message = 'Nuevo pedido #' . $orderId . ' de ' . $displayName . ' por ' . number_format($total, 2, ',', '.') . ' EUR';
    $stmt = $pdo->prepare('INSERT INTO notificaciones (id_usuario, mensaje, leida, fecha) VALUES (?, ?, 0, NOW())');
    $stmt->execute([$userId, $message]);
}

function zymaMarkPaymentStatus(PDO $pdo, int $orderId, string $status): void
{
    $allowed = ['pendiente', 'pagado', 'rechazado'];
    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Estado de pago no valido.');
    }

    $stmt = $pdo->prepare('UPDATE pagos SET estado = ? WHERE id_pedido = ?');
    $stmt->execute([$status, $orderId]);
}

function zymaUpdateOrderStatus(PDO $pdo, int $orderId, string $status): void
{
    $allowed = ['pendiente', 'preparando', 'listo', 'entregado', 'cancelado'];
    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Estado de pedido no valido.');
    }

    $stmt = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id_pedido = ?');
    $stmt->execute([$status, $orderId]);
}

function zymaGetOrderForUser(PDO $pdo, int $orderId, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT p.*, pg.estado AS pago_estado, pg.metodo AS pago_metodo, pg.monto AS pago_monto
         FROM pedidos p
         LEFT JOIN pagos pg ON pg.id_pedido = p.id_pedido
         WHERE p.id_pedido = ? AND p.id_usuario = ?
         LIMIT 1'
    );
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();

    return $order ?: null;
}

function zymaStripeRequest(string $method, string $path, array $payload = []): array
{
    if (STRIPE_SECRET_KEY === '') {
        throw new RuntimeException('Configura STRIPE_SECRET_KEY antes de usar pagos con tarjeta.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('La extension cURL de PHP es necesaria para conectar con Stripe.');
    }

    $ch = curl_init('https://api.stripe.com/v1' . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
    ]);

    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    }

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('No se pudo conectar con Stripe: ' . $error);
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Respuesta invalida de Stripe.');
    }

    if ($statusCode >= 400) {
        $message = $decoded['error']['message'] ?? 'Error desconocido al crear la sesion de pago.';
        throw new RuntimeException($message);
    }

    return $decoded;
}

function zymaCreateStripeCheckoutSession(array $cartItems, int $orderId): array
{
    $payload = [
        'mode' => 'payment',
        'success_url' => zymaGetBaseUrl() . '/stripe_return.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => zymaGetBaseUrl() . '/stripe_return.php?cancelled=1&order_id=' . $orderId,
        'client_reference_id' => (string) $orderId,
        'metadata[order_id]' => (string) $orderId,
        'metadata[integration]' => 'zyma_checkout',
        'currency' => STRIPE_CURRENCY,
    ];

    foreach (array_values($cartItems) as $index => $item) {
        $payload["line_items[$index][quantity]"] = (int) $item['quantity'];
        $payload["line_items[$index][price_data][currency]"] = STRIPE_CURRENCY;
        $payload["line_items[$index][price_data][unit_amount]"] = (int) round(((float) $item['price']) * 100);
        $payload["line_items[$index][price_data][product_data][name]"] = (string) $item['name'];
    }

    return zymaStripeRequest('POST', '/checkout/sessions', $payload);
}

function zymaRetrieveStripeCheckoutSession(string $sessionId): array
{
    return zymaStripeRequest('GET', '/checkout/sessions/' . rawurlencode($sessionId));
}
