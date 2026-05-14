<?php
header('Content-Type: application/json; charset=UTF-8');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once 'config.php';

$user_id = (int)$_SESSION['user_id'];
$notifIdCol = 'id';

try {
    $stmtCol = $pdo->query("
        SELECT COLUMN_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'notificaciones'
          AND COLUMN_NAME IN ('id', 'id_notificacion')
    ");
    $cols = $stmtCol->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('id', $cols, true)) {
        $notifIdCol = 'id';
    } elseif (in_array('id_notificacion', $cols, true)) {
        $notifIdCol = 'id_notificacion';
    }
} catch (Exception $e) {
    $notifIdCol = 'id';
}

$action = $_GET['action'] ?? '';

if ($action === 'read' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE notificaciones
            SET leida = 1
            WHERE {$notifIdCol} = :id AND id_usuario = :id_usuario
        ");
        $stmt->execute([':id' => $id, ':id_usuario' => $user_id]);
        echo json_encode(['ok' => true]);
        exit;
    }
}

if ($action === 'read_all') {
    $stmt = $pdo->prepare("
        UPDATE notificaciones
        SET leida = 1
        WHERE id_usuario = :id_usuario AND leida = 0
    ");
    $stmt->execute([':id_usuario' => $user_id]);
    echo json_encode(['ok' => true]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT {$notifIdCol} AS notif_id, mensaje, leida, fecha
    FROM notificaciones
    WHERE id_usuario = :id_usuario
    ORDER BY fecha DESC, {$notifIdCol} DESC
");
$stmt->execute([':id_usuario' => $user_id]);
$notificaciones = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM notificaciones
    WHERE id_usuario = :id_usuario AND leida = 0
");
$stmt->execute([':id_usuario' => $user_id]);
$unread_count = (int)$stmt->fetchColumn();

echo json_encode([
    'unread_count' => $unread_count,
    'notificaciones' => $notificaciones
]);
