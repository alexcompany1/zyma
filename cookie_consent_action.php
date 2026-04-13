<?php
ob_start();

session_start();
require_once 'config.php';
require_once 'cookie_consent_helper.php';

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

function jsonResponse(int $status, array $payload): void
{
    if (ob_get_length() > 0) {
        ob_clean();
    }

    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['ok' => false, 'message' => 'Método no permitido']);
}

if (empty($_SESSION['user_id'])) {
    jsonResponse(401, ['ok' => false, 'message' => 'Sesión no válida']);
}

$action = (string)($_POST['action'] ?? '');
$userId = (int)($_SESSION['user_id'] ?? 0);

try {
    if ($action === 'accept_all') {
        saveCookieConsent($pdo, $userId, 'accepted', true, true);
        $_SESSION['cookie_preferences'] = [
            'analytics' => true,
            'marketing' => true,
            'policy_version' => ZYMA_COOKIE_POLICY_VERSION,
            'estado' => 'accepted',
        ];
        $_SESSION['show_cookie_popup'] = false;
        jsonResponse(200, ['ok' => true, 'action' => 'accept_all', 'message' => 'Preferencias guardadas correctamente']);
    }

    if ($action === 'save_custom') {
        $analytics = !empty($_POST['analytics']);
        $marketing = !empty($_POST['marketing']);

        saveCookieConsent($pdo, $userId, 'customized', $analytics, $marketing);
        $_SESSION['cookie_preferences'] = [
            'analytics' => $analytics,
            'marketing' => $marketing,
            'policy_version' => ZYMA_COOKIE_POLICY_VERSION,
            'estado' => 'customized',
        ];
        $_SESSION['show_cookie_popup'] = false;
        jsonResponse(200, ['ok' => true, 'action' => 'save_custom', 'message' => 'Personalización guardada correctamente']);
    }

    if ($action === 'reject_all') {
        saveCookieConsent($pdo, $userId, 'rejected', false, false);

        $_SESSION['cookie_preferences'] = [
            'analytics' => false,
            'marketing' => false,
            'policy_version' => ZYMA_COOKIE_POLICY_VERSION,
            'estado' => 'rejected',
        ];
        $_SESSION['show_cookie_popup'] = false;

        jsonResponse(200, [
            'ok' => true,
            'action' => 'reject_all',
            'message' => 'Has rechazado las cookies opcionales. '
        ]);
    }

    jsonResponse(400, ['ok' => false, 'message' => 'Acción no válida']);
} catch (Exception $e) {
    jsonResponse(500, ['ok' => false, 'message' => 'No se pudo guardar la preferencia']);
}
