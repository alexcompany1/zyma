<?php
$path = $_SERVER['SCRIPT_FILENAME'];

if (preg_match('/\.css$/', $path)) {
    header('Content-Type: text/css; charset=utf-8');
} elseif (preg_match('/\.js$/', $path)) {
    header('Content-Type: application/javascript; charset=utf-8');
} elseif (preg_match('/\.png$/', $path)) {
    header('Content-Type: image/png');
} elseif (preg_match('/\.jpg$/', $path) || preg_match('/\.jpeg$/', $path)) {
    header('Content-Type: image/jpeg');
} elseif (preg_match('/\.gif$/', $path)) {
    header('Content-Type: image/gif');
} elseif (preg_match('/\.html$/', $path)) {
    header('Content-Type: text/html; charset=utf-8');
} elseif (preg_match('/\.php$/', $path)) {
    return false; // Let PHP handle PHP files normally
}

return false;
