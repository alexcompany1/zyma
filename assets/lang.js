/**
 * lang.js - Sistema multiidioma para Zyma
 * Idiomas: Español (es), Français (fr), English (en)
 * Uso: añade data-i18n="clave" a los elementos HTML traducibles.
 */
(function () {
  'use strict';

  // ─── TRADUCCIONES ────────────────────────────────────────────────────────────
  var T = {
    es: {
      // Navegación común
      'nav.enter':           'Entrar',
      'nav.createAccount':   'Crear cuenta',
      'nav.home':            'Inicio',
      'nav.viewMenu':        'Ver carta',
      'nav.reviews':         'Valoraciones',
      'nav.tickets':         'Tickets',
      'nav.myProfile':       'Mi perfil',
      'nav.customizeCookies':'Personalizar cookies',
      'nav.logout':          'Cerrar Sesión',
      'nav.quickMenu':       'Menú rápido',
      'nav.cart':            'Carrito',
      'nav.notifications':   'Notificaciones',

      // Footer común
      'footer.rights':       '© 2025/2026 Zyma. Todos los derechos reservados.',
      'footer.cookiePolicy': 'Política de Cookies',
      'footer.privacy':      'Política de Privacidad',
      'footer.legal':        'Aviso Legal',

      // Campos comunes
      'common.email':           'Email',
      'common.password':        'Contraseña',
      'common.workerCode':      'Código de trabajador (opcional)',
      'common.showPassword':    'Mostrar contraseña',
      'common.hidePassword':    'Ocultar contraseña',
      'common.saveChanges':     'Guardar cambios',
      'common.backToLogin':     'Volver al login',

      // ── INDEX ──────────────────────────────────────────────────────────────
      'index.heroTitle':        'Zyma. Hotdogs con alma.',
      'index.heroParagraph':    'Recetas de la casa, ingredientes frescos y un sabor que no se olvida. Crea tu cuenta o inicia sesión y descubre la carta.',
      'index.alreadyAccount':   'Ya tengo cuenta',
      'index.guestView':        'Ver carta sin cuenta',
      'index.reviewsTitle':     'Lo que dicen nuestros clientes',

      // ── LOGIN ──────────────────────────────────────────────────────────────
      'login.title':            'Iniciar Sesión',
      'login.workerHint':       'Trabajador: ej. TRAB001<br>Administrador: ADMIN',
      'login.submit':           'Iniciar Sesión',
      'login.noAccount':        'No tienes cuenta? Registrate',
      'login.forgot':           'He olvidado la Contraseña',

      // ── REGISTRO ───────────────────────────────────────────────────────────
      'register.title':           'Crea tu cuenta',
      'register.passwordLabel':   'Contraseña',
      'register.confirmPassword': 'Confirmar contraseña',
      'register.passwordMin':     'Mínimo 6 caracteres.',
      'register.repeatHint':      'Repite tu contraseña para confirmar.',
      'register.workerHint':      'Si lo tienes, accederás a funciones especiales.',
      'register.submit':          'Registrarse',
      'register.hasAccount':      '¿Ya tienes cuenta? Inicia sesión',
      'register.successTitle':    '¡Registro exitoso!',
      'register.successCreated':  'Tu cuenta ha sido creada correctamente.',
      'register.redirectText':    'Redirigiendo al login en',
      'register.redirectSeconds': 'segundos...',
      'register.goLogin':         'Ir al Login ahora',

      // ── USUARIO (HOME) ─────────────────────────────────────────────────────
      'user.welcomeKicker':    'Bienvenido a Zyma',
      'user.personalPanel':    'Panel personal',
      'user.greeting':         'Hola,',
      'user.description':      'Descubre una experiencia más cuidada, con acceso rápido a la carta, valoraciones reales y un panel mucho más limpio para moverte por la web.',
      'user.viewMenu':         'Ver la carta',
      'user.starProduct':      'Producto estrella',
      'user.myOrders':         'Mis pedidos',
      'user.productsLabel':    'productos disponibles',
      'user.reviewsLabel':     'valoraciones reales',
      'user.avgSatisfaction':  'media de satisfacción',
      'user.quickAccess':      'Acceso rápido',
      'user.quickTitle':       'Todo lo importante en un vistazo',
      'user.tipTitle':         'Consejo',
      'user.tipBody':          'Empieza por la carta para descubrir los productos mejor valorados y añadirlos al carrito en pocos pasos.',
      'user.backToStar':       'Ir al producto estrella',
      'user.exploreMenu':      'Explorar carta completa',
      'user.seeReviews':       'Ver opiniones de clientes',
      'user.openTickets':      'Abrir o revisar tickets',
      'user.updateProfile':    'Actualizar perfil',
      'user.featuredKicker':   'Destacados',
      'user.featuredTitle':    'Productos que mejor impresion causan',
      'user.viewAllMenu':      'Ver toda la carta',
      'user.reviewsKicker':    'Reseñas',
      'user.reviewsTitle':     'Lo que dicen nuestros clientes',
      'user.reviewsViewAll':   'Ver todas',
      'user.ratingsLabel':     'valoraciones',
      'user.starBadge':        'Producto estrella',
      'user.cardKicker':       'Carta',
      'user.cardTitle':        'Descubre el producto estrella',
      'user.cardDesc':         'Entra directamente a la vista del producto destacado y revisalo sin distracciones.',
      'user.reviewsCardKicker':'Opiniones',
      'user.reviewsCardTitle': 'Revisa lo que opinan otros clientes',
      'user.reviewsCardDesc':  'Conoce las reseñas recientes y valora tus productos favoritos.',
      'user.supportCardKicker':'Soporte',
      'user.supportCardTitle': 'Gestiona dudas o incidencias',
      'user.supportCardDesc':  'Accede a tus tickets y mantente al día con las respuestas.',

      // ── CARTA (MENÚ) ───────────────────────────────────────────────────────
      'menu.title':           'Carta de Zyma',
      'menu.subtitle':        'Disfruta de nuestros deliciosos platos artesanales.',
      'menu.subtitleFeatured':'Vista destacada de nuestro producto estrella.',
      'menu.starCallout':     'Producto estrella:',
      'menu.backToAll':       'Volver a toda la carta',
      'menu.guestMode':       'Modo invitado: puedes ver la carta, para pedir necesitas iniciar Sesión.',
      'menu.addToCart':       'Añadir al carrito',
      'menu.rateProduct':     'Valorar producto',
      'menu.loginToOrder':    'Inicia Sesión para pedir',
      'menu.starBadge':       'Producto estrella',

      // ── CARRITO ────────────────────────────────────────────────────────────
      'cart.title':           'Tu Carrito',
      'cart.subtitle':        'Revisa tus productos antes de finalizar el pedido',
      'cart.empty':           'Tu carrito esta vacio.',
      'cart.continueShopping':'Seguir comprando',
      'cart.paymentMethod':   'Método de pago online',
      'cart.cardOption':      'Tarjeta',
      'cart.bizumOption':     'Bizum',
      'cart.payOnline':       'Pagar online',
      'cart.total':           'Total:',

      // ── PERFIL ─────────────────────────────────────────────────────────────
      'profile.personalPanel':    'Panel personal',
      'profile.description':      'Gestiona tu cuenta desde un espacio más claro, profesional y fácil de usar.',
      'profile.activeAccount':    'Cuenta activa',
      'profile.strongSecurity':   'Seguridad reforzada',
      'profile.summaryKicker':    'Resumen',
      'profile.accountInfo':      'Información de cuenta',
      'profile.name':             'Nombre',
      'profile.email':            'Email',
      'profile.role':             'Rol',
      'profile.status':           'Estado',
      'profile.operative':        'Perfil operativo',
      'profile.proTipTitle':      'Consejo profesional',
      'profile.proTipBody':       'Usa tu nombre completo y revisa tu contraseña con frecuencia para mantener una imagen más cuidada y segura.',
      'profile.backToPanel':      'Volver al panel principal',
      'profile.personalDataKicker':'Datos personales',
      'profile.editProfile':      'Editar perfil',
      'profile.editDesc':         'Actualiza la información visible de tu cuenta para dar una imagen más profesional.',
      'profile.fullName':         'Nombre completo',
      'profile.nameNote':         'Este nombre se mostrará en tu área privada y ayuda a que el perfil se vea más serio y ordenado.',
      'profile.securityKicker':   'Seguridad',
      'profile.changePassword':   'Cambiar contraseña',
      'profile.securityDesc':     'Actualiza tu clave para mantener protegido el acceso a la cuenta.',
      'profile.currentPassword':  'Contraseña actual',
      'profile.newPassword':      'Nueva contraseña',
      'profile.confirmNewPwd':    'Confirmar nueva contraseña',
      'profile.passwordNote':     'Combina letras, números y símbolos para conseguir una clave más fuerte.',
      'profile.updatePassword':   'Actualizar contraseña',

      // ── VALORACIONES ───────────────────────────────────────────────────────
      'reviews.title':         'Valoraciones de Productos',
      'reviews.subtitle':      'Comparte tu opinión sobre los productos de Zyma.',
      'reviews.loggedInHint':  'Tu valoración ayuda a otros clientes a tomar mejores decisiones.',
      'reviews.loggedOutHint': 'Inicia sesión para valorar nuestros productos.',
      'reviews.noProducts':    'No hay productos disponibles en este momento.',
      'reviews.noImage':       'Sin imagen',
      'reviews.update':        'Actualizar',
      'reviews.rate':          'Valorar',
      'reviews.loginHint':     'Inicia sesión',
      'reviews.loginToRate':   'para valorar este producto',
      'reviews.viewReviews':   'Ver reseñas',
      'reviews.noReviews':     'No hay reseñas disponibles.',
      'reviews.restaurantReply':'[Respuesta del restaurante Zyma]:',
      'reviews.replied':       'Respondido:',
      'reviews.backHome':      'Volver al inicio',
      'reviews.viewMenu':      'Ver Carta',

      // ── TICKETS ────────────────────────────────────────────────────────────
      'tickets.kicker':          'Atención al cliente',
      'tickets.heroTitle':       'Gestión de incidencias y tickets',
      'tickets.heroDesc':        'Desde aquí puedes comunicar cualquier problema con tu pedido, tu cuenta o un pago, y al mismo tiempo seguir teniendo a mano tus tickets de compra.',
      'tickets.openLabel':       'abiertas',
      'tickets.inProgressLabel': 'en proceso',
      'tickets.closedLabel':     'cerradas',
      'tickets.newIssueKicker':  'Nueva incidencia',
      'tickets.newIssueTitle':   'Cuéntanos qué ha pasado',
      'tickets.newIssueDesc':    'Describe el problema con claridad para que podamos ayudarte más rápido.',
      'tickets.subject':         'Asunto',
      'tickets.category':        'Categoría',
      'tickets.catOrder':        'Pedido',
      'tickets.catPayment':      'Pago',
      'tickets.catAccount':      'Cuenta',
      'tickets.catProduct':      'Producto',
      'tickets.catTechnical':    'Técnico',
      'tickets.catGeneral':      'General',
      'tickets.priority':        'Prioridad',
      'tickets.priMedium':       'Media',
      'tickets.priHigh':         'Alta',
      'tickets.priLow':          'Baja',
      'tickets.description':     'Descripción',
      'tickets.submit':          'Enviar incidencia',
      'tickets.trackingKicker':  'Seguimiento',
      'tickets.myIssues':        'Mis incidencias',
      'tickets.myIssuesDesc':    'Consulta el estado de cada incidencia registrada desde tu cuenta.',
      'tickets.noIssues':        'Todavía no has creado incidencias.',
      'tickets.priorityLabel':   'Prioridad',
      'tickets.created':         'Creada:',
      'tickets.updated':         'Actualizada:',
      'tickets.purchasesKicker': 'Compras',
      'tickets.purchasesTitle':  'Tickets de compra',
      'tickets.purchasesDesc':   'Aquí sigues teniendo acceso a tus comprobantes de pedido.',
      'tickets.noOrders':        'Aún no tienes pedidos registrados.',
      'tickets.date':            'Fecha:',
      'tickets.statusLabel':     'Estado:',
      'tickets.totalLabel':      'Total:',
      'tickets.viewTicket':      'Ver ticket',

      // ── MIS PEDIDOS ────────────────────────────────────────────────────────
      'orders.title':         'Mis Pedidos',
      'orders.empty':         'No tienes pedidos realizados.',
      'orders.viewMenu':      'Ver Carta',
      'orders.status':        'Estado:',
      'orders.total':         'Total:',
      'orders.date':          'Fecha:',
      'orders.products':      'Productos:',
      'orders.cancel':        'Cancelar Pedido',

      // ── NOTIFICACIONES ─────────────────────────────────────────────────────
      'notif.title':       'Notificaciones',
      'notif.subtitle':    'Aquí verás los avisos sobre tus pedidos y actualizaciones.',
      'notif.unread':      'No leídas:',
      'notif.markAllRead': 'Marcar todas como leídas',
      'notif.empty':       'No tienes notificaciones todavía.',
      'notif.markRead':    'Marcar como leída',
      'notif.read':        'Leída',

      // ── FORGOT PASSWORD ────────────────────────────────────────────────────
      'forgot.title':      'Recuperar Contraseña',
      'forgot.submit':     'Enviar enlace',
      'forgot.backLogin':  'Volver al login',

      // ── RESET PASSWORD ─────────────────────────────────────────────────────
      'reset.title':       'Establecer nueva Contraseña',
      'reset.newPassword': 'Nueva Contraseña',
      'reset.confirmPwd':  'Confirmar Contraseña',
      'reset.submit':      'Guardar Contraseña',
      'reset.requestNew':  'Solicitar nuevo enlace',
      'reset.backLogin':   'Volver al login',

      // ── TICKET (vista detalle) ─────────────────────────────────────────────
      'ticket.title':        'Ticket de compra',
      'ticket.product':      'Producto',
      'ticket.quantity':     'Cantidad',
      'ticket.unitPrice':    'Precio unidad',
      'ticket.vatPct':       'IVA %',
      'ticket.vatAmount':    'IVA importe',
      'ticket.subtotalNoVat':'Subtotal (sin IVA)',
      'ticket.totalCol':     'Total',
      'ticket.subtotal':     'Subtotal:',
      'ticket.vat':          'IVA:',
      'ticket.totalWithVat': 'Total (con IVA):',
      'ticket.backToTickets':'Volver a Tickets',

      // ── PRODUCTOS ─────────────────────────────────────────────────────────
      'product.1': 'Nachos con Queso',
      'product.2': 'Patatas Fritas',
      'product.3': 'Hotdog BBQ',
      'product.4': 'Hotdog Clásico',
      'product.5': 'Hotdog Vegano',
      'product.6': 'Refresco Cola',
      'product.7': 'Agua Mineral',
    },

    // ─────────────────────────────────────────────────────────────────────────
    fr: {
      // Navegación común
      'nav.enter':           'Se connecter',
      'nav.createAccount':   'Créer un compte',
      'nav.home':            'Accueil',
      'nav.viewMenu':        'Voir la carte',
      'nav.reviews':         'Avis',
      'nav.tickets':         'Tickets',
      'nav.myProfile':       'Mon profil',
      'nav.customizeCookies':'Personnaliser les cookies',
      'nav.logout':          'Se déconnecter',
      'nav.quickMenu':       'Menu rapide',
      'nav.cart':            'Panier',
      'nav.notifications':   'Notifications',

      // Footer
      'footer.rights':       '© 2025/2026 Zyma. Tous droits réservés.',
      'footer.cookiePolicy': 'Politique de Cookies',
      'footer.privacy':      'Politique de Confidentialité',
      'footer.legal':        'Mentions légales',

      // Campos comunes
      'common.email':           'Email',
      'common.password':        'Mot de passe',
      'common.workerCode':      'Code employé (optionnel)',
      'common.showPassword':    'Afficher le mot de passe',
      'common.hidePassword':    'Masquer le mot de passe',
      'common.saveChanges':     'Enregistrer les modifications',
      'common.backToLogin':     'Retour à la connexion',

      // INDEX
      'index.heroTitle':        'Zyma. Hotdogs avec âme.',
      'index.heroParagraph':    'Recettes maison, ingrédients frais et une saveur inoubliable. Créez votre compte ou connectez-vous pour découvrir la carte.',
      'index.alreadyAccount':   "J'ai déjà un compte",
      'index.guestView':        'Voir la carte sans compte',
      'index.reviewsTitle':     'Ce que disent nos clients',

      // LOGIN
      'login.title':            'Se connecter',
      'login.workerHint':       'Employé : ex. TRAB001<br>Administrateur : ADMIN',
      'login.submit':           'Se connecter',
      'login.noAccount':        'Pas de compte ? Inscrivez-vous',
      'login.forgot':           "J'ai oublié mon mot de passe",

      // REGISTRO
      'register.title':           'Créez votre compte',
      'register.passwordLabel':   'Mot de passe',
      'register.confirmPassword': 'Confirmer le mot de passe',
      'register.passwordMin':     'Minimum 6 caractères.',
      'register.repeatHint':      'Répétez votre mot de passe pour confirmer.',
      'register.workerHint':      "Si vous l'avez, vous accéderez à des fonctions spéciales.",
      'register.submit':          "S'inscrire",
      'register.hasAccount':      'Vous avez déjà un compte ? Connectez-vous',
      'register.successTitle':    'Inscription réussie !',
      'register.successCreated':  'Votre compte a été créé avec succès.',
      'register.redirectText':    'Redirection vers la connexion dans',
      'register.redirectSeconds': 'secondes...',
      'register.goLogin':         'Aller à la connexion maintenant',

      // USUARIO
      'user.welcomeKicker':    'Bienvenue chez Zyma',
      'user.personalPanel':    'Panneau personnel',
      'user.greeting':         'Bonjour,',
      'user.description':      "Découvrez une expérience plus soignée, avec un accès rapide à la carte, des avis réels et un panneau beaucoup plus clair pour naviguer sur le site.",
      'user.viewMenu':         'Voir la carte',
      'user.starProduct':      'Produit vedette',
      'user.myOrders':         'Mes commandes',
      'user.productsLabel':    'produits disponibles',
      'user.reviewsLabel':     'avis réels',
      'user.avgSatisfaction':  'satisfaction moyenne',
      'user.quickAccess':      'Accès rapide',
      'user.quickTitle':       "Tout l'essentiel en un coup d'œil",
      'user.tipTitle':         'Conseil',
      'user.tipBody':          "Commencez par la carte pour découvrir les produits les mieux notés et les ajouter au panier en quelques étapes.",
      'user.backToStar':       'Aller au produit vedette',
      'user.exploreMenu':      'Explorer la carte complète',
      'user.seeReviews':       'Voir les avis des clients',
      'user.openTickets':      'Ouvrir ou consulter les tickets',
      'user.updateProfile':    'Mettre à jour le profil',
      'user.featuredKicker':   'En vedette',
      'user.featuredTitle':    'Les produits qui font la meilleure impression',
      'user.viewAllMenu':      'Voir toute la carte',
      'user.reviewsKicker':    'Avis',
      'user.reviewsTitle':     'Ce que disent nos clients',
      'user.reviewsViewAll':   'Voir tout',
      'user.ratingsLabel':     'avis',
      'user.starBadge':        'Produit vedette',
      'user.cardKicker':       'Carte',
      'user.cardTitle':        'Découvrez le produit vedette',
      'user.cardDesc':         'Accédez directement à la vue du produit mis en avant et consultez-le sans distraction.',
      'user.reviewsCardKicker':'Avis',
      'user.reviewsCardTitle': "Découvrez l'avis des autres clients",
      'user.reviewsCardDesc':  'Consultez les avis récents et évaluez vos produits favoris.',
      'user.supportCardKicker':'Support',
      'user.supportCardTitle': 'Gérez vos questions ou incidents',
      'user.supportCardDesc':  'Accédez à vos tickets et restez informé des réponses.',

      // CARTA
      'menu.title':           'Carte de Zyma',
      'menu.subtitle':        'Profitez de nos délicieux plats artisanaux.',
      'menu.subtitleFeatured':"Vue en vedette de notre produit phare.",
      'menu.starCallout':     'Produit vedette :',
      'menu.backToAll':       'Retour à toute la carte',
      'menu.guestMode':       'Mode invité : vous pouvez voir la carte, pour commander vous devez vous connecter.',
      'menu.addToCart':       'Ajouter au panier',
      'menu.rateProduct':     'Évaluer le produit',
      'menu.loginToOrder':    'Connectez-vous pour commander',
      'menu.starBadge':       'Produit vedette',

      // CARRITO
      'cart.title':           'Votre Panier',
      'cart.subtitle':        'Vérifiez vos produits avant de finaliser la commande',
      'cart.empty':           'Votre panier est vide.',
      'cart.continueShopping':'Continuer les achats',
      'cart.paymentMethod':   'Méthode de paiement en ligne',
      'cart.cardOption':      'Carte',
      'cart.bizumOption':     'Bizum',
      'cart.payOnline':       'Payer en ligne',
      'cart.total':           'Total :',

      // PERFIL
      'profile.personalPanel':    'Panneau personnel',
      'profile.description':      "Gérez votre compte depuis un espace plus clair, professionnel et facile à utiliser.",
      'profile.activeAccount':    'Compte actif',
      'profile.strongSecurity':   'Sécurité renforcée',
      'profile.summaryKicker':    'Résumé',
      'profile.accountInfo':      'Informations du compte',
      'profile.name':             'Nom',
      'profile.email':            'Email',
      'profile.role':             'Rôle',
      'profile.status':           'Statut',
      'profile.operative':        'Profil opérationnel',
      'profile.proTipTitle':      'Conseil professionnel',
      'profile.proTipBody':       "Utilisez votre nom complet et révisez votre mot de passe régulièrement pour maintenir une image soignée et sécurisée.",
      'profile.backToPanel':      'Retour au panneau principal',
      'profile.personalDataKicker':'Données personnelles',
      'profile.editProfile':      'Modifier le profil',
      'profile.editDesc':         "Mettez à jour les informations visibles de votre compte pour donner une image plus professionnelle.",
      'profile.fullName':         'Nom complet',
      'profile.nameNote':         "Ce nom s'affichera dans votre espace privé et aide à rendre le profil plus sérieux et ordonné.",
      'profile.securityKicker':   'Sécurité',
      'profile.changePassword':   'Changer de mot de passe',
      'profile.securityDesc':     "Mettez à jour votre clé pour protéger l'accès à votre compte.",
      'profile.currentPassword':  'Mot de passe actuel',
      'profile.newPassword':      'Nouveau mot de passe',
      'profile.confirmNewPwd':    'Confirmer le nouveau mot de passe',
      'profile.passwordNote':     'Combinez lettres, chiffres et symboles pour obtenir un mot de passe plus solide.',
      'profile.updatePassword':   'Mettre à jour le mot de passe',

      // VALORACIONES
      'reviews.title':         'Évaluations des Produits',
      'reviews.subtitle':      'Partagez votre avis sur les produits de Zyma.',
      'reviews.loggedInHint':  "Votre évaluation aide les autres clients à prendre de meilleures décisions.",
      'reviews.loggedOutHint': 'Connectez-vous pour évaluer nos produits.',
      'reviews.noProducts':    'Aucun produit disponible pour le moment.',
      'reviews.noImage':       'Sans image',
      'reviews.update':        'Mettre à jour',
      'reviews.rate':          'Évaluer',
      'reviews.loginHint':     'Connectez-vous',
      'reviews.loginToRate':   'pour évaluer ce produit',
      'reviews.viewReviews':   'Voir les avis',
      'reviews.noReviews':     'Aucun avis disponible.',
      'reviews.restaurantReply':'[Réponse du restaurant Zyma] :',
      'reviews.replied':       'Répondu le :',
      'reviews.backHome':      "Retour à l'accueil",
      'reviews.viewMenu':      'Voir la Carte',

      // TICKETS
      'tickets.kicker':          'Service client',
      'tickets.heroTitle':       'Gestion des incidents et tickets',
      'tickets.heroDesc':        "Depuis ici vous pouvez signaler tout problème avec votre commande, votre compte ou un paiement, et avoir en même temps accès à vos tickets d'achat.",
      'tickets.openLabel':       'ouvertes',
      'tickets.inProgressLabel': 'en cours',
      'tickets.closedLabel':     'fermées',
      'tickets.newIssueKicker':  'Nouvel incident',
      'tickets.newIssueTitle':   "Dites-nous ce qui s'est passé",
      'tickets.newIssueDesc':    'Décrivez clairement le problème pour que nous puissions vous aider plus rapidement.',
      'tickets.subject':         'Objet',
      'tickets.category':        'Catégorie',
      'tickets.catOrder':        'Commande',
      'tickets.catPayment':      'Paiement',
      'tickets.catAccount':      'Compte',
      'tickets.catProduct':      'Produit',
      'tickets.catTechnical':    'Technique',
      'tickets.catGeneral':      'Général',
      'tickets.priority':        'Priorité',
      'tickets.priMedium':       'Moyenne',
      'tickets.priHigh':         'Haute',
      'tickets.priLow':          'Basse',
      'tickets.description':     'Description',
      'tickets.submit':          "Envoyer l'incident",
      'tickets.trackingKicker':  'Suivi',
      'tickets.myIssues':        'Mes incidents',
      'tickets.myIssuesDesc':    "Consultez l'état de chaque incident enregistré depuis votre compte.",
      'tickets.noIssues':        "Vous n'avez pas encore créé d'incidents.",
      'tickets.priorityLabel':   'Priorité',
      'tickets.created':         'Créé :',
      'tickets.updated':         'Mis à jour :',
      'tickets.purchasesKicker': 'Achats',
      'tickets.purchasesTitle':  "Tickets d'achat",
      'tickets.purchasesDesc':   "Vous avez toujours accès à vos preuves de commande.",
      'tickets.noOrders':        "Vous n'avez pas encore de commandes enregistrées.",
      'tickets.date':            'Date :',
      'tickets.statusLabel':     'Statut :',
      'tickets.totalLabel':      'Total :',
      'tickets.viewTicket':      'Voir le ticket',

      // MIS PEDIDOS
      'orders.title':         'Mes Commandes',
      'orders.empty':         "Vous n'avez pas encore de commandes.",
      'orders.viewMenu':      'Voir la Carte',
      'orders.status':        'Statut :',
      'orders.total':         'Total :',
      'orders.date':          'Date :',
      'orders.products':      'Produits :',
      'orders.cancel':        'Annuler la Commande',

      // NOTIFICACIONES
      'notif.title':       'Notifications',
      'notif.subtitle':    'Ici vous verrez les avis sur vos commandes et mises à jour.',
      'notif.unread':      'Non lues :',
      'notif.markAllRead': 'Tout marquer comme lu',
      'notif.empty':       "Vous n'avez pas encore de notifications.",
      'notif.markRead':    'Marquer comme lu',
      'notif.read':        'Lu',

      // FORGOT PASSWORD
      'forgot.title':     'Récupérer le Mot de Passe',
      'forgot.submit':    'Envoyer le lien',
      'forgot.backLogin': 'Retour à la connexion',

      // RESET PASSWORD
      'reset.title':       'Définir un nouveau Mot de Passe',
      'reset.newPassword': 'Nouveau Mot de Passe',
      'reset.confirmPwd':  'Confirmer le Mot de Passe',
      'reset.submit':      'Enregistrer le Mot de Passe',
      'reset.requestNew':  'Demander un nouveau lien',
      'reset.backLogin':   'Retour à la connexion',

      // TICKET VISTA
      'ticket.title':        "Ticket d'achat",
      'ticket.product':      'Produit',
      'ticket.quantity':     'Quantité',
      'ticket.unitPrice':    'Prix unitaire',
      'ticket.vatPct':       'TVA %',
      'ticket.vatAmount':    'Montant TVA',
      'ticket.subtotalNoVat':'Sous-total (HT)',
      'ticket.totalCol':     'Total',
      'ticket.subtotal':     'Sous-total :',
      'ticket.vat':          'TVA :',
      'ticket.totalWithVat': 'Total (TTC) :',
      'ticket.backToTickets':'Retour aux Tickets',

      // ── PRODUITS ──────────────────────────────────────────────────────────
      'product.1': 'Nachos au Fromage',
      'product.2': 'Frites',
      'product.3': 'Hot-dog BBQ',
      'product.4': 'Hot-dog Classique',
      'product.5': 'Hot-dog Végétalien',
      'product.6': 'Soda Cola',
      'product.7': 'Eau Minérale',
    },

    // ─────────────────────────────────────────────────────────────────────────
    en: {
      // Nav
      'nav.enter':           'Sign in',
      'nav.createAccount':   'Create account',
      'nav.home':            'Home',
      'nav.viewMenu':        'View menu',
      'nav.reviews':         'Reviews',
      'nav.tickets':         'Tickets',
      'nav.myProfile':       'My profile',
      'nav.customizeCookies':'Customize cookies',
      'nav.logout':          'Log out',
      'nav.quickMenu':       'Quick menu',
      'nav.cart':            'Cart',
      'nav.notifications':   'Notifications',

      // Footer
      'footer.rights':       '© 2025/2026 Zyma. All rights reserved.',
      'footer.cookiePolicy': 'Cookie Policy',
      'footer.privacy':      'Privacy Policy',
      'footer.legal':        'Legal Notice',

      // Common
      'common.email':           'Email',
      'common.password':        'Password',
      'common.workerCode':      'Worker code (optional)',
      'common.showPassword':    'Show password',
      'common.hidePassword':    'Hide password',
      'common.saveChanges':     'Save changes',
      'common.backToLogin':     'Back to login',

      // INDEX
      'index.heroTitle':        'Zyma. Hotdogs with soul.',
      'index.heroParagraph':    "House recipes, fresh ingredients and an unforgettable flavour. Create your account or sign in and discover the menu.",
      'index.alreadyAccount':   'I already have an account',
      'index.guestView':        'View menu without account',
      'index.reviewsTitle':     'What our customers say',

      // LOGIN
      'login.title':            'Sign in',
      'login.workerHint':       'Worker: e.g. TRAB001<br>Admin: ADMIN',
      'login.submit':           'Sign in',
      'login.noAccount':        "Don't have an account? Register",
      'login.forgot':           'I forgot my password',

      // REGISTRO
      'register.title':           'Create your account',
      'register.passwordLabel':   'Password',
      'register.confirmPassword': 'Confirm password',
      'register.passwordMin':     'Minimum 6 characters.',
      'register.repeatHint':      'Repeat your password to confirm.',
      'register.workerHint':      "If you have one, you'll access special features.",
      'register.submit':          'Register',
      'register.hasAccount':      'Already have an account? Sign in',
      'register.successTitle':    'Registration successful!',
      'register.successCreated':  'Your account has been created successfully.',
      'register.redirectText':    'Redirecting to login in',
      'register.redirectSeconds': 'seconds...',
      'register.goLogin':         'Go to Login now',

      // USUARIO
      'user.welcomeKicker':    'Welcome to Zyma',
      'user.personalPanel':    'Personal panel',
      'user.greeting':         'Hello,',
      'user.description':      "Discover a more refined experience, with quick access to the menu, real reviews and a much cleaner panel to navigate the site.",
      'user.viewMenu':         'View the menu',
      'user.starProduct':      'Star product',
      'user.myOrders':         'My orders',
      'user.productsLabel':    'products available',
      'user.reviewsLabel':     'real reviews',
      'user.avgSatisfaction':  'average satisfaction',
      'user.quickAccess':      'Quick access',
      'user.quickTitle':       'Everything important at a glance',
      'user.tipTitle':         'Tip',
      'user.tipBody':          "Start with the menu to discover the best-rated products and add them to your cart in just a few steps.",
      'user.backToStar':       'Go to star product',
      'user.exploreMenu':      'Explore full menu',
      'user.seeReviews':       'See customer reviews',
      'user.openTickets':      'Open or review tickets',
      'user.updateProfile':    'Update profile',
      'user.featuredKicker':   'Featured',
      'user.featuredTitle':    'Products that make the best impression',
      'user.viewAllMenu':      'View full menu',
      'user.reviewsKicker':    'Reviews',
      'user.reviewsTitle':     'What our customers say',
      'user.reviewsViewAll':   'View all',
      'user.ratingsLabel':     'reviews',
      'user.starBadge':        'Star product',
      'user.cardKicker':       'Menu',
      'user.cardTitle':        'Discover the star product',
      'user.cardDesc':         'Go directly to the featured product view and browse it without distractions.',
      'user.reviewsCardKicker':'Reviews',
      'user.reviewsCardTitle': 'See what other customers think',
      'user.reviewsCardDesc':  'Discover recent reviews and rate your favourite products.',
      'user.supportCardKicker':'Support',
      'user.supportCardTitle': 'Manage questions or incidents',
      'user.supportCardDesc':  'Access your tickets and stay up to date with replies.',

      // CARTA
      'menu.title':           'Zyma Menu',
      'menu.subtitle':        'Enjoy our delicious artisanal dishes.',
      'menu.subtitleFeatured':"Featured view of our star product.",
      'menu.starCallout':     'Star product:',
      'menu.backToAll':       'Back to full menu',
      'menu.guestMode':       'Guest mode: you can view the menu, to order you need to sign in.',
      'menu.addToCart':       'Add to cart',
      'menu.rateProduct':     'Rate product',
      'menu.loginToOrder':    'Sign in to order',
      'menu.starBadge':       'Star product',

      // CARRITO
      'cart.title':           'Your Cart',
      'cart.subtitle':        'Review your products before finalising the order',
      'cart.empty':           'Your cart is empty.',
      'cart.continueShopping':'Continue shopping',
      'cart.paymentMethod':   'Online payment method',
      'cart.cardOption':      'Card',
      'cart.bizumOption':     'Bizum',
      'cart.payOnline':       'Pay online',
      'cart.total':           'Total:',

      // PERFIL
      'profile.personalPanel':    'Personal panel',
      'profile.description':      "Manage your account from a cleaner, more professional and easy-to-use space.",
      'profile.activeAccount':    'Active account',
      'profile.strongSecurity':   'Enhanced security',
      'profile.summaryKicker':    'Summary',
      'profile.accountInfo':      'Account information',
      'profile.name':             'Name',
      'profile.email':            'Email',
      'profile.role':             'Role',
      'profile.status':           'Status',
      'profile.operative':        'Operational profile',
      'profile.proTipTitle':      'Professional tip',
      'profile.proTipBody':       "Use your full name and review your password frequently to maintain a more polished and secure image.",
      'profile.backToPanel':      'Back to main panel',
      'profile.personalDataKicker':'Personal data',
      'profile.editProfile':      'Edit profile',
      'profile.editDesc':         "Update the visible information on your account to give a more professional image.",
      'profile.fullName':         'Full name',
      'profile.nameNote':         "This name will be shown in your private area and helps make the profile look more professional.",
      'profile.securityKicker':   'Security',
      'profile.changePassword':   'Change password',
      'profile.securityDesc':     "Update your password to keep your account access protected.",
      'profile.currentPassword':  'Current password',
      'profile.newPassword':      'New password',
      'profile.confirmNewPwd':    'Confirm new password',
      'profile.passwordNote':     'Combine letters, numbers and symbols for a stronger password.',
      'profile.updatePassword':   'Update password',

      // VALORACIONES
      'reviews.title':         'Product Reviews',
      'reviews.subtitle':      'Share your opinion about Zyma products.',
      'reviews.loggedInHint':  "Your review helps other customers make better decisions.",
      'reviews.loggedOutHint': 'Sign in to rate our products.',
      'reviews.noProducts':    'No products available at this time.',
      'reviews.noImage':       'No image',
      'reviews.update':        'Update',
      'reviews.rate':          'Rate',
      'reviews.loginHint':     'Sign in',
      'reviews.loginToRate':   'to rate this product',
      'reviews.viewReviews':   'View reviews',
      'reviews.noReviews':     'No reviews available.',
      'reviews.restaurantReply':'[Zyma restaurant response]:',
      'reviews.replied':       'Replied:',
      'reviews.backHome':      'Back to home',
      'reviews.viewMenu':      'View Menu',

      // TICKETS
      'tickets.kicker':          'Customer service',
      'tickets.heroTitle':       'Incident and ticket management',
      'tickets.heroDesc':        "From here you can report any problem with your order, your account or a payment, and at the same time keep your purchase tickets handy.",
      'tickets.openLabel':       'open',
      'tickets.inProgressLabel': 'in progress',
      'tickets.closedLabel':     'closed',
      'tickets.newIssueKicker':  'New issue',
      'tickets.newIssueTitle':   'Tell us what happened',
      'tickets.newIssueDesc':    'Describe the problem clearly so we can help you faster.',
      'tickets.subject':         'Subject',
      'tickets.category':        'Category',
      'tickets.catOrder':        'Order',
      'tickets.catPayment':      'Payment',
      'tickets.catAccount':      'Account',
      'tickets.catProduct':      'Product',
      'tickets.catTechnical':    'Technical',
      'tickets.catGeneral':      'General',
      'tickets.priority':        'Priority',
      'tickets.priMedium':       'Medium',
      'tickets.priHigh':         'High',
      'tickets.priLow':          'Low',
      'tickets.description':     'Description',
      'tickets.submit':          'Submit issue',
      'tickets.trackingKicker':  'Tracking',
      'tickets.myIssues':        'My issues',
      'tickets.myIssuesDesc':    "Check the status of each issue registered from your account.",
      'tickets.noIssues':        "You haven't created any issues yet.",
      'tickets.priorityLabel':   'Priority',
      'tickets.created':         'Created:',
      'tickets.updated':         'Updated:',
      'tickets.purchasesKicker': 'Purchases',
      'tickets.purchasesTitle':  'Purchase tickets',
      'tickets.purchasesDesc':   "You still have access to your order receipts.",
      'tickets.noOrders':        "You don't have any registered orders yet.",
      'tickets.date':            'Date:',
      'tickets.statusLabel':     'Status:',
      'tickets.totalLabel':      'Total:',
      'tickets.viewTicket':      'View ticket',

      // MIS PEDIDOS
      'orders.title':         'My Orders',
      'orders.empty':         'You have no orders placed.',
      'orders.viewMenu':      'View Menu',
      'orders.status':        'Status:',
      'orders.total':         'Total:',
      'orders.date':          'Date:',
      'orders.products':      'Products:',
      'orders.cancel':        'Cancel Order',

      // NOTIFICACIONES
      'notif.title':       'Notifications',
      'notif.subtitle':    "Here you'll see notices about your orders and updates.",
      'notif.unread':      'Unread:',
      'notif.markAllRead': 'Mark all as read',
      'notif.empty':       "You don't have any notifications yet.",
      'notif.markRead':    'Mark as read',
      'notif.read':        'Read',

      // FORGOT PASSWORD
      'forgot.title':     'Recover Password',
      'forgot.submit':    'Send link',
      'forgot.backLogin': 'Back to login',

      // RESET PASSWORD
      'reset.title':       'Set new Password',
      'reset.newPassword': 'New Password',
      'reset.confirmPwd':  'Confirm Password',
      'reset.submit':      'Save Password',
      'reset.requestNew':  'Request new link',
      'reset.backLogin':   'Back to login',

      // TICKET VISTA
      'ticket.title':        'Purchase ticket',
      'ticket.product':      'Product',
      'ticket.quantity':     'Quantity',
      'ticket.unitPrice':    'Unit price',
      'ticket.vatPct':       'VAT %',
      'ticket.vatAmount':    'VAT amount',
      'ticket.subtotalNoVat':'Subtotal (ex. VAT)',
      'ticket.totalCol':     'Total',
      'ticket.subtotal':     'Subtotal:',
      'ticket.vat':          'VAT:',
      'ticket.totalWithVat': 'Total (incl. VAT):',
      'ticket.backToTickets':'Back to Tickets',

      // ── PRODUCTS ──────────────────────────────────────────────────────────
      'product.1': 'Nachos with Cheese',
      'product.2': 'French Fries',
      'product.3': 'BBQ Hotdog',
      'product.4': 'Classic Hotdog',
      'product.5': 'Vegan Hotdog',
      'product.6': 'Cola Drink',
      'product.7': 'Mineral Water',
    }
  };

  // ─── HELPERS ──────────────────────────────────────────────────────────────
  function getLang() {
    return localStorage.getItem('zyma_lang') || 'es';
  }

  function setLang(lang) {
    localStorage.setItem('zyma_lang', lang);
  }

  function t(key) {
    var lang = getLang();
    return (T[lang] && T[lang][key] !== undefined)
      ? T[lang][key]
      : (T['es'][key] !== undefined ? T['es'][key] : key);
  }

  // ─── DOM TRANSLATION ──────────────────────────────────────────────────────
  function applyTranslations() {
    var lang = getLang();

    // data-i18n → textContent (safe for elements without child elements)
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      var text = t(key);
      if (el.children.length === 0) {
        el.textContent = text;
      } else {
        // Only replace the first non-empty text node
        var replaced = false;
        el.childNodes.forEach(function (node) {
          if (!replaced && node.nodeType === 3 && node.textContent.trim() !== '') {
            node.textContent = text + ' ';
            replaced = true;
          }
        });
      }
    });

    // data-i18n-html → innerHTML (for strings that contain HTML like <br>)
    document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-html');
      el.innerHTML = t(key);
    });

    // data-i18n-placeholder → placeholder attribute
    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-placeholder');
      el.placeholder = t(key);
    });

    // data-i18n-aria → aria-label attribute
    document.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-aria');
      el.setAttribute('aria-label', t(key));
    });

    // Update <html lang="...">
    document.documentElement.lang = lang;
  }

  // ─── FLOATING BUTTON ──────────────────────────────────────────────────────
  function injectButton() {
    var langs = [
      { code: 'es', flag: '🇪🇸', label: 'ES', name: 'Español' },
      { code: 'fr', flag: '🇫🇷', label: 'FR', name: 'Français' },
      { code: 'en', flag: '🇬🇧', label: 'EN', name: 'English' }
    ];
    var currentCode = getLang();
    var current = langs.find(function (l) { return l.code === currentCode; }) || langs[0];

    // Wrapper
    var wrapper = document.createElement('div');
    wrapper.id = 'zyma-lang-sw';
    wrapper.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;font-family:Montserrat,sans-serif;';

    // Dropdown panel
    var panel = document.createElement('div');
    panel.id = 'zyma-lang-panel';
    panel.style.cssText = [
      'position:absolute;bottom:calc(100% + 10px);right:0;',
      'background:#fff;border-radius:14px;',
      'box-shadow:0 8px 32px rgba(0,0,0,0.18);',
      'overflow:hidden;display:none;min-width:152px;',
      'border:1px solid rgba(0,0,0,0.07);'
    ].join('');

    langs.forEach(function (lang) {
      var btn = document.createElement('button');
      var isActive = lang.code === currentCode;
      btn.style.cssText = [
        'display:flex;align-items:center;gap:10px;width:100%;',
        'padding:11px 16px;background:' + (isActive ? '#f5ede0' : 'transparent') + ';',
        'border:none;cursor:pointer;font-family:inherit;font-size:14px;',
        'font-weight:' + (isActive ? '700' : '500') + ';',
        'color:#45050C;text-align:left;transition:background .15s;'
      ].join('');
      btn.innerHTML = '<span style="font-size:18px">' + lang.flag + '</span>'
        + '<span>' + lang.name + '</span>';

      btn.addEventListener('mouseenter', function () {
        if (lang.code !== getLang()) btn.style.background = '#fdf6ec';
      });
      btn.addEventListener('mouseleave', function () {
        btn.style.background = lang.code === getLang() ? '#f5ede0' : 'transparent';
      });

      btn.addEventListener('click', function () {
        setLang(lang.code);
        applyTranslations();
        currentCode = lang.code;
        current = lang;
        toggleBtn.innerHTML = '<span style="font-size:20px">' + lang.flag + '</span>'
          + '<span style="letter-spacing:.5px">' + lang.label + '</span>'
          + '<span style="font-size:11px;opacity:.7">▲</span>';
        closePanel();
        // Refresh active highlight
        panel.querySelectorAll('button').forEach(function (b, i) {
          var isNowActive = langs[i].code === lang.code;
          b.style.background = isNowActive ? '#f5ede0' : 'transparent';
          b.style.fontWeight = isNowActive ? '700' : '500';
        });
      });

      panel.appendChild(btn);
    });

    // Toggle button
    var toggleBtn = document.createElement('button');
    toggleBtn.id = 'zyma-lang-btn';
    toggleBtn.style.cssText = [
      'background:#45050C;color:#fff;border:none;border-radius:50px;',
      'padding:10px 18px;cursor:pointer;font-family:inherit;font-size:13px;',
      'font-weight:700;display:flex;align-items:center;gap:8px;',
      'box-shadow:0 4px 16px rgba(69,5,12,.45);transition:background .2s,transform .15s;',
      'white-space:nowrap;'
    ].join('');
    toggleBtn.innerHTML = '<span style="font-size:20px">' + current.flag + '</span>'
      + '<span style="letter-spacing:.5px">' + current.label + '</span>'
      + '<span style="font-size:11px;opacity:.7">▲</span>';

    toggleBtn.addEventListener('mouseenter', function () {
      toggleBtn.style.background = '#720E07';
      toggleBtn.style.transform = 'translateY(-2px)';
    });
    toggleBtn.addEventListener('mouseleave', function () {
      toggleBtn.style.background = '#45050C';
      toggleBtn.style.transform = 'translateY(0)';
    });

    var open = false;
    function closePanel() {
      open = false;
      panel.style.display = 'none';
      toggleBtn.querySelector('span:last-child').style.transform = 'rotate(0deg)';
    }

    toggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      open = !open;
      panel.style.display = open ? 'block' : 'none';
      toggleBtn.querySelector('span:last-child').style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
    });

    document.addEventListener('click', function () {
      if (open) closePanel();
    });

    wrapper.appendChild(panel);
    wrapper.appendChild(toggleBtn);
    document.body.appendChild(wrapper);
  }

  // ─── INIT ─────────────────────────────────────────────────────────────────
  function init() {
    applyTranslations();
    injectButton();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
