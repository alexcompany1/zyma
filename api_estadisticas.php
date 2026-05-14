<?php
header('Content-Type: application/json; charset=UTF-8');

session_start();
require_once 'config.php';
require_once 'auth.php';
zymaRequireRole('worker');

try {
    $stmt = $pdo->query("
        SELECT 
            id,
            nombre,
            cantidad,
            unidad,
            stock_minimo,
            CASE 
                WHEN cantidad <= stock_minimo THEN 'critico'
                WHEN cantidad <= stock_minimo * 1.5 THEN 'bajo'
                ELSE 'normal'
            END as nivel_stock
        FROM ingredientes 
        ORDER BY cantidad ASC, nombre ASC
    ");
    $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
    $pedidosPendientes = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('preparando', 'listo')");
    $pedidosEnProceso = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    $pedidosHoy = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    $ventasHoy = (float)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes WHERE cantidad <= stock_minimo");
    $ingredientesBajoStock = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes");
    $totalIngredientes = (int)$stmt->fetchColumn();

    $porcentajeStock = $totalIngredientes > 0 ? round((($totalIngredientes - $ingredientesBajoStock) / $totalIngredientes) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'pedidosPendientes' => $pedidosPendientes,
            'pedidosEnProceso' => $pedidosEnProceso,
            'pedidosHoy' => $pedidosHoy,
            'ventasHoy' => $ventasHoy,
            'ingredientesBajoStock' => $ingredientesBajoStock,
            'porcentajeStock' => $porcentajeStock,
            'ingredientes' => $ingredientes
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
