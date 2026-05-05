<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: fidelidad.php');
    exit;
}

$userId = $_SESSION['user_id'];
$rewardId = (int)($_POST['reward_id'] ?? 0);

$pdo = getPDO();

// Obtener recompensa
$stmt = $pdo->prepare("SELECT * FROM loyalt_rewards WHERE id = ? AND activo = 1");
$stmt->execute([$rewardId]);
$reward = $stmt->fetch();

if (!$reward) {
    $_SESSION['error'] = 'Recompensa no válida.';
    header('Location: fidelidad.php');
    exit;
}

// Obtener puntos actuales
$stmt = $pdo->prepare("SELECT puntos FROM loyalt_users WHERE id_usuario = ?");
$stmt->execute([$userId]);
$loyalty = $stmt->fetch();

if (!$loyalty || $loyalty['puntos'] < $reward['puntos_necesarios']) {
    $_SESSION['error'] = 'No tienes suficientes puntos.';
    header('Location: fidelidad.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Restar puntos
    $stmt = $pdo->prepare("UPDATE loyalt_users SET puntos = puntos - ?, total_canjeado = total_canjeado + ? WHERE id_usuario = ?");
    $stmt->execute([$reward['puntos_necesarios'], $reward['descuento_eur'] ?? 0, $userId]);

    // Registrar transacción
    $desc = $reward['descuento_eur'] 
        ? "Canje: {$reward['nombre']} (-{$reward['descuento_eur']}€)"
        : "Canje: {$reward['nombre']}";
    
    $stmt = $pdo->prepare("INSERT INTO loyalt_transactions (id_usuario, tipo, puntos, concepto, monto) VALUES (?, 'canjeado', ?, ?, ?)");
    $stmt->execute([$userId, -$reward['puntos_necesarios'], $desc, $reward['descuento_eur']]);

    // Si es producto gratis, añadir al carrito
    if ($reward['producto_gratis_id']) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][] = [
            'id' => $reward['producto_gratis_id'],
            'quantity' => 1,
            'extras' => [],
            'is_free' => true,
            'reward_id' => $rewardId
        ];
    }

    // Si es descuento, guardar en sesión
    if ($reward['descuento_eur']) {
        $_SESSION['loyalty_discount'] = $reward['descuento_eur'];
    }

    $pdo->commit();
    $_SESSION['success'] = 'Recompensa canjeada correctamente.';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error al procesar la recompensa.';
}

header('Location: fidelidad.php');
exit;
