<?php
/**
 * config.php
 * Configuracion de base de datos y conexion PDO.
 */

ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

$host = getenv('DB_HOST') ?: 'sql202.infinityfree.com';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'if0_41600982_zyma'; // Reemplaza con el nombre real de tu base de datos en InfinityFree
$username = getenv('DB_USER') ?: 'if0_41600982';
$password = getenv('DB_PASSWORD') ?: 'MRkaAubL6DN';

$stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: '';
$stripePublishableKey = getenv('STRIPE_PUBLISHABLE_KEY') ?: '';
$stripeCurrency = getenv('STRIPE_CURRENCY') ?: 'eur';
$bizumPhone = getenv('BIZUM_PHONE') ?: '722618318';
$bizumBeneficiary = getenv('BIZUM_BENEFICIARY') ?: 'Zyma';
$bizumConceptPrefix = getenv('BIZUM_CONCEPT_PREFIX') ?: 'Pedido Zyma';

$localConfigPath = __DIR__ . DIRECTORY_SEPARATOR . 'config.local.php';
$isProduction = isset($_SERVER['SERVER_NAME']) && (strpos($_SERVER['SERVER_NAME'], 'wuaze') !== false || strpos($_SERVER['SERVER_NAME'], 'infinityfree') !== false);
if (is_file($localConfigPath) && !$isProduction) {
    require $localConfigPath;
}

define('TAX_RATE', 0.10);

if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', trim($stripeSecretKey));
}
$GLOBALS['stripeSecretKey'] = trim($stripeSecretKey);

if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', trim($stripePublishableKey));
}

if (!defined('STRIPE_CURRENCY')) {
    define('STRIPE_CURRENCY', getenv('STRIPE_CURRENCY') ?: $stripeCurrency ?: 'eur');
}
error_reporting(E_ALL);
define('BIZUM_PHONE', $bizumPhone);
define('BIZUM_BENEFICIARY', $bizumBeneficiary);
define('BIZUM_CONCEPT_PREFIX', $bizumConceptPrefix);

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    $message = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    die(
        "<h2 class='page-error'>Error de conexion a la base de datos</h2>" .
        "<p>Mensaje: $message</p>" .
        "<p>Comprueba en el panel de InfinityFree los datos de host, usuario, contrasena y nombre de base de datos.</p>"
    );
}
?>
