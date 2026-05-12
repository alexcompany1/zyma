(function () {
  'use strict';

  var STORAGE_KEY = 'zyma_lang';
  var DEFAULT_LANG = 'es';
  var SUPPORTED = ['es', 'en', 'fr', 'ca', 'de', 'it'];
  var observerStarted = false;

  var translations = {
    es: {
      'nav.home': 'Inicio',
      'nav.viewMenu': 'Ver carta',
      'nav.reviews': 'Valoraciones',
      'nav.tickets': 'Tickets',
      'nav.myProfile': 'Mi perfil',
      'nav.customizeCookies': 'Personalizar cookies',
      'nav.logout': 'Cerrar sesion',
      'landing.kicker': 'Hotdogs artesanales',
      'landing.tagline': 'Zyma. Sabor con alma.',
      'landing.heroText': 'Recetas de la casa, ingredientes frescos y un sabor que no se olvida. Crea tu cuenta o inicia sesion y descubre nuestra carta.',
      'landing.createAccount': 'Crear cuenta',
      'landing.loginBtn': 'Ya tengo cuenta',
      'landing.guestMode': 'Ver carta sin cuenta',
      'landing.mostOrdered': 'Lo mas pedido',
      'landing.featuredProducts': 'Productos destacados',
      'landing.seeFullMenu': 'Ver toda la carta',
      'landing.realReviews': 'Opiniones reales',
      'landing.whatClientsSay': 'Lo que dicen nuestros clientes',
      'landing.simpleFast': 'Sencillo y rapido',
      'landing.howItWorks': 'Como funciona',
      'landing.step1Title': 'Crea tu cuenta',
      'landing.step1Text': 'Registrate en menos de un minuto y accede a todas las funcionalidades.',
      'landing.step2Title': 'Explora la carta',
      'landing.step2Text': 'Descubre nuestros hotdogs artesanales, entrantes y bebidas.',
      'landing.step3Title': 'Haz tu pedido',
      'landing.step3Text': 'Anade al carrito, elige tu metodo de pago y confirma tu pedido.',
      'auth.loginBtn': 'Iniciar Sesion',
      'auth.email': 'Email',
      'auth.password': 'Contrasena',
      'auth.workerCodeOptional': 'Codigo de trabajador',
      'auth.workerCodeHint': 'Opcional',
      'auth.noAccount': 'No tienes cuenta? Registrate',
      'auth.forgotPassword': 'Olvidaste tu contrasena?',
      'auth.required': '*',
      'cart.title': 'Tu Carrito',
      'cart.subtitle': 'Revisa tus productos antes de confirmar el pedido.',
      'cart.empty': 'Tu carrito esta vacio.',
      'cart.continueShopping': 'Seguir comprando',
      'cart.paymentMethod': 'Metodo de pago',
      'cart.cardOption': 'Tarjeta',
      'cart.bizumOption': 'Bizum',
      'cart.payOnline': 'Pagar online',
      'common.street': 'Calle Falsa 123, Barcelona',
      'common.contact': 'contacto@zyma.com | +34 600 000 000',
      'common.cookiePolicy': 'Politica de Cookies',
      'common.privacyPolicy': 'Politica de Privacidad',
      'common.legalNotice': 'Aviso Legal',
      'common.facebook': 'Facebook',
      'common.instagram': 'Instagram',
      'common.twitter': 'Twitter'
    },
    en: {
      'nav.home': 'Home',
      'nav.viewMenu': 'View menu',
      'nav.reviews': 'Reviews',
      'nav.tickets': 'Tickets',
      'nav.myProfile': 'My profile',
      'nav.customizeCookies': 'Customize cookies',
      'nav.logout': 'Log out',
      'landing.kicker': 'Artisan hotdogs',
      'landing.tagline': 'Zyma. Flavor with soul.',
      'landing.heroText': 'House recipes, fresh ingredients, and a flavor you will not forget. Create your account or sign in and discover our menu.',
      'landing.createAccount': 'Create account',
      'landing.loginBtn': 'I already have an account',
      'landing.guestMode': 'View menu as guest',
      'landing.mostOrdered': 'Most ordered',
      'landing.featuredProducts': 'Featured products',
      'landing.seeFullMenu': 'See full menu',
      'landing.realReviews': 'Real reviews',
      'landing.whatClientsSay': 'What our customers say',
      'landing.simpleFast': 'Simple and fast',
      'landing.howItWorks': 'How it works',
      'landing.step1Title': 'Create your account',
      'landing.step1Text': 'Sign up in less than a minute and access all features.',
      'landing.step2Title': 'Explore the menu',
      'landing.step2Text': 'Discover our artisan hotdogs, starters, and drinks.',
      'landing.step3Title': 'Place your order',
      'landing.step3Text': 'Add items to the cart, choose your payment method, and confirm your order.',
      'auth.loginBtn': 'Sign in',
      'auth.email': 'Email',
      'auth.password': 'Password',
      'auth.workerCodeOptional': 'Worker code',
      'auth.workerCodeHint': 'Optional',
      'auth.noAccount': 'No account? Sign up',
      'auth.forgotPassword': 'Forgot your password?',
      'auth.required': '*',
      'cart.title': 'Your Cart',
      'cart.subtitle': 'Review your products before confirming the order.',
      'cart.empty': 'Your cart is empty.',
      'cart.continueShopping': 'Continue shopping',
      'cart.paymentMethod': 'Payment method',
      'cart.cardOption': 'Card',
      'cart.bizumOption': 'Bizum',
      'cart.payOnline': 'Pay online',
      'common.street': 'Fake Street 123, Barcelona',
      'common.contact': 'contact@zyma.com | +34 600 000 000',
      'common.cookiePolicy': 'Cookie Policy',
      'common.privacyPolicy': 'Privacy Policy',
      'common.legalNotice': 'Legal Notice',
      'common.facebook': 'Facebook',
      'common.instagram': 'Instagram',
      'common.twitter': 'Twitter'
    },
    fr: {
      'nav.home': 'Accueil',
      'nav.viewMenu': 'Voir la carte',
      'nav.reviews': 'Avis',
      'nav.tickets': 'Tickets',
      'nav.myProfile': 'Mon profil',
      'nav.customizeCookies': 'Personnaliser les cookies',
      'nav.logout': 'Se deconnecter',
      'landing.kicker': 'Hotdogs artisanaux',
      'landing.tagline': 'Zyma. Du gout avec une ame.',
      'landing.heroText': 'Recettes maison, ingredients frais et un gout inoubliable. Creez votre compte ou connectez-vous et decouvrez notre carte.',
      'landing.createAccount': 'Creer un compte',
      'landing.loginBtn': 'J ai deja un compte',
      'landing.guestMode': 'Voir la carte sans compte',
      'landing.mostOrdered': 'Les plus commandes',
      'landing.featuredProducts': 'Produits phares',
      'landing.seeFullMenu': 'Voir toute la carte',
      'landing.realReviews': 'Avis reels',
      'landing.whatClientsSay': 'Ce que disent nos clients',
      'landing.simpleFast': 'Simple et rapide',
      'landing.howItWorks': 'Comment ca marche',
      'landing.step1Title': 'Creez votre compte',
      'landing.step1Text': 'Inscrivez-vous en moins d une minute et accedez a toutes les fonctionnalites.',
      'landing.step2Title': 'Explorez la carte',
      'landing.step2Text': 'Decouvrez nos hotdogs artisanaux, entrees et boissons.',
      'landing.step3Title': 'Passez commande',
      'landing.step3Text': 'Ajoutez au panier, choisissez votre moyen de paiement et confirmez votre commande.',
      'auth.loginBtn': 'Connexion',
      'auth.email': 'Email',
      'auth.password': 'Mot de passe',
      'auth.workerCodeOptional': 'Code employe',
      'auth.workerCodeHint': 'Optionnel',
      'auth.noAccount': 'Pas de compte ? Inscrivez-vous',
      'auth.forgotPassword': 'Mot de passe oublie ?',
      'auth.required': '*',
      'cart.title': 'Votre panier',
      'cart.subtitle': 'Verifiez vos produits avant de confirmer la commande.',
      'cart.empty': 'Votre panier est vide.',
      'cart.continueShopping': 'Continuer vos achats',
      'cart.paymentMethod': 'Moyen de paiement',
      'cart.cardOption': 'Carte',
      'cart.bizumOption': 'Bizum',
      'cart.payOnline': 'Payer en ligne',
      'common.street': 'Rue Fausse 123, Barcelone',
      'common.contact': 'contact@zyma.com | +34 600 000 000',
      'common.cookiePolicy': 'Politique de cookies',
      'common.privacyPolicy': 'Politique de confidentialite',
      'common.legalNotice': 'Mentions legales',
      'common.facebook': 'Facebook',
      'common.instagram': 'Instagram',
      'common.twitter': 'Twitter'
    },
    ca: {
      'nav.home': 'Inici',
      'nav.viewMenu': 'Veure carta',
      'nav.reviews': 'Valoracions',
      'nav.tickets': 'Tiquets',
      'nav.myProfile': 'El meu perfil',
      'nav.customizeCookies': 'Personalitzar galetes',
      'nav.logout': 'Tancar sessio',
      'landing.kicker': 'Hotdogs artesanals',
      'landing.tagline': 'Zyma. Sabor amb anima.',
      'landing.heroText': 'Receptes de la casa, ingredients frescos i un sabor que no s oblida. Crea el teu compte o inicia sessio i descobreix la nostra carta.',
      'landing.createAccount': 'Crear compte',
      'landing.loginBtn': 'Ja tinc compte',
      'landing.guestMode': 'Veure carta sense compte',
      'landing.mostOrdered': 'El mes demanat',
      'landing.featuredProducts': 'Productes destacats',
      'landing.seeFullMenu': 'Veure tota la carta',
      'landing.realReviews': 'Opinions reals',
      'landing.whatClientsSay': 'Que diuen els nostres clients',
      'landing.simpleFast': 'Senzill i rapid',
      'landing.howItWorks': 'Com funciona',
      'landing.step1Title': 'Crea el teu compte',
      'landing.step1Text': 'Registra t en menys d un minut i accedeix a totes les funcionalitats.',
      'landing.step2Title': 'Explora la carta',
      'landing.step2Text': 'Descobreix els nostres hotdogs artesanals, entrants i begudes.',
      'landing.step3Title': 'Fes la teva comanda',
      'landing.step3Text': 'Afegeix al carret, tria el metode de pagament i confirma la comanda.',
      'auth.loginBtn': 'Iniciar sessio',
      'auth.email': 'Email',
      'auth.password': 'Contrasenya',
      'auth.workerCodeOptional': 'Codi de treballador',
      'auth.workerCodeHint': 'Opcional',
      'auth.noAccount': 'No tens compte? Registra t',
      'auth.forgotPassword': 'Has oblidat la contrasenya?',
      'auth.required': '*',
      'cart.title': 'El teu carret',
      'cart.subtitle': 'Revisa els productes abans de confirmar la comanda.',
      'cart.empty': 'El teu carret esta buit.',
      'cart.continueShopping': 'Continuar comprant',
      'cart.paymentMethod': 'Metode de pagament',
      'cart.cardOption': 'Targeta',
      'cart.bizumOption': 'Bizum',
      'cart.payOnline': 'Pagar en linia',
      'common.street': 'Carrer Fals 123, Barcelona',
      'common.contact': 'contacte@zyma.com | +34 600 000 000',
      'common.cookiePolicy': 'Politica de galetes',
      'common.privacyPolicy': 'Politica de privacitat',
      'common.legalNotice': 'Avis legal',
      'common.facebook': 'Facebook',
      'common.instagram': 'Instagram',
      'common.twitter': 'Twitter'
    },
    de: {
      'nav.home': 'Startseite',
      'nav.viewMenu': 'Speisekarte',
      'nav.reviews': 'Bewertungen',
      'nav.tickets': 'Tickets',
      'nav.myProfile': 'Mein Profil',
      'nav.customizeCookies': 'Cookies anpassen',
      'nav.logout': 'Abmelden',
      'landing.kicker': 'Handgemachte Hotdogs',
      'landing.tagline': 'Zyma. Geschmack mit Seele.',
      'landing.heroText': 'Hausrezepte, frische Zutaten und ein Geschmack, den man nicht vergisst. Erstelle dein Konto oder melde dich an und entdecke unsere Karte.',
      'landing.createAccount': 'Konto erstellen',
      'landing.loginBtn': 'Ich habe ein Konto',
      'landing.guestMode': 'Karte ohne Konto ansehen',
      'landing.mostOrdered': 'Am meisten bestellt',
      'landing.featuredProducts': 'Ausgewahlte Produkte',
      'landing.seeFullMenu': 'Ganze Karte ansehen',
      'landing.realReviews': 'Echte Bewertungen',
      'landing.whatClientsSay': 'Was unsere Kunden sagen',
      'landing.simpleFast': 'Einfach und schnell',
      'landing.howItWorks': 'So funktioniert es',
      'landing.step1Title': 'Erstelle dein Konto',
      'landing.step1Text': 'Registriere dich in weniger als einer Minute und nutze alle Funktionen.',
      'landing.step2Title': 'Entdecke die Karte',
      'landing.step2Text': 'Entdecke unsere handgemachten Hotdogs, Vorspeisen und Getranke.',
      'landing.step3Title': 'Bestellung aufgeben',
      'landing.step3Text': 'Lege Produkte in den Warenkorb, wahle die Zahlungsart und bestatige deine Bestellung.',
      'auth.loginBtn': 'Anmelden',
      'auth.email': 'E-Mail',
      'auth.password': 'Passwort',
      'auth.workerCodeOptional': 'Mitarbeitercode',
      'auth.workerCodeHint': 'Optional',
      'auth.noAccount': 'Noch kein Konto? Registrieren',
      'auth.forgotPassword': 'Passwort vergessen?',
      'auth.required': '*',
      'cart.title': 'Dein Warenkorb',
      'cart.subtitle': 'Prufe deine Produkte, bevor du die Bestellung bestatigst.',
      'cart.empty': 'Dein Warenkorb ist leer.',
      'cart.continueShopping': 'Weiter einkaufen',
      'cart.paymentMethod': 'Zahlungsart',
      'cart.cardOption': 'Karte',
      'cart.bizumOption': 'Bizum',
      'cart.payOnline': 'Online bezahlen',
      'common.street': 'Falsche Strasse 123, Barcelona',
      'common.contact': 'kontakt@zyma.com | +34 600 000 000',
      'common.cookiePolicy': 'Cookie-Richtlinie',
      'common.privacyPolicy': 'Datenschutzrichtlinie',
      'common.legalNotice': 'Impressum',
      'common.facebook': 'Facebook',
      'common.instagram': 'Instagram',
      'common.twitter': 'Twitter'
    },
    it: {
      'nav.home': 'Home',
      'nav.viewMenu': 'Vedi menu',
      'nav.reviews': 'Recensioni',
      'nav.tickets': 'Ticket',
      'nav.myProfile': 'Il mio profilo',
      'nav.customizeCookies': 'Personalizza cookie',
      'nav.logout': 'Esci',
      'landing.kicker': 'Hotdog artigianali',
      'landing.tagline': 'Zyma. Gusto con anima.',
      'landing.heroText': 'Ricette della casa, ingredienti freschi e un gusto indimenticabile. Crea il tuo account o accedi e scopri il nostro menu.',
      'landing.createAccount': 'Crea account',
      'landing.loginBtn': 'Ho gia un account',
      'landing.guestMode': 'Vedi menu senza account',
      'landing.mostOrdered': 'I piu ordinati',
      'landing.featuredProducts': 'Prodotti in evidenza',
      'landing.seeFullMenu': 'Vedi tutto il menu',
      'landing.realReviews': 'Recensioni reali',
      'landing.whatClientsSay': 'Cosa dicono i clienti',
      'landing.simpleFast': 'Semplice e veloce',
      'landing.howItWorks': 'Come funziona',
      'landing.step1Title': 'Crea il tuo account',
      'landing.step1Text': 'Registrati in meno di un minuto e accedi a tutte le funzionalita.',
      'landing.step2Title': 'Esplora il menu',
      'landing.step2Text': 'Scopri i nostri hotdog artigianali, antipasti e bevande.',
      'landing.step3Title': 'Fai il tuo ordine',
      'landing.step3Text': 'Aggiungi al carrello, scegli il metodo di pagamento e conferma l ordine.',
      'auth.loginBtn': 'Accedi',
      'auth.email': 'Email',
      'auth.password': 'Password',
      'auth.workerCodeOptional': 'Codice lavoratore',
      'auth.workerCodeHint': 'Opzionale',
      'auth.noAccount': 'Non hai un account? Registrati',
      'auth.forgotPassword': 'Password dimenticata?',
      'auth.required': '*',
      'cart.title': 'Il tuo carrello',
      'cart.subtitle': 'Controlla i prodotti prima di confermare l ordine.',
      'cart.empty': 'Il carrello e vuoto.',
      'cart.continueShopping': 'Continua gli acquisti',
      'cart.paymentMethod': 'Metodo di pagamento',
      'cart.cardOption': 'Carta',
      'cart.bizumOption': 'Bizum',
      'cart.payOnline': 'Paga online',
      'common.street': 'Via Falsa 123, Barcellona',
      'common.contact': 'contatto@zyma.com | +34 600 000 000',
      'common.cookiePolicy': 'Politica dei cookie',
      'common.privacyPolicy': 'Informativa privacy',
      'common.legalNotice': 'Note legali',
      'common.facebook': 'Facebook',
      'common.instagram': 'Instagram',
      'common.twitter': 'Twitter'
    }
  };

  var phraseMap = {
    'Entrar': { en: 'Sign in', fr: 'Connexion', ca: 'Entrar', de: 'Anmelden', it: 'Accedi' },
    'Crear cuenta': { en: 'Create account', fr: 'Creer un compte', ca: 'Crear compte', de: 'Konto erstellen', it: 'Crea account' },
    'Carta': { en: 'Menu', fr: 'Carte', ca: 'Carta', de: 'Speisekarte', it: 'Menu' },
    'Como funciona': { en: 'How it works', fr: 'Comment ca marche', ca: 'Com funciona', de: 'So funktioniert es', it: 'Come funziona' },
    'Opiniones': { en: 'Reviews', fr: 'Avis', ca: 'Opinions', de: 'Bewertungen', it: 'Recensioni' },
    'Iniciar Sesion': { en: 'Sign in', fr: 'Connexion', ca: 'Iniciar sessio', de: 'Anmelden', it: 'Accedi' },
    'Email': { en: 'Email', fr: 'Email', ca: 'Email', de: 'E-Mail', it: 'Email' },
    'Contrasena': { en: 'Password', fr: 'Mot de passe', ca: 'Contrasenya', de: 'Passwort', it: 'Password' },
    'Inicio': { en: 'Home', fr: 'Accueil', ca: 'Inici', de: 'Startseite', it: 'Home' },
    'Ver carta': { en: 'View menu', fr: 'Voir la carte', ca: 'Veure carta', de: 'Speisekarte', it: 'Vedi menu' },
    'Valoraciones': { en: 'Reviews', fr: 'Avis', ca: 'Valoracions', de: 'Bewertungen', it: 'Recensioni' },
    'Mi perfil': { en: 'My profile', fr: 'Mon profil', ca: 'El meu perfil', de: 'Mein Profil', it: 'Il mio profilo' },
    'Personalizar cookies': { en: 'Customize cookies', fr: 'Personnaliser les cookies', ca: 'Personalitzar galetes', de: 'Cookies anpassen', it: 'Personalizza cookie' },
    'Cerrar Sesion': { en: 'Log out', fr: 'Se deconnecter', ca: 'Tancar sessio', de: 'Abmelden', it: 'Esci' },
    'Cerrar sesion': { en: 'Log out', fr: 'Se deconnecter', ca: 'Tancar sessio', de: 'Abmelden', it: 'Esci' },
    'Politica de Cookies': { en: 'Cookie Policy', fr: 'Politique de cookies', ca: 'Politica de galetes', de: 'Cookie-Richtlinie', it: 'Politica dei cookie' },
    'Politica de Privacidad': { en: 'Privacy Policy', fr: 'Politique de confidentialite', ca: 'Politica de privacitat', de: 'Datenschutzrichtlinie', it: 'Informativa privacy' },
    'Aviso Legal': { en: 'Legal Notice', fr: 'Mentions legales', ca: 'Avis legal', de: 'Impressum', it: 'Note legali' },
    'Tu Carrito': { en: 'Your Cart', fr: 'Votre panier', ca: 'El teu carret', de: 'Dein Warenkorb', it: 'Il tuo carrello' },
    'Seguir comprando': { en: 'Continue shopping', fr: 'Continuer vos achats', ca: 'Continuar comprant', de: 'Weiter einkaufen', it: 'Continua gli acquisti' },
    'Editar Carta': { en: 'Edit Menu', fr: 'Modifier la carte', ca: 'Editar carta', de: 'Speisekarte bearbeiten', it: 'Modifica menu' },
    'Volver al Panel de Control': { en: 'Back to control panel', fr: 'Retour au panneau de controle', ca: 'Tornar al panell de control', de: 'Zuruck zum Kontrollbereich', it: 'Torna al pannello di controllo' },
    'Carta de Zyma': { en: 'Zyma Menu', fr: 'Carte de Zyma', ca: 'Carta de Zyma', de: 'Zyma Speisekarte', it: 'Menu di Zyma' },
    'Disfruta de nuestros deliciosos platos artesanales.': { en: 'Enjoy our delicious artisan dishes.', fr: 'Profitez de nos delicieux plats artisanaux.', ca: 'Gaudeix dels nostres deliciosos plats artesanals.', de: 'Geniesse unsere leckeren handgemachten Gerichte.', it: 'Goditi i nostri deliziosi piatti artigianali.' },
    'Producto estrella:': { en: 'Star product:', fr: 'Produit vedette :', ca: 'Producte estrella:', de: 'Highlight:', it: 'Prodotto speciale:' },
    'Modo invitado: puedes ver la carta, para pedir necesitas iniciar Sesion.': { en: 'Guest mode: you can view the menu, but you need to sign in to order.', fr: 'Mode invite : vous pouvez voir la carte, mais vous devez vous connecter pour commander.', ca: 'Mode convidat: pots veure la carta, pero has d iniciar sessio per demanar.', de: 'Gastmodus: Du kannst die Karte ansehen, musst dich aber zum Bestellen anmelden.', it: 'Modalita ospite: puoi vedere il menu, ma devi accedere per ordinare.' }
  };

  function normalize(value) {
    return (value || '').replace(/\s+/g, ' ').trim();
  }

  function getLang() {
    var cookieMatch = document.cookie.match(new RegExp('(?:^|; )' + STORAGE_KEY + '=([^;]*)'));
    var cookieLang = cookieMatch ? decodeURIComponent(cookieMatch[1]) : '';
    var saved = localStorage.getItem(STORAGE_KEY) || cookieLang || DEFAULT_LANG;
    return SUPPORTED.indexOf(saved) === -1 ? DEFAULT_LANG : saved;
  }

  function translateKey(key, lang) {
    return (translations[lang] && translations[lang][key]) || (translations.es && translations.es[key]) || '';
  }

  function translatePhrase(text, lang) {
    var normalized = normalize(text);
    var item = phraseMap[normalized];
    if (!item) return null;
    return lang === DEFAULT_LANG ? normalized : (item[lang] || normalized);
  }

  function applyDataTranslations(lang) {
    var count = 0;
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      var value = translateKey(key, lang);
      if (!value) {
        // Si no hay valor, mantener el original
        if (el.hasAttribute('data-i18n-original')) {
          if (el.getAttribute('data-i18n-raw') === '1') {
            el.innerHTML = el.getAttribute('data-i18n-original');
          } else {
            el.textContent = el.getAttribute('data-i18n-original');
          }
        }
        return;
      }
      count++;
      if (!el.hasAttribute('data-i18n-original')) {
        el.setAttribute('data-i18n-original', el.innerHTML);
      }
      if (el.getAttribute('data-i18n-raw') === '1') el.innerHTML = value;
      else el.textContent = value;
    });
    // Debug: loguear cantidad de elementos traducidos
    if (typeof console !== 'undefined' && console.log) {
      console.log('[i18n] Elementos traducidos con data-i18n:', count, 'Idioma:', lang);
    }

    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      var value = translateKey(el.getAttribute('data-i18n-placeholder'), lang);
      if (value) el.setAttribute('placeholder', value);
    });

    document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
      var value = translateKey(el.getAttribute('data-i18n-html'), lang);
      if (value) {
        if (!el.hasAttribute('data-i18n-original')) {
          el.setAttribute('data-i18n-original', el.innerHTML);
        }
        el.innerHTML = value;
      }
    });

    document.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
      var value = translateKey(el.getAttribute('data-i18n-aria'), lang);
      if (value) el.setAttribute('aria-label', value);
    });
  }

  function applyPhraseTranslations(lang) {
    var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
      acceptNode: function (node) {
        var parent = node.parentElement;
        if (!parent) return NodeFilter.FILTER_REJECT;
        if (parent.closest('script, style, textarea, select, [data-no-i18n], [data-i18n]')) return NodeFilter.FILTER_REJECT;
        return normalize(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
      }
    });

    var nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);

    nodes.forEach(function (node) {
      if (!node.parentElement) return;
      if (!node._zymaSourceText) node._zymaSourceText = node.nodeValue;
      var source = node._zymaSourceText;
      var translated = translatePhrase(source, lang);
      if (translated) {
        var leading = (source.match(/^\s*/) || [''])[0];
        var trailing = (source.match(/\s*$/) || [''])[0];
        node.nodeValue = leading + translated + trailing;
      }
    });
  }

  function applyTitle(lang) {
    if (!document.body.hasAttribute('data-i18n-title-source')) {
      document.body.setAttribute('data-i18n-title-source', document.title);
    }
    var source = document.body.getAttribute('data-i18n-title-source');
    var replacements = {
      en: source.replace('Iniciar Sesion', 'Sign in').replace('Registrarse', 'Sign up').replace('Carta', 'Menu').replace('Bienvenido', 'Welcome'),
      fr: source.replace('Iniciar Sesion', 'Connexion').replace('Registrarse', 'Inscription').replace('Carta', 'Carte').replace('Bienvenido', 'Bienvenue'),
      ca: source.replace('Iniciar Sesion', 'Iniciar sessio').replace('Registrarse', 'Registrar-se').replace('Carta', 'Carta').replace('Bienvenido', 'Benvingut'),
      de: source.replace('Iniciar Sesion', 'Anmelden').replace('Registrarse', 'Registrieren').replace('Carta', 'Speisekarte').replace('Bienvenido', 'Willkommen'),
      it: source.replace('Iniciar Sesion', 'Accedi').replace('Registrarse', 'Registrati').replace('Carta', 'Menu').replace('Bienvenido', 'Benvenuto')
    };
    document.title = lang === DEFAULT_LANG ? source : (replacements[lang] || source);
  }

  function apply(lang) {
    lang = SUPPORTED.indexOf(lang) === -1 ? DEFAULT_LANG : lang;
    if (!document.body) return;
    document.documentElement.lang = lang;
    applyDataTranslations(lang);
    applyPhraseTranslations(lang);
    applyTitle(lang);
    document.dispatchEvent(new CustomEvent('zyma:language-applied', { detail: { lang: lang } }));
  }

  window.ZymaLang = {
    get: getLang,
    set: function (lang) {
      if (SUPPORTED.indexOf(lang) === -1) lang = DEFAULT_LANG;
      localStorage.setItem(STORAGE_KEY, lang);
      document.cookie = STORAGE_KEY + '=' + encodeURIComponent(lang) + '; path=/; max-age=31536000; SameSite=Lax';
      // Asegurar que apply se llama inmediatamente y sincrónicamente
      apply(lang);
      document.dispatchEvent(new CustomEvent('zyma:language-change', { detail: { lang: lang } }));
      document.dispatchEvent(new CustomEvent('zyma:language-applied', { detail: { lang: lang } }));
    },
    apply: apply,
    translations: translations
  };

  function mergeExtraTranslations() {
    if (!window.ZymaExtraTranslations) return;
    var extra = window.ZymaExtraTranslations;
    for (var lang in extra) {
      if (!extra.hasOwnProperty(lang)) continue;
      translations[lang] = translations[lang] || {};
      for (var key in extra[lang]) {
        if (extra[lang].hasOwnProperty(key) && !translations[lang][key]) {
          translations[lang][key] = extra[lang][key];
        }
      }
    }
    translations.ca = translations.ca || {};
    translations.de = translations.de || {};
    translations.it = translations.it || {};
  }

  function startObserver() {
    if (observerStarted || !document.body || !window.MutationObserver) return;
    observerStarted = true;
    var timeout = null;
    var observer = new MutationObserver(function () {
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        apply(getLang());
      }, 40);
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  function boot() {
    mergeExtraTranslations();
    apply(getLang());
    startObserver();
  }

  window.addEventListener('storage', function (event) {
    if (event.key === STORAGE_KEY) apply(getLang());
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
