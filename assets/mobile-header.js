(() => {
  const hamburgerSvg = `
    <svg class="hamburger-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <line x1="4" y1="7" x2="20" y2="7"></line>
      <line x1="4" y1="12" x2="20" y2="12"></line>
      <line x1="4" y1="17" x2="20" y2="17"></line>
    </svg>
  `;
  const quickMenuSvg = `
    <svg class="quick-menu-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <line x1="4" y1="7" x2="20" y2="7"></line>
      <line x1="4" y1="12" x2="20" y2="12"></line>
      <line x1="4" y1="17" x2="20" y2="17"></line>
    </svg>
  `;

  function buildMenuLinks(bar) {
    const links = [];
    const seen = new Set();

    const addLink = (href, text) => {
      const key = `${href}|${text}`.trim();
      if (!href || !text || seen.has(key)) return;
      seen.add(key);
      links.push({ href, text });
    };

    const isPrivateHeader = !!bar.querySelector('.profile-section');

    if (isPrivateHeader) {
      addLink('usuario.php', 'Inicio');
      addLink('carta.php', 'Carta');
      addLink('carrito.php', 'Carrito');
      addLink('perfil.php', 'Mi perfil');
      addLink('notificaciones.php', 'Notificaciones');
      addLink('politica_cookies.php', 'Personalizar cookies');
      addLink('logout.php', 'Cerrar sesión');
      return links;
    }

    const existingActions = bar.querySelectorAll('.landing-actions a');
    existingActions.forEach((a) => addLink(a.getAttribute('href'), a.textContent.trim()));

    const logo = bar.querySelector('.landing-logo');
    if (logo) addLink(logo.getAttribute('href'), 'Inicio');

    return links;
  }

  function ensureDrawer(bar) {
    let drawer = bar.querySelector('.landing-actions');
    if (drawer) {
      drawer.classList.add('mobile-drawer');
      return drawer;
    }

    drawer = document.createElement('div');
    drawer.className = 'landing-actions mobile-drawer mobile-generated';
    const links = buildMenuLinks(bar);
    if (links.length === 0) return null;

    links.forEach((item) => {
      const a = document.createElement('a');
      a.href = item.href;
      a.textContent = item.text;
      a.className = item.text.toLowerCase().includes('crear') ? 'landing-cta' : 'landing-link';
      drawer.appendChild(a);
    });

    bar.appendChild(drawer);
    return drawer;
  }

  function initMobileHeader() {
    const bar = document.querySelector('.landing-bar');
    if (!bar) return;

    bar.classList.add('mobile-ready');

    let hamburger = bar.querySelector('.hamburger-btn');
    if (!hamburger) {
      hamburger = document.createElement('button');
      hamburger.type = 'button';
      hamburger.className = 'hamburger-btn';
      hamburger.setAttribute('aria-label', 'Abrir menu');
      hamburger.innerHTML = hamburgerSvg;
      bar.appendChild(hamburger);
    } else if (!hamburger.querySelector('.hamburger-icon')) {
      hamburger.innerHTML = hamburgerSvg;
    }

    const drawer = ensureDrawer(bar);
    if (!drawer) return;

    let overlay = document.querySelector('.menu-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'menu-overlay';
      document.body.appendChild(overlay);
    }

    const closeMenu = () => {
      hamburger.classList.remove('active');
      drawer.classList.remove('active');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    };

    const openMenu = () => {
      hamburger.classList.add('active');
      drawer.classList.add('active');
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    };

    hamburger.addEventListener('click', () => {
      if (drawer.classList.contains('active')) {
        closeMenu();
      } else {
        openMenu();
      }
    });

    overlay.addEventListener('click', closeMenu);
    drawer.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));
    window.addEventListener('resize', () => {
      if (window.innerWidth > 768) closeMenu();
    });
  }

  function initQuickMenuDropdown() {
    const quickMenuBtn = document.getElementById('quickMenuBtn');
    const quickDropdown = document.getElementById('quickDropdown');
    if (!quickMenuBtn || !quickDropdown) return;

    if (!quickMenuBtn.querySelector('.quick-menu-icon')) {
      quickMenuBtn.innerHTML = quickMenuSvg;
    }

    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    quickMenuBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      quickDropdown.classList.toggle('show');
      if (dropdownMenu) dropdownMenu.classList.remove('show');
    });

    window.addEventListener('click', (e) => {
      if (!quickMenuBtn.contains(e.target) && !quickDropdown.contains(e.target)) {
        quickDropdown.classList.remove('show');
      }
      if (profileBtn && dropdownMenu && !profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
      }
    });
  }

  const translations = {
    'Idioma': { en: 'Language', fr: 'Langue' },
    'Iniciar Sesión': { en: 'Sign In', fr: 'Se connecter' },
    'Email': { en: 'Email', fr: 'Email' },
    'Contraseña': { en: 'Password', fr: 'Mot de passe' },
    'Mostrar contraseña': { en: 'Show password', fr: 'Afficher le mot de passe' },
    'Ocultar contraseña': { en: 'Hide password', fr: 'Masquer le mot de passe' },
    'Código de trabajador (opcional)': { en: 'Worker Code (optional)', fr: 'Code employé (optionnel)' },
    'Trabajador: ej. TRAB001': { en: 'Worker: e.g. TRAB001', fr: 'Employé : ex. TRAB001' },
    'Administrador: ADMIN': { en: 'Administrator: ADMIN', fr: 'Administrateur : ADMIN' },
    'Crear cuenta': { en: 'Create account', fr: 'Créer un compte' },
    'No tienes cuenta? Registrate': { en: "Don't have an account? Register", fr: "Vous n'avez pas de compte ? Inscrivez-vous" },
    'He olvidado la Contraseña': { en: 'I forgot my password', fr: 'J’ai oublié mon mot de passe' },
    'Política de Cookies': { en: 'Cookie Policy', fr: 'Politique de cookies' },
    'Política de Privacidad': { en: 'Privacy Policy', fr: 'Politique de confidentialité' },
    'Aviso Legal': { en: 'Legal Notice', fr: 'Mentions légales' },
    'Tu Contraseña se ha actualizado. Ya puedes iniciar Sesión.': { en: 'Your password has been updated. You can now sign in.', fr: 'Votre mot de passe a été mis à jour. Vous pouvez maintenant vous connecter.' },
    'Has rechazado las cookies. Puedes seguir usando Zyma': { en: 'You have rejected cookies. You can continue using Zyma.', fr: 'Vous avez refusé les cookies. Vous pouvez continuer à utiliser Zyma.' },
    'Credenciales incorrectas.': { en: 'Incorrect credentials.', fr: 'Identifiants incorrects.' },
    'El email y la Contraseña son obligatorios.': { en: 'Email and password are required.', fr: 'Email et mot de passe sont obligatoires.' },
    'Tu cuenta está bloqueada. Contacta con administración.': { en: 'Your account is locked. Contact administration.', fr: 'Votre compte est bloqué. Contactez l’administration.' },
    'Código de trabajador incorrecto.': { en: 'Worker code is incorrect.', fr: 'Le code employé est incorrect.' },
    'Error interno del sistema.': { en: 'Internal system error.', fr: 'Erreur interne du système.' },
    'Cookies en Zyma': { en: 'Cookies on Zyma', fr: 'Cookies sur Zyma' },
    'Usamos cookies propias y de terceros para mejorar tu experiencia, analizar el uso de la web y mostrar contenido personalizado relacionado con nuestro restaurante.': { en: 'We use first-party and third-party cookies to improve your experience, analyze site usage and show personalized restaurant-related content.', fr: 'Nous utilisons des cookies internes et tiers pour améliorer votre expérience, analyser l’utilisation du site et afficher du contenu personnalisé lié à notre restaurant.' },
    'Puedes aceptar todas, rechazar las opcionales o personalizar tu elección. Las cookies técnicas son necesarias para que la web funcione correctamente.': { en: 'You can accept all, reject optional cookies or customize your selection. Technical cookies are required for the website to work correctly.', fr: 'Vous pouvez tout accepter, refuser les cookies optionnels ou personnaliser votre choix. Les cookies techniques sont nécessaires au bon fonctionnement du site.' },
    'Aceptar todas': { en: 'Accept all', fr: 'Accepter tout' },
    'Rechazar opcionales': { en: 'Reject optional', fr: 'Refuser les optionnels' },
    'Personalizar': { en: 'Customize', fr: 'Personnaliser' },
    'Cookies técnicas (siempre activas)': { en: 'Technical cookies (always active)', fr: 'Cookies techniques (toujours actifs)' },
    'Cookies analíticas': { en: 'Analytical cookies', fr: 'Cookies analytiques' },
    'Cookies de marketing': { en: 'Marketing cookies', fr: 'Cookies marketing' },
    'Guardar preferencias': { en: 'Save preferences', fr: 'Enregistrer les préférences' },
    'Preferencias guardadas correctamente': { en: 'Preferences saved successfully', fr: 'Préférences enregistrées avec succès' },
    'Has rechazado las cookies opcionales.': { en: 'You have rejected optional cookies.', fr: 'Vous avez refusé les cookies optionnels.' },
    'Mi perfil': { en: 'My profile', fr: 'Mon profil' },
    'Cerrar Sesión': { en: 'Sign out', fr: 'Déconnexion' },
    'Inicio': { en: 'Home', fr: 'Accueil' },
    'Carta': { en: 'Menu', fr: 'Menu' },
    'Carrito': { en: 'Cart', fr: 'Panier' },
    'Notificaciones': { en: 'Notifications', fr: 'Notifications' },
    'Personalizar cookies': { en: 'Customize cookies', fr: 'Personnaliser les cookies' },
    'Menú': { en: 'Menu', fr: 'Menu' },
    'Usuario': { en: 'User', fr: 'Utilisateur' },
  };

  function getSavedLanguage() {
    return window.localStorage.getItem('zymaLanguage') || 'es';
  }

  function translateOriginalText(value, lang) {
    const original = value.trim();
    const translation = translations[original] && translations[original][lang];
    return translation || (lang === 'es' ? original : null);
  }

  function translateElement(el, lang) {
    if (el.children.length > 0) {
      return;
    }

    const original = el.dataset.i18nOriginalText || el.textContent.trim();
    if (!original) {
      return;
    }
    if (!el.dataset.i18nOriginalText) {
      el.dataset.i18nOriginalText = original;
    }

    const translated = translateOriginalText(original, lang);
    if (translated) {
      el.textContent = translated;
    } else if (lang === 'es') {
      el.textContent = original;
    }
  }

  function translateAttributes(el, lang) {
    ['placeholder', 'title', 'aria-label', 'alt'].forEach((attribute) => {
      if (!el.hasAttribute(attribute)) {
        return;
      }
      const original = el.dataset[`i18nOriginal${attribute}`] || el.getAttribute(attribute);
      if (!original) {
        return;
      }
      if (!el.dataset[`i18nOriginal${attribute}`]) {
        el.dataset[`i18nOriginal${attribute}`] = original;
      }
      const translated = translateOriginalText(original, lang);
      if (translated) {
        el.setAttribute(attribute, translated);
      } else if (lang === 'es') {
        el.setAttribute(attribute, original);
      }
    });
  }

  function translateTextNodes(lang) {
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
    let node;
    while ((node = walker.nextNode())) {
      const originalText = node._i18nOriginalText || node.nodeValue.trim();
      if (!originalText) {
        continue;
      }

      if (node._i18nOriginalText === undefined) {
        node._i18nOriginalText = originalText;
      }

      const translated = translateOriginalText(originalText, lang);
      if (translated) {
        node.nodeValue = node.nodeValue.replace(originalText, translated);
      } else if (lang === 'es') {
        node.nodeValue = node.nodeValue.replace(node.nodeValue.trim(), originalText);
      }
    }
  }

  function translatePage(lang) {
    document.documentElement.lang = lang;
    const elements = document.querySelectorAll('body *');
    elements.forEach((el) => {
      if (el.matches('script, style, textarea, select, option')) {
        return;
      }
      translateAttributes(el, lang);

      if (el.tagName === 'INPUT') {
        if (el.type === 'submit' || el.type === 'button') {
          const originalValue = el.dataset.i18nOriginalValue || el.value;
          if (!el.dataset.i18nOriginalValue) {
            el.dataset.i18nOriginalValue = originalValue;
          }
          const translated = translateOriginalText(originalValue, lang);
          if (translated) {
            el.value = translated;
          } else if (lang === 'es') {
            el.value = originalValue;
          }
        }
        return;
      }

      if (el.tagName === 'BUTTON') {
        translateElement(el, lang);
        return;
      }

      if (el.children.length === 0) {
        translateElement(el, lang);
      }
    });

    translateTextNodes(lang);

    const titleTranslation = translateOriginalText(document.title, lang);
    if (titleTranslation) {
      document.title = titleTranslation;
    }
  }

  function createFloatingLanguageSwitcher() {
    if (document.getElementById('languageFloatingSwitcher')) {
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.id = 'languageFloatingSwitcher';
    wrapper.className = 'language-floating-switcher';
    wrapper.innerHTML = `
      <button type="button" class="language-floating-toggle" aria-label="Cambiar idioma">ES</button>
      <div class="language-floating-menu" aria-label="Seleccionar idioma">
        <button type="button" data-lang="es">ES</button>
        <button type="button" data-lang="en">EN</button>
        <button type="button" data-lang="fr">FR</button>
      </div>
    `;

    document.body.appendChild(wrapper);

    const toggle = wrapper.querySelector('.language-floating-toggle');
    const menu = wrapper.querySelector('.language-floating-menu');
    const buttons = wrapper.querySelectorAll('button[data-lang]');

    toggle.addEventListener('click', (event) => {
      event.stopPropagation();
      menu.classList.toggle('active');
    });

    buttons.forEach((button) => {
      button.addEventListener('click', (event) => {
        event.stopPropagation();
        const lang = button.dataset.lang;
        setLanguage(lang);
        menu.classList.remove('active');
      });
    });

    window.addEventListener('click', () => {
      menu.classList.remove('active');
    });
  }

  function createHeaderLanguageSwitcher() {
    const bar = document.querySelector('.landing-bar');
    const actions = bar ? bar.querySelector('.landing-actions') : null;
    if (!actions || actions.querySelector('.language-switcher')) {
      return;
    }

    const switcher = document.createElement('div');
    switcher.className = 'language-switcher';
    switcher.innerHTML = '<span class="language-switcher-label">Idioma</span>';

    ['es', 'en', 'fr'].forEach((lang) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'language-switcher-btn';
      button.dataset.lang = lang;
      button.textContent = lang.toUpperCase();
      button.addEventListener('click', () => {
        setLanguage(lang);
      });
      switcher.appendChild(button);
    });

    actions.insertBefore(switcher, actions.firstChild);
  }

  function updateLanguageButtons(activeLang) {
    document.querySelectorAll('.language-switcher-btn, #languageFloatingSwitcher button[data-lang]').forEach((button) => {
      button.classList.toggle('active', button.dataset.lang === activeLang);
    });
    const floating = document.querySelector('.language-floating-toggle');
    if (floating) {
      floating.textContent = activeLang.toUpperCase();
    }
  }

  function setLanguage(lang, silent) {
    window.localStorage.setItem('zymaLanguage', lang);
    updateLanguageButtons(lang);
    if (!silent) {
      translatePage(lang);
    }
    document.documentElement.lang = lang;
  }

  function initLanguageSwitcher() {
    createHeaderLanguageSwitcher();
    createFloatingLanguageSwitcher();
    const savedLang = getSavedLanguage();
    translatePage(savedLang);
    updateLanguageButtons(savedLang);
  }

  function initAll() {
    initMobileHeader();
    initQuickMenuDropdown();
    initLanguageSwitcher();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
