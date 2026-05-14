<?php
/**
 * Debug de traducción
 */

// Simulación de LocalStorage
$lang = $_GET['lang'] ?? 'es';
$supported = ['es', 'en', 'fr', 'ca', 'de', 'it'];

if (!in_array($lang, $supported)) {
    $lang = 'es';
}

$_SESSION['debug_lang'] = $lang;

echo "<!DOCTYPE html>
<html lang='$lang'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Debug i18n - Idioma: $lang</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="shortcut icon" type="image/png" href="assets/favicon.png">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #333; }
        .status { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .test { background: #e8f4f8; padding: 10px; margin: 10px 0; border-left: 4px solid #0099cc; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
        .info { color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Debug - Sistema de Traducción i18n</h1>
        
        <div class='status'>
            <h3>Estado Actual</h3>
            <p><strong>Idioma Seleccionado:</strong> <span id='currentLang'>$lang</span></p>
            <p><strong>LocalStorage Lang:</strong> <span id='storageLang'>-</span></p>
            <p><strong>Cookie Lang:</strong> <span id='cookieLang'>-</span></p>
        </div>

        <h3>Cambiar Idioma (para pruebas):</h3>
        <div>";

foreach ($supported as $s) {
    $label = ['es' => 'Español', 'en' => 'English', 'fr' => 'Français', 'ca' => 'Català', 'de' => 'Deutsch', 'it' => 'Italiano'][$s] ?? $s;
    echo "<button onclick=\"window.ZymaLang?.set('$s') || alert('ZymaLang no cargado'); setTimeout(() => location.reload(), 500)\">$label ($s)</button>";
}

echo "        </div>

        <div class='test'>
            <h4 data-i18n='landing.kicker'>Hotdogs artesanales</h4>
            <h4 data-i18n='landing.tagline'>Zyma. Sabor con alma.</h4>
            <p data-i18n='auth.email'>Email</p>
            <p data-i18n='auth.password'>Contraseña</p>
            <p data-i18n='nav.home'>Inicio</p>
            <p data-i18n='nav.viewMenu'>Ver carta</p>
        </div>

        <h3>Variables de Debug:</h3>
        <pre id='debugInfo'></pre>
    </div>

    <script src='assets/translations.js'></script>
    <script>
        console.log('ZymaLang disponible:', !!window.ZymaLang);
        
        document.getElementById('storageLang').textContent = localStorage.getItem('zyma_lang') || 'no establecido';
        document.getElementById('cookieLang').textContent = document.cookie.match(/zyma_lang=([^;]*)/)?.[1] || 'no establecido';
        
        window.addEventListener('zyma:language-applied', function() {
            console.log('Evento zyma:language-applied disparado');
            document.getElementById('currentLang').textContent = window.ZymaLang?.get() || 'desconocido';
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Cargado');
            console.log('Idioma actual:', window.ZymaLang?.get());
            document.getElementById('debugInfo').textContent = JSON.stringify({
                zymaDefined: !!window.ZymaLang,
                currentLang: window.ZymaLang?.get(),
                storageValue: localStorage.getItem('zyma_lang'),
                dataI18nElements: document.querySelectorAll('[data-i18n]').length
            }, null, 2);
        });
    </script>
</body>
</html>";
?>
