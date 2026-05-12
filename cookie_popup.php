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
    <h2 id="cookieModalTitle" data-i18n="cookies.popupTitle">Cookies en Zyma</h2>
    <p data-i18n="cookies.popupIntro">
      Usamos cookies propias y de terceros para mejorar tu experiencia, analizar el uso de la web y
      mostrar contenido personalizado relacionado con nuestro restaurante.
    </p>
    <p data-i18n="cookies.popupSummary">
      Puedes aceptar todas, rechazar las opcionales o personalizar tu elección.
      Las cookies técnicas son necesarias para que la web funcione correctamente.
    </p>

    <div class="cookie-modal-actions">
      <button type="button" class="cookie-btn cookie-btn-primary" id="cookieAcceptAll" data-i18n="cookies.acceptAll">Aceptar todas</button>
      <button type="button" class="cookie-btn cookie-btn-dark" id="cookieRejectAll" data-i18n="cookies.rejectOptional">Rechazar opcionales</button>
      <button type="button" class="cookie-btn cookie-btn-light" id="cookieCustomize" data-i18n="cookies.customize">Personalizar</button>
    </div>

    <div class="cookie-customize" id="cookieCustomizePanel" hidden>
      <label class="cookie-switch-row">
        <span data-i18n="cookies.technicalAlways">Cookies técnicas (siempre activas)</span>
        <input type="checkbox" checked disabled>
      </label>
      <label class="cookie-switch-row">
        <span data-i18n="cookies.analyticsCookies">Cookies analíticas</span>
        <input type="checkbox" id="cookieAnalytics" <?= $cookie_analytics_default ? 'checked' : '' ?>>
      </label>
      <label class="cookie-switch-row">
        <span data-i18n="cookies.marketingCookies">Cookies de marketing</span>
        <input type="checkbox" id="cookieMarketing" <?= $cookie_marketing_default ? 'checked' : '' ?>>
      </label>
      <button type="button" class="cookie-btn cookie-btn-primary" id="cookieSaveCustom" data-i18n="cookies.savePreferences">Guardar preferencias</button>
    </div>

    <nav class="cookie-legal-links" aria-label="Enlaces legales" data-i18n-aria="cookies.legalLinksAria">
      <a href="politica_cookies.php" data-i18n="footer.cookiePolicy">Política de Cookies</a>
      <a href="politica_privacidad.php" data-i18n="footer.privacy">Política de Privacidad</a>
      <a href="aviso_legal.php" data-i18n="footer.legal">Aviso Legal</a>
    </nav>
  </section>
</div>
<script src="assets/cookie-consent.js?v=20260414-1"></script>

