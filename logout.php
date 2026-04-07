<?php
/**
 * logout.php
 * Cierra la Sesión y vuelve al inicio.
 */

session_start();
require_once 'auth.php';
zymaClearAuthenticatedUser();
session_destroy();
header('Location: index.php?msg=logout');
exit;
?>
