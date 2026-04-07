<?php
$show_cookie_popup = !empty($show_cookie_popup);
$cookie_preferences = $cookie_preferences ?? [];
$cookie_analytics_default = !empty($cookie_preferences['analytics']);
$cookie_marketing_default = !empty($cookie_preferences['marketing']);
$cookie_policy_version = isset($cookie_preferences['policy_version']) ? (string)$cookie_preferences['policy_version'] : '';
?>
<div
  class="cookie-modal-overlay"
  id="cookieModal"
  data-force-show="<?= $show_cookie_popup ? '1' : '0' ?>"
  data-policy-version="<?= htmlspecialchars($cookie_policy_version) ?>"
  data-endpoint="cookie_consent_action.php"
  hidden
>
  <section class="cookie-modal-card" role="dialog" aria-modal="true" aria-labelledby="cookieModalTitle">
    <h2 id="cookieModalTitle">Cookies en Zyma</h2>
    <p>
      Usamos cookies propias y de terceros para mejorar tu experiencia, analizar el uso de la web y
      mostrar contenido personalizado relacionado con nuestro restaurante.
    </p>
    <p>
      Puedes aceptar todas, rechazar las opcionales o personalizar tu elección.
      Las cookies técnicas son necesarias para que la web funcione correctamente.
    </p>

    <div class="cookie-modal-actions">
      <button type="button" class="cookie-btn cookie-btn-primary" id="cookieAcceptAll">Aceptar todas</button>
      <button type="button" class="cookie-btn cookie-btn-dark" id="cookieRejectAll">Rechazar opcionales</button>
      <button type="button" class="cookie-btn cookie-btn-light" id="cookieCustomize">Personalizar</button>
    </div>

    <div class="cookie-customize" id="cookieCustomizePanel" hidden>
      <label class="cookie-switch-row">
        <span>Cookies técnicas (siempre activas)</span>
        <input type="checkbox" checked disabled>
      </label>
      <label class="cookie-switch-row">
        <span>Cookies analíticas</span>
        <input type="checkbox" id="cookieAnalytics" <?= $cookie_analytics_default ? 'checked' : '' ?>>
      </label>
      <label class="cookie-switch-row">
        <span>Cookies de marketing</span>
        <input type="checkbox" id="cookieMarketing" <?= $cookie_marketing_default ? 'checked' : '' ?>>
      </label>
      <button type="button" class="cookie-btn cookie-btn-primary" id="cookieSaveCustom">Guardar preferencias</button>
    </div>

    <nav class="cookie-legal-links" aria-label="Enlaces legales">
      <a href="politica_cookies.php">Política de Cookies</a>
      <a href="politica_privacidad.php">Política de Privacidad</a>
      <a href="aviso_legal.php">Aviso Legal</a>
    </nav>
  </section>
</div>
<script src="assets/cookie-consent.js?v=20260317-5"></script>

