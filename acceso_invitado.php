<?php
/**
 * acceso_invitado.php
 * Activa modo invitado y redirige a la carta.
 */

session_start();
unset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['worker_code'], $_SESSION['nombre']);
$_SESSION['guest_mode'] = true;

header('Location: carta.php');
exit;

