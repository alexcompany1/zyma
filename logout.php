<?php
/**
 * logout.php
 * Cierra la sesion y vuelve al inicio.
 */

session_start();
session_destroy();
header('Location: index.php?msg=logout');
exit;
?>
