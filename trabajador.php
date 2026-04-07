<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
require_once 'auth.php';
zymaRequireRole('worker');
require_once 'config.php';

$show_cookie_popup = !empty($_SESSION['show_cookie_popup']);
$cookie_preferences = $_SESSION['cookie_preferences'] ?? [];
unset($_SESSION['show_cookie_popup']);

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}

$workerCode = trim((string) ($_SESSION['worker_code'] ?? ''));
$workerInitials = strtoupper(mb_substr($display_name, 0, 1, 'UTF-8'));
$greeting = 'Bienvenido al centro de operaciones';

$dashboardStats = [
    'pending_orders' => 0,
    'in_progress_orders' => 0,
    'delivered_today' => 0,
    'low_stock_items' => 0,
];

$serviceAlerts = [];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
    $dashboardStats['pending_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('preparando', 'entregando')");
    $dashboardStats['in_progress_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()");
    $dashboardStats['delivered_today'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes WHERE cantidad <= 10");
    $dashboardStats['low_stock_items'] = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $serviceAlerts[] = 'No se pudieron cargar todas las métricas del panel.';
}

$serviceLevel = 'estable';
$serviceLevelLabel = 'Operativa estable';
$serviceLevelText = 'El turno está controlado y el panel queda listo para mantener ritmo y orden.';
$serviceProgress = 38;

if ($dashboardStats['pending_orders'] >= 8 || $dashboardStats['low_stock_items'] >= 6) {
    $serviceLevel = 'critico';
    $serviceLevelLabel = 'Máxima prioridad';
    $serviceLevelText = 'Conviene actuar sobre pedidos pendientes y revisar stock crítico antes de que afecte al servicio.';
    $serviceProgress = 92;
} elseif ($dashboardStats['pending_orders'] >= 4 || $dashboardStats['in_progress_orders'] >= 6 || $dashboardStats['low_stock_items'] >= 3) {
    $serviceLevel = 'atencion';
    $serviceLevelLabel = 'Atención activa';
    $serviceLevelText = 'Hay movimiento suficiente para trabajar con foco y revisar los puntos sensibles del turno.';
    $serviceProgress = 67;
}

$smartRecommendations = [];
if ($dashboardStats['pending_orders'] > 0) {
    $smartRecommendations[] = 'Prioriza pedidos pendientes para evitar cuellos de botella en cocina y entrega.';
}
if ($dashboardStats['low_stock_items'] > 0) {
    $smartRecommendations[] = 'Revisa ingredientes críticos y ajusta la carta si hace falta para no vender productos sin stock.';
}
if ($dashboardStats['in_progress_orders'] >= 5) {
    $smartRecommendations[] = 'Haz seguimiento de pedidos en curso para que ningún servicio se quede sin actualizar.';
}
if (!$smartRecommendations) {
    $smartRecommendations[] = 'Mantén el panel limpio y aprovecha el momento para revisar carta, incidencias y consistencia del servicio.';
}

$priorityActions = [
    [
        'title' => 'Gestionar pedidos',
        'description' => 'Supervisa pedidos pendientes, actualiza estados y mantén el flujo de cocina bajo control.',
        'href' => 'gestionar_pedidos.php',
        'eyebrow' => 'Operativa en vivo',
        'accent' => 'gold',
    ],
    [
        'title' => 'Editar carta',
        'description' => 'Ajusta productos, disponibilidad y detalles de la carta para evitar errores de servicio.',
        'href' => 'editar_carta.php',
        'eyebrow' => 'Configuración',
        'accent' => 'red',
    ],
    [
        'title' => 'Ver estadísticas',
        'description' => 'Consulta el rendimiento del negocio y toma decisiones rápidas con datos del día.',
        'href' => 'estadisticas.php',
        'eyebrow' => 'Rendimiento',
        'accent' => 'dark',
    ],
];

$serviceChecklist = [
    'Revisar pedidos nuevos y confirmar prioridades del turno.',
    'Comprobar ingredientes críticos antes del pico de demanda.',
    'Vigilar incidencias y tickets con clientes para responder con rapidez.',
    'Cerrar el turno con estados actualizados y panel limpio.',
];

$secondaryLinks = [
    ['label' => 'Gestionar pedidos', 'href' => 'gestionar_pedidos.php'],
    ['label' => 'Editar carta', 'href' => 'editar_carta.php'],
    ['label' => 'Estadísticas', 'href' => 'estadisticas.php'],
    ['label' => 'Mi perfil', 'href' => 'perfil.php'],
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Panel de trabajador</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260407-1">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn" aria-label="Perfil">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
        <a href="perfil.php">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
        <a href="logout.php">Cerrar sesión</a>
      </div>
    </div>

    <a href="trabajador.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="trabajador.php">Panel</a>
        <a href="gestionar_pedidos.php">Pedidos</a>
        <a href="editar_carta.php">Carta</a>
        <a href="estadisticas.php">Estadísticas</a>
        <a href="incidencias.php">Incidencias</a>
      </div>
    </div>

    <div class="landing-actions">
      <a href="gestionar_pedidos.php" class="landing-link">Pedidos</a>
      <a href="estadisticas.php" class="landing-cta">Estadísticas</a>
    </div>
  </div>
</header>

<main class="container worker-dashboard-page">
  <section class="worker-hero">
    <div class="worker-hero-copy">
      <span class="worker-eyebrow">Área profesional del trabajador</span>
      <h1><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($display_name) ?></h1>
      <p>Tu panel centraliza la operativa del turno, la supervisión de pedidos y los accesos clave para trabajar con rapidez, criterio y buena presencia.</p>

      <div class="worker-hero-actions">
        <a href="gestionar_pedidos.php" class="landing-cta">Abrir operativa</a>
        <a href="editar_carta.php" class="worker-secondary-action">Actualizar carta</a>
      </div>
    </div>

    <aside class="worker-hero-card">
      <div class="worker-identity">
        <div class="worker-avatar"><?= htmlspecialchars($workerInitials) ?></div>
        <div>
          <span class="worker-card-label">Identificación</span>
          <strong><?= htmlspecialchars($display_name) ?></strong>
          <p>Código interno: <?= htmlspecialchars($workerCode !== '' ? $workerCode : 'Activo') ?></p>
        </div>
      </div>

      <div class="worker-shift-state">
        <span class="worker-status-dot"></span>
        <div>
          <strong>Panel listo para operar</strong>
          <p>Acceso rápido a pedidos, carta, estadísticas e incidencias.</p>
        </div>
      </div>
    </aside>
  </section>

  <?php if ($serviceAlerts): ?>
    <div class="alert alert-warning worker-inline-alert">
      <?= htmlspecialchars(implode(' ', $serviceAlerts)) ?>
    </div>
  <?php endif; ?>

  <section class="worker-stats-grid" aria-label="Resumen operativo">
    <article class="worker-stat-card">
      <span class="worker-stat-label">Pedidos pendientes</span>
      <strong><?= $dashboardStats['pending_orders'] ?></strong>
      <p>Pedidos esperando atención inmediata.</p>
    </article>
    <article class="worker-stat-card">
      <span class="worker-stat-label">Pedidos en curso</span>
      <strong><?= $dashboardStats['in_progress_orders'] ?></strong>
      <p>Servicios en preparación o entrega.</p>
    </article>
    <article class="worker-stat-card">
      <span class="worker-stat-label">Entregados hoy</span>
      <strong><?= $dashboardStats['delivered_today'] ?></strong>
      <p>Pedidos cerrados durante la jornada.</p>
    </article>
    <article class="worker-stat-card">
      <span class="worker-stat-label">Ingredientes críticos</span>
      <strong><?= $dashboardStats['low_stock_items'] ?></strong>
      <p>Ingredientes con stock bajo para revisar.</p>
    </article>
  </section>

  <section class="worker-layout">
    <div class="worker-main-column">
      <section class="worker-panel-card">
        <div class="worker-section-head">
          <div>
            <span class="worker-section-kicker">Panel de control</span>
            <h2>Acciones prioritarias</h2>
          </div>
          <p>Todo lo importante del turno, organizado para actuar rápido y con claridad.</p>
        </div>

        <div class="worker-action-grid">
          <?php foreach ($priorityActions as $action): ?>
            <a href="<?= htmlspecialchars($action['href']) ?>" class="worker-action-card worker-action-card-<?= htmlspecialchars($action['accent']) ?>">
              <span class="worker-action-kicker"><?= htmlspecialchars($action['eyebrow']) ?></span>
              <strong><?= htmlspecialchars($action['title']) ?></strong>
              <p><?= htmlspecialchars($action['description']) ?></p>
              <span class="worker-action-link">Entrar</span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="worker-panel-card worker-excellence-card">
        <div class="worker-section-head">
          <div>
            <span class="worker-section-kicker">Extra profesional</span>
            <h2>Protocolo de excelencia del turno</h2>
          </div>
          <p>Una guía visual para que la parte del trabajador se sienta más seria, útil y completa.</p>
        </div>

        <div class="worker-checklist">
          <?php foreach ($serviceChecklist as $item): ?>
            <div class="worker-checklist-item">
              <span class="worker-check-icon" aria-hidden="true"></span>
              <p><?= htmlspecialchars($item) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="worker-panel-card worker-radar-card">
        <div class="worker-section-head">
          <div>
            <span class="worker-section-kicker">Innovación operativa</span>
            <h2>Radar inteligente del turno</h2>
          </div>
          <p>Una lectura rápida del momento actual para decidir mejor sin perder tiempo.</p>
        </div>

        <div class="worker-radar-top">
          <div class="worker-radar-badge worker-radar-badge-<?= htmlspecialchars($serviceLevel) ?>">
            <?= htmlspecialchars($serviceLevelLabel) ?>
          </div>
          <p><?= htmlspecialchars($serviceLevelText) ?></p>
        </div>

        <div class="worker-progress-block" aria-label="Pulso del turno">
          <div class="worker-progress-meta">
            <span>Pulso operativo</span>
            <strong><?= $serviceProgress ?>%</strong>
          </div>
          <div class="worker-progress-bar">
            <span style="width: <?= $serviceProgress ?>%"></span>
          </div>
        </div>

        <div class="worker-recommendations">
          <?php foreach ($smartRecommendations as $recommendation): ?>
            <article class="worker-recommendation-item">
              <span class="worker-recommendation-mark" aria-hidden="true"></span>
              <p><?= htmlspecialchars($recommendation) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </div>

    <aside class="worker-side-column">
      <section class="worker-panel-card worker-side-card">
        <div class="worker-section-head worker-section-head-compact">
          <div>
            <span class="worker-section-kicker">Atajos</span>
            <h2>Herramientas del día</h2>
          </div>
        </div>
        <div class="worker-utility-links">
          <?php foreach ($secondaryLinks as $link): ?>
            <a href="<?= htmlspecialchars($link['href']) ?>" class="worker-utility-link"><?= htmlspecialchars($link['label']) ?></a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="worker-panel-card worker-side-card worker-highlight-card">
        <span class="worker-section-kicker">Objetivo del panel</span>
        <h2>Una experiencia más sólida</h2>
        <p>Este espacio ahora prioriza visibilidad operativa, accesos directos y una presentación más premium para el equipo.</p>
      </section>
    </aside>
  </section>
</main>

<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
  <p class="footer-legal-links">
    <a href="politica_cookies.php">Política de Cookies</a>
    <span>|</span>
    <a href="politica_privacidad.php">Política de Privacidad</a>
    <span>|</span>
    <a href="aviso_legal.php">Aviso Legal</a>
  </p>
</footer>

<?php include 'cookie_popup.php'; ?>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
}
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>
