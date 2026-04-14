<?php
/**
 * acceso_invitado.php
 * Activa modo invitado y redirige a la carta.
 */

session_start();
require_once 'auth.php';
zymaClearAuthenticatedUser();
$_SESSION['guest_mode'] = true;

header('Location: carta.php');
exit;
