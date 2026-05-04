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
      'tickets.heroKicker':        'Tus compras',
      'tickets.historyKicker':     'Historial',
      'tickets.myPurchasesTitle':  'Mis tickets de compra',
      'tickets.myPurchasesDesc':   'Accede a los detalles de cada pedido y descarga tus comprobantes.',
      'tickets.makeOrder':         'Hacer pedido',
      'tickets.problemText':       '¿Tienes un problema con tu pedido?',
      'tickets.openIncidence':     'Abrir incidencia',
      'tickets.supportKicker':     'Soporte y atención',
      'tickets.incidenciasTitle':  'Gestión de incidencias',
      'tickets.incidenciasDesc':   'Reporta cualquier problema con tu pedido, tu cuenta, un pago o cualquier otro tema. Te ayudaremos lo antes posible.',
      'tickets.needPurchaseProof': '¿Necesitas tu comprobante de compra?',
      'tickets.viewPurchaseTickets':'Ver tickets de compra',

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

      // ── TRABAJADOR ────────────────────────────────────────────────────────
      'worker.operationsCenter':   'Centro de operaciones',
      'worker.welcome':            'Bienvenido al centro de operaciones',
      'worker.stableOps':          'Operativa estable',
      'worker.activeAttention':    'Atención activa',
      'worker.criticalPriority':   'Máxima prioridad',
      'worker.managePendingOrders':'Gestionar pedidos',
      'worker.manageOrdersDesc':   'Supervisa pedidos pendientes, actualiza estados y mantén el flujo de cocina bajo control.',
      'worker.editMenu':           'Editar carta',
      'worker.editMenuDesc':       'Ajusta productos, disponibilidad y detalles de la carta para evitar errores de servicio.',
      'worker.viewStats':          'Ver estadísticas',
      'worker.viewStatsDesc':      'Consulta el rendimiento del negocio y toma decisiones rápidas con datos del día.',
      'worker.recommendations':    'Recomendaciones inteligentes',
      'worker.pendingOrders':      'Pedidos pendientes',
      'worker.inProgress':         'En proceso',
      'worker.deliveredToday':     'Entregados hoy',
      'worker.lowStockItems':      'Ingredientes con stock bajo',

      // ── POLÍTICA DE COOKIES ────────────────────────────────────────────────
      'cookies.title':             'Política de Cookies',
      'cookies.subtitle':          'Esta web usa cookies para ofrecer una experiencia más rápida, útil y personalizada para nuestros clientes.',
      'cookies.whatAreCookies':    '1. Qué son las cookies',
      'cookies.whatAreText':       'Son pequeños archivos que se guardan en tu dispositivo para recordar preferencias y mejorar la navegación.',
      'cookies.typesCookies':      '2. Tipos de cookies que usamos',
      'cookies.typesText':         'Cookies técnicas (necesarias), analíticas (medición de uso) y de marketing (contenido promocional personalizado).',
      'cookies.purposes':          '3. Finalidades',
      'cookies.purposesText':      'Permiten mantener tu Sesión, recordar configuraciones, analizar comportamiento de uso y optimizar el rendimiento del sitio.',
      'cookies.management':        '4. Gestión del consentimiento',
      'cookies.managementText':    'Puedes aceptar, rechazar o personalizar las cookies opcionales desde el popup al iniciar Sesión.',
      'cookies.disable':           '5. Cómo desactivar cookies',
      'cookies.disableText':       'También puedes borrar o bloquear cookies desde la configuración de tu navegador en cualquier momento.',
      'cookies.updates':           '6. Actualizaciones',
      'cookies.updatesText':       'Podemos actualizar esta política para reflejar cambios legales o técnicos. Publicaremos siempre la versión vigente en esta página.',

      // ── NAVEGACIÓN ADICIONAL ────────────────────────────────────────────
      'nav.incidents':          'Incidencias',
      'nav.ticketsPurchase':    'Tickets de compra',
      'nav.workerPanel':        'Panel',
      'nav.workerOrders':       'Pedidos',
      'nav.workerEditMenu':     'Editar carta',
      'nav.workerChangePrice':  'Cambiar precios',
      'nav.workerStats':        'Estadísticas',
      'nav.workerIncidents':    'Incidencias',

      // ── USUARIO ADICIONAL ───────────────────────────────────────────────
      'user.openIncidents':     'Abrir o revisar incidencias',
      'user.myPurchaseTickets': 'Ver mis tickets de compra',
      'user.favoritesKicker':   'Favoritos del cliente',
      'user.favoritesTitle':    'Los productos que más compran nuestros clientes',
      'user.viewAll':           'Ver toda la carta',
      'user.reviewsSection':    'Reseñas',
      'user.reviewsViewAllLink':'Ver todas',
      'user.new':               'Nuevo',
      'user.sold':              'vendidas',
      'user.starCardDesc':      'Accede directamente al hotdog estrella del momento en una vista dedicada solo para ese producto.',
      'user.purchasesCardKicker': 'Compras',
      'user.purchasesCardTitle':  'Revisa tus comprobantes',
      'user.purchasesCardDesc':   'Accede a tus tickets de compra y facturas cuando lo necesites.',

      // ── CARTA ADICIONAL ────────────────────────────────────────────────
      'menu.allergenGluten':    'Gluten',
      'menu.allergenDairy':     'Lácteos',
      'menu.allergenSoy':       'Soja',

      // ── ADMIN ──────────────────────────────────────────────────────────
      'admin.panelTitle':       'Panel administrativo',
      'admin.panelDesc':        'Resumen de pedidos, inventario y usuarios. Accede rápidamente a las secciones principales.',
      'admin.todayOrders':      'Pedidos del día',
      'admin.todayRevenue':     'Ingresos del día',
      'admin.activeOrders':     'Pedidos activos',
      'admin.preparingOrders':  'Pedidos en preparación',
      'admin.redIngredients':   'Ingredientes en rojo',
      'admin.topProduct':       'Producto más vendido',
      'admin.internalNotif':    'Notificaciones internas',
      'admin.registeredUsers':  'Usuarios registrados',
      'admin.realTimeOrders':   'Pedidos en tiempo real',
      'admin.addUser':          'Añadir usuario',
      'admin.idOrder':          'ID Pedido',
      'admin.idCol':            'ID',
      'admin.nameCol':          'Nombre',
      'admin.emailCol':         'Email',
      'admin.roleCol':          'Rol',
      'admin.codeCol':          'Código',
      'admin.statusCol':        'Estado',
      'admin.actionsCol':       'Acciones',
      'admin.saveRole':         'Guardar rol',
      'admin.block':            'Bloquear',
      'admin.unblock':          'Desbloquear',
      'admin.delete':           'Eliminar',
      'admin.yourUser':         'Tu usuario',
      'admin.createUser':       'Crear usuario',
      'admin.noActiveOrders':   'No hay pedidos activos disponibles.',
      'admin.ordersLink':       'Pedidos',
      'admin.inventoryLink':    'Inventario',
      'admin.productsLink':     'Productos',
      'admin.todayOrdersDesc':     'Pedidos registrados hoy',
      'admin.noDateAvail':         'Fecha no disponible',
      'admin.todayRevenueDesc':    'Ventas de hoy',
      'admin.activeOrdersDesc':    'Pedidos en curso',
      'admin.preparingOrdersDesc': 'En proceso ahora',
      'admin.criticalInventory':   'Inventario crítico',
      'admin.inventoryNotFound':   'Inventario no detectado',
      'admin.salesSummary':        'Resumen de ventas',
      'admin.noProducts':          'Sin productos',
      'admin.unreadNotif':         'No leídas',
      'admin.notifsNotFound':      'Notificaciones no detectadas',
      'admin.usersDesc':           'Clientes, empleados y administradores',
      'admin.updated':             'Actualizado',
      'confirmation.orderSummary': 'Resumen del Pedido',
      'confirmation.date':         'Fecha:',

      // ── EDITAR CARTA ───────────────────────────────────────────────────
      'editMenu.title':              'Cambiar precios y productos',
      'editMenu.backToWorker':       'Volver al panel de trabajador',
      'editMenu.changeSection':      'Cambiar precios de productos',
      'editMenu.changeSectionDesc':  'Cambia el precio, pulsa guardar y ese importe será el que se use en la carta y en la compra.',
      'editMenu.newProduct':         'Añadir nuevo producto',
      'editMenu.productName':        'Nombre del producto',
      'editMenu.priceEUR':           'Precio en EUR',
      'editMenu.imagePath':          'Ruta de la imagen',
      'editMenu.createProduct':      'Crear producto',
      'editMenu.existingProducts':   'Productos existentes',
      'editMenu.currentPrice':       'Precio actual en EUR',
      'editMenu.saveChanges':        'Guardar precio y cambios',
      'editMenu.delete':             'Eliminar',

      // ── ESTADÍSTICAS ───────────────────────────────────────────────────
      'stats.title':            'Estadísticas - Trabajador',
      'stats.backToPanel':      'Volver al Panel de Control',
      'stats.pendingOrders':    'Pedidos Pendientes',
      'stats.totalOrders':      'Pedidos Totales',
      'stats.inventoryTitle':   'Inventario de Ingredientes (únicos)',
      'stats.ingredient':       'Ingrediente',
      'stats.quantity':         'Cantidad',
      'stats.unit':             'Unidad',
      'stats.minStock':         'Stock Mínimo',
      'stats.statusCol':        'Estado',
      'stats.lowStock':         'Bajo stock',
      'stats.normal':           'Normal',
      'stats.noIngredients':    'No hay ingredientes registrados en la base de datos.',

      // ── GESTIONAR PEDIDOS ──────────────────────────────────────────────
      'manageOrders.title':       'Gestionar Pedidos',
      'manageOrders.backToPanel': 'Volver al Panel de Control',
      'manageOrders.noOrders':    'No hay pedidos.',
      'manageOrders.preparing':   'Preparando',
      'manageOrders.cancel':      'Cancelar',
      'manageOrders.delivering':  'Entregando',
      'manageOrders.ready':       'Listo',
      'manageOrders.delivered':   'Entregado',

      // ── CONFIRMACIÓN PEDIDO ────────────────────────────────────────────
      'confirmation.title':      '¡Pedido confirmado!',
      'confirmation.product':    'Producto',
      'confirmation.quantity':   'Cantidad',
      'confirmation.unitPrice':  'Precio Unitario',
      'confirmation.subtotal':   'Subtotal',
      'confirmation.viewTicket': 'Ver Ticket',
      'confirmation.allTickets': 'Todos mis Tickets',
      'confirmation.backHome':   'Volver al Inicio',

      // ── PEDIDO CONFIRMADO ──────────────────────────────────────────────
      'confirmed.title':         '¡Pedido Confirmado!',
      'confirmed.preparing':     'Nuestro equipo está preparando tu pedido. ¡Gracias por confiar en Zyma!',
      'confirmed.myOrders':      'Ver Mis Pedidos',
      'confirmed.keepShopping':  'Seguir Comprando',

      // ── BIZUM ─────────────────────────────────────────────────────────────
      'bizum.title':         'Completa tu pago por Bizum',
      'bizum.orderPrefix':   'Tu pedido',
      'bizum.orderSuffix':   'ya se ha creado y está pendiente de confirmación.',
      'bizum.amount':        'Importe:',
      'bizum.sendTo':        'Enviar a:',
      'bizum.concept':       'Concepto:',
      'bizum.currentStatus': 'Estado actual:',
      'bizum.step1':         'Abre tu app bancaria y entra en la opción de Bizum.',
      'bizum.step2':         'Envíanos el importe exacto del pedido al teléfono indicado arriba.',
      'bizum.step3':         'Escribe el concepto exactamente como aparece para poder localizar tu pago.',
      'bizum.step4':         'Cuando lo completes, tu pedido seguirá apareciendo en "Mis pedidos" mientras lo revisamos.',
      'bizum.noPhone':       'Todavía no has configurado el teléfono receptor de Bizum. Añade la variable BIZUM_PHONE en el servidor para mostrar el número real.',
      'bizum.viewTicket':    'Ver ticket',
      'bizum.viewOrders':    'Ver mis pedidos',
      'bizum.backHome':      'Volver al inicio',
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
      'tickets.heroKicker':        'Vos achats',
      'tickets.historyKicker':     'Historique',
      'tickets.myPurchasesTitle':  "Mes tickets d'achat",
      'tickets.myPurchasesDesc':   'Accédez aux détails de chaque commande et téléchargez vos reçus.',
      'tickets.makeOrder':         'Passer commande',
      'tickets.problemText':       'Vous avez un problème avec votre commande ?',
      'tickets.openIncidence':     'Ouvrir un incident',
      'tickets.supportKicker':     'Support et service',
      'tickets.incidenciasTitle':  'Gestion des incidents',
      'tickets.incidenciasDesc':   'Signalez tout problème avec votre commande, votre compte, un paiement ou tout autre sujet. Nous vous aiderons dès que possible.',
      'tickets.needPurchaseProof': "Vous avez besoin de votre reçu d'achat ?",
      'tickets.viewPurchaseTickets':"Voir les tickets d'achat",

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

      // ── TRABAJADOR ────────────────────────────────────────────────────────
      'worker.operationsCenter':   'Centre opérationnel',
      'worker.welcome':            'Bienvenue au centre opérationnel',
      'worker.stableOps':          'Opérations stables',
      'worker.activeAttention':    'Attention active',
      'worker.criticalPriority':   'Priorité maximale',
      'worker.managePendingOrders':'Gérer les commandes',
      'worker.manageOrdersDesc':   'Supervisez les commandes en attente, mettez à jour les statuts et maintenez le flux de la cuisine sous contrôle.',
      'worker.editMenu':           'Modifier la carte',
      'worker.editMenuDesc':       'Ajustez les produits, la disponibilité et les détails de la carte pour éviter les erreurs de service.',
      'worker.viewStats':          'Voir les statistiques',
      'worker.viewStatsDesc':      'Consultez les performances commerciales et prenez des décisions rapides avec les données du jour.',
      'worker.recommendations':    'Recommandations intelligentes',
      'worker.pendingOrders':      'Commandes en attente',
      'worker.inProgress':         'En cours de traitement',
      'worker.deliveredToday':     'Livrées aujourd\'hui',
      'worker.lowStockItems':      'Ingrédients en stock faible',

      // ── POLITIQUE DE COOKIES ──────────────────────────────────────────────
      'cookies.title':             'Politique de Cookies',
      'cookies.subtitle':          "Ce site utilise des cookies pour offrir une expérience plus rapide, utile et personnalisée à nos clients.",
      'cookies.whatAreCookies':    '1. Que sont les cookies',
      'cookies.whatAreText':       'Ce sont de petits fichiers stockés sur votre appareil pour mémoriser les préférences et améliorer la navigation.',
      'cookies.typesCookies':      '2. Types de cookies que nous utilisons',
      'cookies.typesText':         'Cookies techniques (nécessaires), analytiques (mesure d\'utilisation) et marketing (contenu promotionnel personnalisé).',
      'cookies.purposes':          '3. Finalités',
      'cookies.purposesText':      'Ils permettent de maintenir votre session, de mémoriser les paramètres, d\'analyser le comportement d\'utilisation et d\'optimiser les performances du site.',
      'cookies.management':        '4. Gestion du consentement',
      'cookies.managementText':    'Vous pouvez accepter, refuser ou personnaliser les cookies facultatifs à partir de la fenêtre contextuelle au démarrage de la session.',
      'cookies.disable':           '5. Comment désactiver les cookies',
      'cookies.disableText':       'Vous pouvez également supprimer ou bloquer les cookies à partir des paramètres de votre navigateur à tout moment.',
      'cookies.updates':           '6. Mises à jour',
      'cookies.updatesText':       'Nous pouvons mettre à jour cette politique pour refléter les changements juridiques ou techniques. Nous publierons toujours la version en vigueur sur cette page.',

      'nav.incidents':          'Incidents',
      'nav.ticketsPurchase':    "Tickets d'achat",
      'nav.workerPanel':        'Panneau',
      'nav.workerOrders':       'Commandes',
      'nav.workerEditMenu':     'Modifier la carte',
      'nav.workerChangePrice':  'Changer les prix',
      'nav.workerStats':        'Statistiques',
      'nav.workerIncidents':    'Incidents',
      'user.openIncidents':     'Ouvrir ou réviser les incidents',
      'user.myPurchaseTickets': "Voir mes tickets d'achat",
      'user.favoritesKicker':   'Favoris des clients',
      'user.favoritesTitle':    'Les produits que nos clients achètent le plus',
      'user.viewAll':           'Voir toute la carte',
      'user.reviewsSection':    'Avis',
      'user.reviewsViewAllLink':'Voir tout',
      'user.new':               'Nouveau',
      'user.sold':              'vendus',
      'user.starCardDesc':      "Accédez directement au hot-dog vedette du moment dans une vue dédiée uniquement à ce produit.",
      'user.purchasesCardKicker': 'Achats',
      'user.purchasesCardTitle':  'Consultez vos reçus',
      'user.purchasesCardDesc':   "Accédez à vos tickets d'achat et factures quand vous en avez besoin.",
      'menu.allergenGluten':    'Gluten',
      'menu.allergenDairy':     'Lait',
      'menu.allergenSoy':       'Soja',
      'admin.panelTitle':       "Panneau d'administration",
      'admin.panelDesc':        'Résumé des commandes, inventaire et utilisateurs. Accédez rapidement aux sections principales.',
      'admin.todayOrders':      'Commandes du jour',
      'admin.todayRevenue':     'Revenus du jour',
      'admin.activeOrders':     'Commandes actives',
      'admin.preparingOrders':  'Commandes en préparation',
      'admin.redIngredients':   'Ingrédients en alerte',
      'admin.topProduct':       'Produit le plus vendu',
      'admin.internalNotif':    'Notifications internes',
      'admin.registeredUsers':  'Utilisateurs enregistrés',
      'admin.realTimeOrders':   'Commandes en temps réel',
      'admin.addUser':          'Ajouter un utilisateur',
      'admin.idOrder':          'ID Commande',
      'admin.idCol':            'ID',
      'admin.nameCol':          'Nom',
      'admin.emailCol':         'Email',
      'admin.roleCol':          'Rôle',
      'admin.codeCol':          'Code',
      'admin.statusCol':        'Statut',
      'admin.actionsCol':       'Actions',
      'admin.saveRole':         'Enregistrer le rôle',
      'admin.block':            'Bloquer',
      'admin.unblock':          'Débloquer',
      'admin.delete':           'Supprimer',
      'admin.yourUser':         'Votre utilisateur',
      'admin.createUser':       'Créer un utilisateur',
      'admin.noActiveOrders':   'Aucune commande active disponible.',
      'admin.ordersLink':       'Commandes',
      'admin.inventoryLink':    'Inventaire',
      'admin.productsLink':     'Produits',
      'admin.todayOrdersDesc':     "Commandes enregistrées aujourd'hui",
      'admin.noDateAvail':         'Date non disponible',
      'admin.todayRevenueDesc':    'Ventes du jour',
      'admin.activeOrdersDesc':    'Commandes en cours',
      'admin.preparingOrdersDesc': 'En traitement actuellement',
      'admin.criticalInventory':   'Inventaire critique',
      'admin.inventoryNotFound':   'Inventaire non détecté',
      'admin.salesSummary':        'Résumé des ventes',
      'admin.noProducts':          'Sans produits',
      'admin.unreadNotif':         'Non lues',
      'admin.notifsNotFound':      'Notifications non détectées',
      'admin.usersDesc':           'Clients, employés et administrateurs',
      'admin.updated':             'Mis à jour',
      'confirmation.orderSummary': 'Récapitulatif de la Commande',
      'confirmation.date':         'Date :',
      'editMenu.title':              'Modifier les prix et les produits',
      'editMenu.backToWorker':       "Retour au panneau employé",
      'editMenu.changeSection':      'Modifier les prix des produits',
      'editMenu.changeSectionDesc':  "Modifiez le prix, appuyez sur enregistrer et ce montant sera utilisé sur la carte.",
      'editMenu.newProduct':         'Ajouter un nouveau produit',
      'editMenu.productName':        'Nom du produit',
      'editMenu.priceEUR':           'Prix en EUR',
      'editMenu.imagePath':          "Chemin de l'image",
      'editMenu.createProduct':      'Créer le produit',
      'editMenu.existingProducts':   'Produits existants',
      'editMenu.currentPrice':       'Prix actuel en EUR',
      'editMenu.saveChanges':        'Enregistrer le prix et les modifications',
      'editMenu.delete':             'Supprimer',
      'stats.title':            'Statistiques - Employé',
      'stats.backToPanel':      'Retour au Panneau de Contrôle',
      'stats.pendingOrders':    'Commandes en attente',
      'stats.totalOrders':      'Total des commandes',
      'stats.inventoryTitle':   'Inventaire des Ingrédients (uniques)',
      'stats.ingredient':       'Ingrédient',
      'stats.quantity':         'Quantité',
      'stats.unit':             'Unité',
      'stats.minStock':         'Stock Minimum',
      'stats.statusCol':        'Statut',
      'stats.lowStock':         'Stock bas',
      'stats.normal':           'Normal',
      'stats.noIngredients':    'Aucun ingrédient enregistré dans la base de données.',
      'manageOrders.title':       'Gérer les Commandes',
      'manageOrders.backToPanel': 'Retour au Panneau de Contrôle',
      'manageOrders.noOrders':    'Aucune commande.',
      'manageOrders.preparing':   'En préparation',
      'manageOrders.cancel':      'Annuler',
      'manageOrders.delivering':  'En livraison',
      'manageOrders.ready':       'Prêt',
      'manageOrders.delivered':   'Livré',
      'confirmation.title':      'Commande confirmée !',
      'confirmation.product':    'Produit',
      'confirmation.quantity':   'Quantité',
      'confirmation.unitPrice':  'Prix unitaire',
      'confirmation.subtotal':   'Sous-total',
      'confirmation.viewTicket': 'Voir le Ticket',
      'confirmation.allTickets': 'Tous mes Tickets',
      'confirmation.backHome':   "Retour à l'Accueil",
      'confirmed.title':         'Commande Confirmée !',
      'confirmed.preparing':     'Notre équipe prépare votre commande. Merci de faire confiance à Zyma !',
      'confirmed.myOrders':      'Voir Mes Commandes',
      'confirmed.keepShopping':  'Continuer les Achats',

      // ── BIZUM ─────────────────────────────────────────────────────────────
      'bizum.title':         'Complétez votre paiement par Bizum',
      'bizum.orderPrefix':   'Votre commande',
      'bizum.orderSuffix':   'a été créée et est en attente de confirmation.',
      'bizum.amount':        'Montant :',
      'bizum.sendTo':        'Envoyer à :',
      'bizum.concept':       'Concept :',
      'bizum.currentStatus': 'Statut actuel :',
      'bizum.step1':         "Ouvrez votre application bancaire et accédez à l'option Bizum.",
      'bizum.step2':         "Envoyez-nous le montant exact de la commande au numéro indiqué ci-dessus.",
      'bizum.step3':         "Écrivez le concept exactement tel qu'il apparaît pour localiser votre paiement.",
      'bizum.step4':         'Une fois complété, votre commande continuera à apparaître dans "Mes commandes" pendant que nous la vérifions.',
      'bizum.noPhone':       "Vous n'avez pas encore configuré le téléphone récepteur Bizum. Ajoutez la variable BIZUM_PHONE sur le serveur pour afficher le vrai numéro.",
      'bizum.viewTicket':    'Voir le ticket',
      'bizum.viewOrders':    'Voir mes commandes',
      'bizum.backHome':      "Retour à l'accueil",
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
      'cart.cardTitle':       'Card payment',
      'cart.cardDesc':        'You will be redirected to the secure Stripe page to complete the payment.',
      'cart.bizumTitle':      'Bizum payment',
      'cart.bizumDesc':       'We will show you the instructions on screen to send the exact amount and confirm your order.',
      'cart.bizumPhone':      'Your Bizum phone (optional)',
      'cart.bizumPlaceholder':'+44XXXXXecurity',
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
      'tickets.heroKicker':        'Your purchases',
      'tickets.historyKicker':     'History',
      'tickets.myPurchasesTitle':  'My purchase tickets',
      'tickets.myPurchasesDesc':   'Access the details of each order and download your receipts.',
      'tickets.makeOrder':         'Place an order',
      'tickets.problemText':       'Do you have a problem with your order?',
      'tickets.openIncidence':     'Open an incident',
      'tickets.supportKicker':     'Support and service',
      'tickets.incidenciasTitle':  'Incident management',
      'tickets.incidenciasDesc':   'Report any issue with your order, your account, a payment or any other topic. We will help you as soon as possible.',
      'tickets.needPurchaseProof': 'Do you need your purchase receipt?',
      'tickets.viewPurchaseTickets':'View purchase tickets',

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

      // ── WORKER ────────────────────────────────────────────────────────────
      'worker.operationsCenter':   'Operations Center',
      'worker.welcome':            'Welcome to the operations center',
      'worker.stableOps':          'Stable operations',
      'worker.activeAttention':    'Active attention',
      'worker.criticalPriority':   'Maximum priority',
      'worker.managePendingOrders':'Manage orders',
      'worker.manageOrdersDesc':   'Supervise pending orders, update statuses and keep the kitchen flow under control.',
      'worker.editMenu':           'Edit menu',
      'worker.editMenuDesc':       'Adjust products, availability and menu details to avoid service errors.',
      'worker.viewStats':          'View statistics',
      'worker.viewStatsDesc':      'Check business performance and make quick decisions with today\'s data.',
      'worker.recommendations':    'Smart recommendations',
      'worker.pendingOrders':      'Pending orders',
      'worker.inProgress':         'In progress',
      'worker.deliveredToday':     'Delivered today',
      'worker.lowStockItems':      'Low stock items',

      // ── COOKIES POLICY ────────────────────────────────────────────────────
      'cookies.title':             'Cookie Policy',
      'cookies.subtitle':          'This website uses cookies to provide a faster, more useful and personalized experience for our customers.',
      'cookies.whatAreCookies':    '1. What are cookies',
      'cookies.whatAreText':       'They are small files stored on your device to remember preferences and improve navigation.',
      'cookies.typesCookies':      '2. Types of cookies we use',
      'cookies.typesText':         'Technical cookies (necessary), analytics (usage measurement) and marketing (personalized promotional content).',
      'cookies.purposes':          '3. Purposes',
      'cookies.purposesText':      'They allow maintaining your session, remembering settings, analyzing usage behavior and optimizing site performance.',
      'cookies.management':        '4. Consent management',
      'cookies.managementText':    'You can accept, reject or customize optional cookies from the popup when starting your session.',
      'cookies.disable':           '5. How to disable cookies',
      'cookies.disableText':       'You can also delete or block cookies from your browser settings at any time.',
      'cookies.updates':           '6. Updates',
      'cookies.updatesText':       'We may update this policy to reflect legal or technical changes. We will always publish the current version on this page.',

      'nav.incidents':          'Incidents',
      'nav.ticketsPurchase':    'Purchase tickets',
      'nav.workerPanel':        'Panel',
      'nav.workerOrders':       'Orders',
      'nav.workerEditMenu':     'Edit menu',
      'nav.workerChangePrice':  'Change prices',
      'nav.workerStats':        'Statistics',
      'nav.workerIncidents':    'Incidents',
      'user.openIncidents':     'Open or review incidents',
      'user.myPurchaseTickets': 'View my purchase tickets',
      'user.favoritesKicker':   'Customer favourites',
      'user.favoritesTitle':    'The products our customers buy the most',
      'user.viewAll':           'View all menu',
      'user.reviewsSection':    'Reviews',
      'user.reviewsViewAllLink':'View all',
      'user.new':               'New',
      'user.sold':              'sold',
      'user.starCardDesc':      'Go directly to the current star hotdog in a view dedicated only to that product.',
      'user.purchasesCardKicker': 'Purchases',
      'user.purchasesCardTitle':  'Review your receipts',
      'user.purchasesCardDesc':   'Access your purchase tickets and invoices whenever you need them.',
      'menu.allergenGluten':    'Gluten',
      'menu.allergenDairy':     'Dairy',
      'menu.allergenSoy':       'Soy',
      'admin.panelTitle':       'Administration Panel',
      'admin.panelDesc':        'Summary of orders, inventory and users. Quickly access the main sections.',
      'admin.todayOrders':      "Today's orders",
      'admin.todayRevenue':     "Today's revenue",
      'admin.activeOrders':     'Active orders',
      'admin.preparingOrders':  'Orders in preparation',
      'admin.redIngredients':   'Low-stock ingredients',
      'admin.topProduct':       'Best-selling product',
      'admin.internalNotif':    'Internal notifications',
      'admin.registeredUsers':  'Registered users',
      'admin.realTimeOrders':   'Real-time orders',
      'admin.addUser':          'Add user',
      'admin.idOrder':          'Order ID',
      'admin.idCol':            'ID',
      'admin.nameCol':          'Name',
      'admin.emailCol':         'Email',
      'admin.roleCol':          'Role',
      'admin.codeCol':          'Code',
      'admin.statusCol':        'Status',
      'admin.actionsCol':       'Actions',
      'admin.saveRole':         'Save role',
      'admin.block':            'Block',
      'admin.unblock':          'Unblock',
      'admin.delete':           'Delete',
      'admin.yourUser':         'Your user',
      'admin.createUser':       'Create user',
      'admin.noActiveOrders':   'No active orders available.',
      'admin.ordersLink':       'Orders',
      'admin.inventoryLink':    'Inventory',
      'admin.productsLink':     'Products',
      'admin.todayOrdersDesc':     'Orders registered today',
      'admin.noDateAvail':         'Date not available',
      'admin.todayRevenueDesc':    "Today's sales",
      'admin.activeOrdersDesc':    'Orders in progress',
      'admin.preparingOrdersDesc': 'Currently in process',
      'admin.criticalInventory':   'Critical inventory',
      'admin.inventoryNotFound':   'Inventory not detected',
      'admin.salesSummary':        'Sales summary',
      'admin.noProducts':          'No products',
      'admin.unreadNotif':         'Unread',
      'admin.notifsNotFound':      'Notifications not detected',
      'admin.usersDesc':           'Clients, employees and administrators',
      'admin.updated':             'Updated',
      'confirmation.orderSummary': 'Order Summary',
      'confirmation.date':         'Date:',
      'editMenu.title':              'Change prices and products',
      'editMenu.backToWorker':       'Back to worker panel',
      'editMenu.changeSection':      'Change product prices',
      'editMenu.changeSectionDesc':  'Change the price, press save and that amount will be used on the menu and at checkout.',
      'editMenu.newProduct':         'Add new product',
      'editMenu.productName':        'Product name',
      'editMenu.priceEUR':           'Price in EUR',
      'editMenu.imagePath':          'Image path',
      'editMenu.createProduct':      'Create product',
      'editMenu.existingProducts':   'Existing products',
      'editMenu.currentPrice':       'Current price in EUR',
      'editMenu.saveChanges':        'Save price and changes',
      'editMenu.delete':             'Delete',
      'stats.title':            'Statistics - Worker',
      'stats.backToPanel':      'Back to Control Panel',
      'stats.pendingOrders':    'Pending Orders',
      'stats.totalOrders':      'Total Orders',
      'stats.inventoryTitle':   'Ingredient Inventory (unique)',
      'stats.ingredient':       'Ingredient',
      'stats.quantity':         'Quantity',
      'stats.unit':             'Unit',
      'stats.minStock':         'Minimum Stock',
      'stats.statusCol':        'Status',
      'stats.lowStock':         'Low stock',
      'stats.normal':           'Normal',
      'stats.noIngredients':    'No ingredients registered in the database.',
      'manageOrders.title':       'Manage Orders',
      'manageOrders.backToPanel': 'Back to Control Panel',
      'manageOrders.noOrders':    'No orders.',
      'manageOrders.preparing':   'Preparing',
      'manageOrders.cancel':      'Cancel',
      'manageOrders.delivering':  'Delivering',
      'manageOrders.ready':       'Ready',
      'manageOrders.delivered':   'Delivered',
      'confirmation.title':      'Order confirmed!',
      'confirmation.product':    'Product',
      'confirmation.quantity':   'Quantity',
      'confirmation.unitPrice':  'Unit Price',
      'confirmation.subtotal':   'Subtotal',
      'confirmation.viewTicket': 'View Ticket',
      'confirmation.allTickets': 'All my Tickets',
      'confirmation.backHome':   'Back to Home',
      'confirmed.title':         'Order Confirmed!',
      'confirmed.preparing':     'Our team is preparing your order. Thank you for trusting Zyma!',
      'confirmed.myOrders':      'View My Orders',
      'confirmed.keepShopping':  'Continue Shopping',

      // ── BIZUM ─────────────────────────────────────────────────────────────
      'bizum.title':         'Complete your Bizum payment',
      'bizum.orderPrefix':   'Your order',
      'bizum.orderSuffix':   'has been created and is pending confirmation.',
      'bizum.amount':        'Amount:',
      'bizum.sendTo':        'Send to:',
      'bizum.concept':       'Concept:',
      'bizum.currentStatus': 'Current status:',
      'bizum.step1':         'Open your banking app and go to the Bizum option.',
      'bizum.step2':         'Send us the exact order amount to the phone number shown above.',
      'bizum.step3':         'Write the concept exactly as it appears so we can locate your payment.',
      'bizum.step4':         'Once completed, your order will continue to appear in "My orders" while we review it.',
      'bizum.noPhone':       'You have not yet configured the Bizum receiver phone. Add the BIZUM_PHONE variable on the server to display the real number.',
      'bizum.viewTicket':    'View ticket',
      'bizum.viewOrders':    'View my orders',
      'bizum.backHome':      'Back to home',
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
