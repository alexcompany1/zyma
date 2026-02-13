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

  document.addEventListener('DOMContentLoaded', () => {
    initMobileHeader();
    initQuickMenuDropdown();
  });
})();
