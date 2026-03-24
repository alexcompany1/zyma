<?php
if (!defined('ZYMA_COOKIE_POLICY_VERSION')) {
    define('ZYMA_COOKIE_POLICY_VERSION', '2026-03-17');
}

function ensureCookieConsentTable(PDO $pdo): void
{
    $sqlWithFk = "
        CREATE TABLE IF NOT EXISTS cookie_consentimientos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            policy_version VARCHAR(32) NOT NULL,
            estado ENUM('accepted','customized','rejected') NOT NULL,
            essential TINYINT(1) NOT NULL DEFAULT 1,
            analytics TINYINT(1) NOT NULL DEFAULT 0,
            marketing TINYINT(1) NOT NULL DEFAULT 0,
            consented_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_cookie_user (id_usuario),
            KEY idx_cookie_policy (policy_version),
            CONSTRAINT fk_cookie_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $sqlWithoutFk = "
        CREATE TABLE IF NOT EXISTS cookie_consentimientos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            policy_version VARCHAR(32) NOT NULL,
            estado ENUM('accepted','customized','rejected') NOT NULL,
            essential TINYINT(1) NOT NULL DEFAULT 1,
            analytics TINYINT(1) NOT NULL DEFAULT 0,
            marketing TINYINT(1) NOT NULL DEFAULT 0,
            consented_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_cookie_user (id_usuario),
            KEY idx_cookie_policy (policy_version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    try {
        $pdo->exec($sqlWithFk);
    } catch (Exception $e) {
        // Fallback para instalaciones antiguas o tablas de usuarios sin FK compatibles.
        $pdo->exec($sqlWithoutFk);
    }
}

function getCookieConsentByUser(PDO $pdo, int $userId): ?array
{
    ensureCookieConsentTable($pdo);

    $stmt = $pdo->prepare("SELECT * FROM cookie_consentimientos WHERE id_usuario = :id_usuario LIMIT 1");
    $stmt->execute([':id_usuario' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function shouldShowCookiePopup(PDO $pdo, int $userId): bool
{
    $consent = getCookieConsentByUser($pdo, $userId);
    if (!$consent) {
        return true;
    }

    $samePolicyVersion = ($consent['policy_version'] ?? '') === ZYMA_COOKIE_POLICY_VERSION;
    if (!$samePolicyVersion) {
        return true;
    }

    $estado = $consent['estado'] ?? '';

    return $estado === 'rejected' && !$samePolicyVersion;;
}

function saveCookieConsent(PDO $pdo, int $userId, string $estado, bool $analytics, bool $marketing): void
{
    ensureCookieConsentTable($pdo);

    $stmt = $pdo->prepare(
        "INSERT INTO cookie_consentimientos (id_usuario, policy_version, estado, essential, analytics, marketing, consented_at)
         VALUES (:id_usuario, :policy_version, :estado, 1, :analytics, :marketing, NOW())
         ON DUPLICATE KEY UPDATE
            policy_version = VALUES(policy_version),
            estado = VALUES(estado),
            essential = 1,
            analytics = VALUES(analytics),
            marketing = VALUES(marketing),
            consented_at = NOW()"
    );

    $stmt->execute([
        ':id_usuario' => $userId,
        ':policy_version' => ZYMA_COOKIE_POLICY_VERSION,
        ':estado' => $estado,
        ':analytics' => $analytics ? 1 : 0,
        ':marketing' => $marketing ? 1 : 0,
    ]);
}
