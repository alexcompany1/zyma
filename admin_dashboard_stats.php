<?php
if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

session_start();
require_once 'config.php';
require_once 'admin_helpers.php';

requireRoles(['admin']);
createAdminSchema($pdo);

try {
    $stats = getDashboardStats($pdo);
    echo json_encode(['success' => true, 'data' => $stats], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
