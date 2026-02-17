<?php
/**
 * test_valoraciones.php
 * Script de test para verificar que no hay warnings en valoraciones.php
 */

// Contar los warnings/errors que aparecen
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir la página
ob_start();
include 'valoraciones.php';
$output = ob_get_clean();

// Verifica que no hay "Warning:" en la salida
if (strpos($output, 'Warning:') === false) {
    echo "✅ SUCCESS: No hay warnings en la página\n";
    exit(0);
} else {
    echo "❌ ERROR: Aún hay warnings detectados\n";
    echo $output;
    exit(1);
}
?>
