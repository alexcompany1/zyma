<?php
/**
 * language_selector.php
 * Selector de idioma en esquina inferior derecha con estilos Zyma.
 */
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '.' || $basePath === '') {
  $basePath = '';
}
?>
<script src="assets/translations.js?v=20260512-6"></script>
<script src="assets/lang-data.js?v=20260512-6"></script>

<div class="zyma-lang-selector" id="zymaLangSelector">
  <button class="zyma-lang-toggle" id="zymaLangToggle" aria-label="Cambiar idioma">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
    <span id="zymaLangCurrent">ES</span>
  </button>
  <div class="zyma-lang-dropdown" id="zymaLangDropdown">
    <button class="zyma-lang-option" data-lang="es">🇪🇸 Español</button>
    <button class="zyma-lang-option" data-lang="en">🇬🇧 English</button>
    <button class="zyma-lang-option" data-lang="fr">🇫🇷 Français</button>
    <button class="zyma-lang-option" data-lang="ca">🇦🇸 Català</button>
    <button class="zyma-lang-option" data-lang="de">🇩🇪 Deutsch</button>
    <button class="zyma-lang-option" data-lang="it">🇮🇹 Italiano</button>
  </div>
</div>

<style>
/* Transición fluida para cambio de idioma */
html {
  transition: opacity 0.15s ease;
}

.zyma-lang-selector {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

.zyma-lang-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #45050C, #720E07);
  color: #EECF6D;
  border: 2px solid rgba(238, 207, 109, 0.3);
  padding: 10px 16px;
  border-radius: 999px;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 4px 16px rgba(69, 5, 12, 0.25);
}

.zyma-lang-toggle:hover {
  background: linear-gradient(135deg, #5f0a12, #8a1a12);
  border-color: rgba(238, 207, 109, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(69, 5, 12, 0.35);
}

.zyma-lang-toggle svg {
  flex-shrink: 0;
}

.zyma-lang-dropdown {
  position: absolute;
  bottom: calc(100% + 8px);
  right: 0;
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(69, 5, 12, 0.08);
  padding: 6px;
  min-width: 160px;
  display: none;
  flex-direction: column;
  gap: 2px;
}

.zyma-lang-dropdown.open {
  display: flex;
  animation: zymaLangFadeIn 0.2s ease;
}

@keyframes zymaLangFadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.zyma-lang-option {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  padding: 10px 14px;
  border-radius: 8px;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.85rem;
  font-weight: 500;
  color: #2a1b1b;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.zyma-lang-option:hover {
  background: #fdfbf9;
  color: #720E07;
}

.zyma-lang-option.active {
  background: rgba(238, 207, 109, 0.15);
  color: #45050C;
  font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var toggle = document.getElementById('zymaLangToggle');
  var dropdown = document.getElementById('zymaLangDropdown');
  var current = document.getElementById('zymaLangCurrent');
  var options = document.querySelectorAll('.zyma-lang-option');
  var pendingLang = null;

  function saveFallback(lang) {
    try {
      localStorage.setItem('zyma_lang', lang);
    } catch (e) {}
    document.cookie = 'zyma_lang=' + encodeURIComponent(lang) + '; path=/; max-age=31536000; SameSite=Lax';
  }

  function updateUI() {
    var lang = window.ZymaLang ? window.ZymaLang.get() : (pendingLang || localStorage.getItem('zyma_lang') || 'es');
    var labels = { es: 'ES', en: 'EN', fr: 'FR', ca: 'CA', de: 'DE', it: 'IT' };
    if (current) current.textContent = labels[lang] || 'ES';
    options.forEach(function(opt) {
      opt.classList.toggle('active', opt.getAttribute('data-lang') === lang);
    });
  }

  function applyLanguageWithTransition(lang) {
    // Agregar efecto de transición visual
    var html = document.documentElement;
    html.style.opacity = '0.8';
    html.style.transition = 'opacity 0.15s ease';
    
    // Aplicar idioma
    pendingLang = lang;
    saveFallback(lang);
    if (window.ZymaLang) {
      window.ZymaLang.set(lang);
    } else {
      var tries = 0;
      var retry = setInterval(function() {
        tries++;
        if (window.ZymaLang) {
          clearInterval(retry);
          window.ZymaLang.set(lang);
        }
        if (tries > 20) clearInterval(retry);
      }, 50);
    }
    
    updateUI();
    dropdown.classList.remove('open');
    
    // Restaurar opacidad
    setTimeout(function() {
      html.style.opacity = '1';
    }, 150);
  }

  if (toggle) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      dropdown.classList.toggle('open');
    });
  }

  options.forEach(function(opt) {
    opt.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var lang = this.getAttribute('data-lang');
      applyLanguageWithTransition(lang);
    });
  });

  document.addEventListener('zyma:language-change', updateUI);
  document.addEventListener('zyma:language-applied', updateUI);

  document.addEventListener('click', function(e) {
    if (!document.getElementById('zymaLangSelector').contains(e.target)) {
      dropdown.classList.remove('open');
    }
  });

  updateUI();
});
</script>
