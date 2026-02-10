<?php
/**
 * config.php
 * Configuracion de base de datos y conexion PDO.
 */

// Host BD
$host = 'localhost';

// Puerto BD
$port = '3306';

// Nombre BD
$dbname = 'zyma';

// Usuario BD
$username = 'root';

// Password BD
$password = '';

try {
    // Crear conexion PDO
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", // DSN
        $username, // usuario
        $password, // password
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // errores con excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // fetch asociativo
            PDO::ATTR_EMULATE_PREPARES => false // prepared reales
        ]
    );
} catch (PDOException $e) {
    // Error de conexion
    die("<h2 class='page-error'>Error de conexión a la base de datos</h2>");
}
?>  
