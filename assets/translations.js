(function() {
  'use strict';

  var translations = {
    es: {
      siteName: 'Zyma',
      nav: { home: 'Inicio', viewMenu: 'Ver carta', reviews: 'Valoraciones', incidents: 'Incidencias', tickets: 'Tickets de compra', myProfile: 'Mi perfil', customizeCookies: 'Personalizar cookies', logout: 'Cerrar Sesion' },
      auth: { welcomeBack: 'Bienvenido de nuevo', loginSubtitle: 'Inicia sesion para acceder a tu cuenta', loginBtn: 'Iniciar Sesion', createAccount: 'Crear cuenta', createAccountSubtitle: 'Unete a Zyma y disfruta de nuestros productos', registerBtn: 'Crear cuenta', alreadyHaveAccount: 'Ya tienes cuenta? Inicia sesion', noAccount: 'No tienes cuenta? Registrate', forgotPassword: 'He olvidado la contrasena', email: 'Email', password: 'Contrasena', confirmPassword: 'Confirmar contrasena', workerCode: 'Codigo de trabajador o administrador', workerCodePlaceholder: 'Ej: TRAB001 o ADMIN', workerCodeOptional: 'Codigo de trabajador (opcional)', workerCodeHint: 'Si lo tienes, accederas a funciones especiales', passwordMin: 'Minimo 6 caracteres.', emailRequired: 'Email', passwordRequired: 'Contrasena', required: '*' },
      landing: { tagline: 'Zyma. Sabor con alma.', kicker: 'Hotdogs artesanales', heroText: 'Recetas de la casa, ingredientes frescos y un sabor que no se olvida. Crea tu cuenta o inicia sesion y descubre nuestra carta.', createAccount: 'Crear cuenta', loginBtn: 'Ya tengo cuenta', guestMode: 'Ver carta sin cuenta', freshIngredients: 'Ingredientes frescos', avgRating: 'Valoracion media', fastOrders: 'Pedidos rapidos', mostOrdered: 'Lo mas pedido', featuredProducts: 'Productos destacados', seeFullMenu: 'Ver toda la carta', realReviews: 'Opiniones reales', whatClientsSay: 'Lo que dicen nuestros clientes', howItWorks: 'Como funciona', simpleFast: 'Sencillo y rapido', step1Title: 'Crea tu cuenta', step1Text: 'Registrate en menos de un minuto y accede a todas las funcionalidades.', step2Title: 'Explora la carta', step2Text: 'Descubre nuestros hotdogs artesanales, entrantes y bebidas.', step3Title: 'Haz tu pedido', step3Text: 'Anade al carrito, elige tu metodo de pago y confirma tu pedido.' },
      menu: { cartaTitle: 'Carta de Zyma', cartaSubtitle: 'Disfruta de nuestros deliciosos platos artesanales.', starProduct: 'Producto estrella', backToMenu: 'Volver a toda la carta', guestNotice: 'Modo invitado: puedes ver la carta, para pedir necesitas iniciar sesion.', searchPlaceholder: 'Buscar producto...', allCategories: 'Todas las categorias', price: 'Precio', lowToHigh: 'Menor a mayor', highToLow: 'Mayor a menor', filter: 'Filtrar', clearFilters: 'Limpiar', showing: 'Mostrando', of: 'de', products: 'productos', for: 'para', starBadge: 'Producto estrella', available: 'Disponible', soldOut: 'Agotado', addToCart: 'Anadir al carrito', rateProduct: 'Valorar producto', loginToOrder: 'Inicia sesion para pedir', noResults: 'No se encontraron productos con esos filtros.', seeAllProducts: 'Ver todos los productos' },
      cart: { title: 'Tu Carrito', subtitle: 'Revisa tus productos antes de finalizar el pedido', empty: 'Tu carrito esta vacio.', continueShopping: 'Seguir comprando', paymentMethod: 'Metodo de pago online', cardOption: 'Tarjeta', bizumOption: 'Bizum', stripeCheckout: 'Stripe Checkout', guidedPayment: 'Pago guiado', cardPayment: 'Pago con tarjeta', cardNote: 'Seras redirigido a la pagina segura de Stripe para completar el pago.', bizumPayment: 'Pago con Bizum', bizumNote: 'Te mostraremos las instrucciones en pantalla para enviar el importe exacto y confirmar tu pedido.', bizumPhone: 'Tu telefono de Bizum (opcional)', payOnline: 'Pagar online', orderSummary: 'Resumen del pedido', total: 'Total' },
      worker: { greeting: 'Bienvenido al centro de operaciones', professionalArea: 'Area profesional del trabajador', heroText: 'Tu panel centraliza la operativa del turno, la supervision de pedidos y los accesos clave para trabajar con rapidez, criterio y buena presencia.', openOperations: 'Abrir operativa', changePrices: 'Cambiar precios de productos', pendingOrders: 'Pedidos pendientes', inProgress: 'Pedidos en curso', deliveredToday: 'Entregados hoy', criticalIngredients: 'Ingredientes criticos', controlPanel: 'Panel de control', priorityActions: 'Acciones prioritarias', excellenceProtocol: 'Protocolo de excelencia del turno', smartRadar: 'Radar inteligente del turno', shortcuts: 'Atajos', dailyTools: 'Herramientas del dia', solidExperience: 'Una experiencia mas solida' },
      admin: { title: 'Panel de Administracion', adminPanel: 'Panel administrativo', adminSubtitle: 'Resumen de pedidos, inventario y usuarios. Accede rapidamente a las secciones principales.', orders: 'Pedidos', inventory: 'Inventario', products: 'Productos', todayOrders: 'Pedidos del dia', todayRevenue: 'Ingresos del dia', activeOrders: 'Pedidos activos', preparingOrders: 'Pedidos en preparacion', redIngredients: 'Ingredientes en rojo', topProduct: 'Producto mas vendido', notifications: 'Notificaciones internas', registeredUsers: 'Usuarios registrados', realTimeOrders: 'Pedidos en tiempo real', updated: 'Actualizado', noActiveOrders: 'No hay pedidos activos disponibles.', addUser: 'Anadir usuario', name: 'Nombre', role: 'Rol', client: 'Cliente', worker: 'Trabajador', admin: 'Admin', workerCodePlaceholder: 'Codigo de trabajador (opcional)', createUser: 'Crear usuario', registeredUsersTitle: 'Usuarios registrados', status: 'Estado', actions: 'Acciones', active: 'Activo', blocked: 'Bloqueado', saveRole: 'Guardar rol', block: 'Bloquear', unblock: 'Desbloquear', delete: 'Eliminar', yourUser: 'Tu usuario' },
      common: { street: 'Calle Falsa 123, Barcelona', contact: 'contacto@zyma.com | +34 600 000 000', facebook: 'Facebook', instagram: 'Instagram', twitter: 'Twitter', cookiePolicy: 'Politica de Cookies', privacyPolicy: 'Politica de Privacidad', legalNotice: 'Aviso Legal', allRightsReserved: 'Todos los derechos reservados.' }
    },
    en: {
      siteName: 'Zyma',
      nav: { home: 'Home', viewMenu: 'View menu', reviews: 'Reviews', incidents: 'Incidents', tickets: 'Purchase tickets', myProfile: 'My profile', customizeCookies: 'Customize cookies', logout: 'Log out' },
      auth: { welcomeBack: 'Welcome back', loginSubtitle: 'Log in to access your account', loginBtn: 'Log in', createAccount: 'Create account', createAccountSubtitle: 'Join Zyma and enjoy our products', registerBtn: 'Create account', alreadyHaveAccount: 'Already have an account? Log in', noAccount: 'Don\'t have an account? Register', forgotPassword: 'I forgot my password', email: 'Email', password: 'Password', confirmPassword: 'Confirm password', workerCode: 'Worker or admin code', workerCodePlaceholder: 'E.g.: TRAB001 or ADMIN', workerCodeOptional: 'Worker code (optional)', workerCodeHint: 'If you have one, you\'ll access special features', passwordMin: 'Minimum 6 characters.', emailRequired: 'Email', passwordRequired: 'Password', required: '*' },
      landing: { tagline: 'Zyma. Hotdogs with soul.', kicker: 'Artisan hotdogs', heroText: 'House recipes, fresh ingredients and an unforgettable flavor. Create your account or log in and discover our menu.', createAccount: 'Create account', loginBtn: 'I already have an account', guestMode: 'View menu without account', freshIngredients: 'Fresh ingredients', avgRating: 'Average rating', fastOrders: 'Fast orders', mostOrdered: 'Most ordered', featuredProducts: 'Featured products', seeFullMenu: 'See full menu', realReviews: 'Real reviews', whatClientsSay: 'What our customers say', howItWorks: 'How it works', simpleFast: 'Simple and fast', step1Title: 'Create your account', step1Text: 'Register in less than a minute and access all features.', step2Title: 'Explore the menu', step2Text: 'Discover our artisan hotdogs, starters and drinks.', step3Title: 'Place your order', step3Text: 'Add to cart, choose your payment method and confirm your order.' },
      menu: { cartaTitle: 'Zyma Menu', cartaSubtitle: 'Enjoy our delicious artisan dishes.', starProduct: 'Star product', backToMenu: 'Back to full menu', guestNotice: 'Guest mode: you can view the menu, but you need to log in to order.', searchPlaceholder: 'Search product...', allCategories: 'All categories', price: 'Price', lowToHigh: 'Low to high', highToLow: 'High to low', filter: 'Filter', clearFilters: 'Clear', showing: 'Showing', of: 'of', products: 'products', for: 'for', starBadge: 'Star product', available: 'Available', soldOut: 'Sold out', addToCart: 'Add to cart', rateProduct: 'Rate product', loginToOrder: 'Log in to order', noResults: 'No products found with those filters.', seeAllProducts: 'See all products' },
      cart: { title: 'Your Cart', subtitle: 'Review your items before checkout', empty: 'Your cart is empty.', continueShopping: 'Continue shopping', paymentMethod: 'Online payment method', cardOption: 'Card', bizumOption: 'Bizum', stripeCheckout: 'Stripe Checkout', guidedPayment: 'Guided payment', cardPayment: 'Card payment', cardNote: 'You will be redirected to Stripe\'s secure page to complete payment.', bizumPayment: 'Bizum payment', bizumNote: 'We will show on-screen instructions to send the exact amount and confirm your order.', bizumPhone: 'Your Bizum phone (optional)', payOnline: 'Pay online', orderSummary: 'Order summary', total: 'Total' },
      worker: { greeting: 'Welcome to the operations center', professionalArea: 'Worker professional area', heroText: 'Your panel centralizes shift operations, order supervision and key access to work quickly and efficiently.', openOperations: 'Open operations', changePrices: 'Change product prices', pendingOrders: 'Pending orders', inProgress: 'Orders in progress', deliveredToday: 'Delivered today', criticalIngredients: 'Critical ingredients', controlPanel: 'Control panel', priorityActions: 'Priority actions', excellenceProtocol: 'Shift excellence protocol', smartRadar: 'Smart shift radar', shortcuts: 'Shortcuts', dailyTools: 'Daily tools', solidExperience: 'A more solid experience' },
      admin: { title: 'Admin Panel', adminPanel: 'Admin panel', adminSubtitle: 'Overview of orders, inventory and users. Quickly access the main sections.', orders: 'Orders', inventory: 'Inventory', products: 'Products', todayOrders: 'Today\'s orders', todayRevenue: 'Today\'s revenue', activeOrders: 'Active orders', preparingOrders: 'Orders being prepared', redIngredients: 'Low stock items', topProduct: 'Best selling product', notifications: 'Internal notifications', registeredUsers: 'Registered users', realTimeOrders: 'Real-time orders', updated: 'Updated', noActiveOrders: 'No active orders available.', addUser: 'Add user', name: 'Name', role: 'Role', client: 'Client', worker: 'Worker', admin: 'Admin', workerCodePlaceholder: 'Worker code (optional)', createUser: 'Create user', registeredUsersTitle: 'Registered users', status: 'Status', actions: 'Actions', active: 'Active', blocked: 'Blocked', saveRole: 'Save role', block: 'Block', unblock: 'Unblock', delete: 'Delete', yourUser: 'Your user' },
      common: { street: '123 Fake Street, Barcelona', contact: 'contact@zyma.com | +34 600 000 000', facebook: 'Facebook', instagram: 'Instagram', twitter: 'Twitter', cookiePolicy: 'Cookie Policy', privacyPolicy: 'Privacy Policy', legalNotice: 'Legal Notice', allRightsReserved: 'All rights reserved.' }
    },
    fr: {
      siteName: 'Zyma',
      nav: { home: 'Accueil', viewMenu: 'Voir la carte', reviews: 'Avis', incidents: 'Incidents', tickets: 'Tickets d\'achat', myProfile: 'Mon profil', customizeCookies: 'Personnaliser cookies', logout: 'Deconnexion' },
      auth: { welcomeBack: 'Bienvenue', loginSubtitle: 'Connectez-vous pour acceder a votre compte', loginBtn: 'Se connecter', createAccount: 'Creer un compte', createAccountSubtitle: 'Rejoignez Zyma et decouvrez nos produits', registerBtn: 'Creer un compte', alreadyHaveAccount: 'Vous avez deja un compte? Connectez-vous', noAccount: 'Pas de compte? Inscrivez-vous', forgotPassword: 'J\'ai oublie mon mot de passe', email: 'Email', password: 'Mot de passe', confirmPassword: 'Confirmer le mot de passe', workerCode: 'Code employe ou administrateur', workerCodePlaceholder: 'Ex: TRAB001 ou ADMIN', workerCodeOptional: 'Code employe (optionnel)', workerCodeHint: 'Si vous en avez un, vous accederez a des fonctionnalites speciales', passwordMin: 'Minimum 6 caracteres.', emailRequired: 'Email', passwordRequired: 'Mot de passe', required: '*' },
      landing: { tagline: 'Zyma. Hot-dogs avec ame.', kicker: 'Hot-dogs artisanaux', heroText: 'Recettes maison, ingredients frais et une saveur inoubliable. Creez votre compte ou connectez-vous et decouvrez notre carte.', createAccount: 'Creer un compte', loginBtn: 'J\'ai deja un compte', guestMode: 'Voir la carte sans compte', freshIngredients: 'Ingredients frais', avgRating: 'Note moyenne', fastOrders: 'Commandes rapides', mostOrdered: 'Les plus commandes', featuredProducts: 'Produits vedettes', seeFullMenu: 'Voir toute la carte', realReviews: 'Avis reels', whatClientsSay: 'Ce que disent nos clients', howItWorks: 'Comment ca marche', simpleFast: 'Simple et rapide', step1Title: 'Creez votre compte', step1Text: 'Inscrivez-vous en moins d\'une minute et accedez a toutes les fonctionnalites.', step2Title: 'Explorez la carte', step2Text: 'Decouvrez nos hot-dogs artisanaux, entrees et boissons.', step3Title: 'Passez votre commande', step3Text: 'Ajoutez au panier, choisissez votre methode de paiement et confirmez votre commande.' },
      menu: { cartaTitle: 'Carte de Zyma', cartaSubtitle: 'Degustez nos delicieux plats artisanaux.', starProduct: 'Produit vedette', backToMenu: 'Retour a la carte complete', guestNotice: 'Mode invite: vous pouvez voir la carte, mais vous devez vous connecter pour commander.', searchPlaceholder: 'Rechercher un produit...', allCategories: 'Toutes les categories', price: 'Prix', lowToHigh: 'Prix croissant', highToLow: 'Prix decroissant', filter: 'Filtrer', clearFilters: 'Effacer', showing: 'Affichant', of: 'de', products: 'produits', for: 'pour', starBadge: 'Produit vedette', available: 'Disponible', soldOut: 'Rupture de stock', addToCart: 'Ajouter au panier', rateProduct: 'Noter le produit', loginToOrder: 'Connectez-vous pour commander', noResults: 'Aucun produit trouve avec ces filtres.', seeAllProducts: 'Voir tous les produits' },
      cart: { title: 'Votre Panier', subtitle: 'Verifiez vos articles avant de finaliser la commande', empty: 'Votre panier est vide.', continueShopping: 'Continuer vos achats', paymentMethod: 'Methode de paiement en ligne', cardOption: 'Carte', bizumOption: 'Bizum', stripeCheckout: 'Stripe Checkout', guidedPayment: 'Paiement guide', cardPayment: 'Paiement par carte', cardNote: 'Vous serez redirige vers la page securisee de Stripe pour完成 le paiement.', bizumPayment: 'Paiement par Bizum', bizumNote: 'Nous afficherons les instructions a l\'ecran pour envoyer le montant exact et confirmer votre commande.', bizumPhone: 'Votre telephone Bizum (optionnel)', payOnline: 'Payer en ligne', orderSummary: 'Resume de la commande', total: 'Total' },
      worker: { greeting: 'Bienvenue au centre d\'operations', professionalArea: 'Espace professionnel employe', heroText: 'Votre panneau centralise les operations du shift, la supervision des commandes et les acces cles pour travailler rapidement.', openOperations: 'Ouvrir les operations', changePrices: 'Modifier les prix des produits', pendingOrders: 'Commandes en attente', inProgress: 'Commandes en cours', deliveredToday: 'Livrees aujourd\'hui', criticalIngredients: 'Ingredients critiques', controlPanel: 'Panneau de controle', priorityActions: 'Actions prioritaires', excellenceProtocol: 'Protocole d\'excellence du shift', smartRadar: 'Radar intelligent du shift', shortcuts: 'Access rapides', dailyTools: 'Outils du jour', solidExperience: 'Une experience plus solide' },
      admin: { title: 'Panneau d\'administration', adminPanel: 'Panneau administratif', adminSubtitle: 'Resume des commandes, inventaire et utilisateurs. Accedez rapidement aux sections principales.', orders: 'Commandes', inventory: 'Inventaire', products: 'Produits', todayOrders: 'Commandes du jour', todayRevenue: 'Revenus du jour', activeOrders: 'Commandes actives', preparingOrders: 'Commandes en preparation', redIngredients: 'Articles en stock faible', topProduct: 'Produit le plus vendu', notifications: 'Notifications internes', registeredUsers: 'Utilisateurs inscrits', realTimeOrders: 'Commandes en temps reel', updated: 'Mis a jour', noActiveOrders: 'Aucune commande active disponible.', addUser: 'Ajouter un utilisateur', name: 'Nom', role: 'Role', client: 'Client', worker: 'Employe', admin: 'Admin', workerCodePlaceholder: 'Code employe (optionnel)', createUser: 'Creer un utilisateur', registeredUsersTitle: 'Utilisateurs inscrits', status: 'Statut', actions: 'Actions', active: 'Actif', blocked: 'Bloque', saveRole: 'Sauvegarder role', block: 'Bloquer', unblock: 'Debloquer', delete: 'Supprimer', yourUser: 'Votre utilisateur' },
      common: { street: '123 Rue Fictive, Barcelona', contact: 'contact@zyma.com | +34 600 000 000', facebook: 'Facebook', instagram: 'Instagram', twitter: 'Twitter', cookiePolicy: 'Politique de Cookies', privacyPolicy: 'Politique de Confidentialite', legalNotice: 'Mentions legales', allRightsReserved: 'Tous droits reserves.' }
    }
  };

  var supportedLanguages = ['es', 'en', 'fr'];
  var currentLang = 'es';
  var STORAGE_KEY = 'zyma_lang';

  function detectBrowserLanguage() {
    var browserLang = (navigator.language || navigator.userLanguage || 'es').substring(0, 2);
    return supportedLanguages.indexOf(browserLang) !== -1 ? browserLang : 'es';
  }

  function getStoredLanguage() {
    try {
      var stored = localStorage.getItem(STORAGE_KEY);
      if (stored && supportedLanguages.indexOf(stored) !== -1) {
        return stored;
      }
    } catch (e) {}
    return null;
  }

  function getCurrentLanguage() {
    return getStoredLanguage() || detectBrowserLanguage();
  }

  function setLanguage(lang) {
    if (supportedLanguages.indexOf(lang) === -1) return;
    currentLang = lang;
    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}
    applyTranslations();
    updateLangSwitcherUI();
  }

  function t(key) {
    var parts = key.split('.');
    var obj = translations[currentLang];
    if (!obj) return key;
    for (var i = 0; i < parts.length; i++) {
      if (obj && obj[parts[i]] !== undefined) {
        obj = obj[parts[i]];
      } else {
        var fallback = translations['es'];
        for (var j = 0; j < parts.length; j++) {
          if (fallback && fallback[parts[j]] !== undefined) {
            fallback = fallback[parts[j]];
          } else {
            return key;
          }
        }
        return fallback;
      }
    }
    return typeof obj === 'string' ? obj : key;
  }

  function translateElement(el) {
    var key = el.getAttribute('data-i18n');
    if (!key) return;

    var translated = t(key);

    var isInput = el.tagName === 'INPUT' || el.tagName === 'TEXTAREA';

    if (isInput) {
      if (el.getAttribute('data-i18n-attr') === 'placeholder') {
        el.placeholder = translated;
      }
    } else if (el.children.length === 0 || el.getAttribute('data-i18n-raw')) {
      el.textContent = translated;
    } else {
      var childNodes = Array.prototype.slice.call(el.childNodes);
      var textNodes = childNodes.filter(function(node) {
        return node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '';
      });
      if (textNodes.length > 0) {
        textNodes[0].textContent = translated;
      } else {
        el.textContent = translated;
      }
    }

    if (el.hasAttribute('title') && el.getAttribute('data-i18n-title')) {
      el.title = t(el.getAttribute('data-i18n-title'));
    }
    if (el.hasAttribute('placeholder') && el.getAttribute('data-i18n-placeholder')) {
      el.placeholder = t(el.getAttribute('data-i18n-placeholder'));
    }
    if (el.tagName === 'SELECT') {
      var options = el.querySelectorAll('option');
      for (var i = 0; i < options.length; i++) {
        var optKey = options[i].getAttribute('data-i18n');
        if (optKey) {
          options[i].textContent = t(optKey);
        }
      }
    }
  }

  function translateDescendants(el) {
    var elements = el.querySelectorAll('[data-i18n]');
    for (var i = 0; i < elements.length; i++) {
      translateElement(elements[i]);
    }
  }

  function applyTranslations() {
    translateDescendants(document);
  }

  function updateLangSwitcherUI() {
    var activeBtn = document.querySelector('[data-lang="' + currentLang + '"]');
    var allBtns = document.querySelectorAll('[data-lang]');
    for (var i = 0; i < allBtns.length; i++) {
      allBtns[i].classList.remove('active');
    }
    if (activeBtn) {
      activeBtn.classList.add('active');
    }
  }

  function createLangSwitcher() {
    var existing = document.getElementById('lang-switcher');
    if (existing) return;

    var container = document.createElement('div');
    container.id = 'lang-switcher';
    container.className = 'lang-switcher-container';

    var toggleBtn = document.createElement('button');
    toggleBtn.className = 'lang-switcher-toggle';
    toggleBtn.setAttribute('aria-label', 'Cambiar idioma');
    toggleBtn.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>';

    var dropdown = document.createElement('div');
    dropdown.className = 'lang-switcher-dropdown';

    var languages = [
      { code: 'es', label: 'ES', name: 'Espanol' },
      { code: 'en', label: 'EN', name: 'English' },
      { code: 'fr', label: 'FR', name: 'Francais' }
    ];

    for (var i = 0; i < languages.length; i++) {
      var btn = document.createElement('button');
      btn.className = 'lang-option' + (languages[i].code === currentLang ? ' active' : '');
      btn.setAttribute('data-lang', languages[i].code);
      btn.setAttribute('title', languages[i].name);
      btn.innerHTML = '<span class="lang-code">' + languages[i].label + '</span><span class="lang-name">' + languages[i].name + '</span>';
      btn.addEventListener('click', (function(lang) {
        return function() {
          setLanguage(lang);
          dropdown.classList.remove('open');
          container.classList.remove('open');
        };
      })(languages[i].code));
      dropdown.appendChild(btn);
    }

    container.appendChild(toggleBtn);
    container.appendChild(dropdown);
    document.body.appendChild(container);

    toggleBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      container.classList.toggle('open');
      dropdown.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
      if (!container.contains(e.target)) {
        container.classList.remove('open');
        dropdown.classList.remove('open');
      }
    });
  }

  function init() {
    currentLang = getCurrentLanguage();
    applyTranslations();
    createLangSwitcher();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.ZymaLang = {
    set: setLanguage,
    get: getCurrentLanguage,
    t: t,
    supported: supportedLanguages
  };
})();
