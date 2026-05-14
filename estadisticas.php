<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * estadisticas.php
 * Dashboard visual de inventario y pedidos para trabajadores.
 */

session_start();
require_once 'config.php';
require_once 'auth.php';
zymaRequireRole('worker');

// Cargar ingredientes con información de stock
try {
    $stmt = $pdo->query("
        SELECT 
            id,
            nombre,
            cantidad,
            unidad,
            stock_minimo,
            CASE 
                WHEN cantidad <= stock_minimo THEN 'critico'
                WHEN cantidad <= stock_minimo * 1.5 THEN 'bajo'
                ELSE 'normal'
            END as nivel_stock
        FROM ingredientes 
        ORDER BY cantidad ASC, nombre ASC
    ");
    $ingredientes = $stmt->fetchAll();
} catch (Exception $e) {
    $ingredientes = [];
}

// Estadísticas adicionales
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
    $pedidosPendientes = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('preparando', 'listo')");
    $pedidosEnProceso = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    $pedidosHoy = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE DATE(fecha_hora) = CURDATE()");
    $ventasHoy = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes WHERE cantidad <= stock_minimo");
    $ingredientesBajoStock = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM ingredientes");
    $totalIngredientes = $stmt->fetchColumn();
    
    $porcentajeStock = $totalIngredientes > 0 ? round((($totalIngredientes - $ingredientesBajoStock) / $totalIngredientes) * 100, 1) : 0;
    
} catch (Exception $e) {
    $pedidosPendientes = $pedidosEnProceso = $pedidosHoy = $ventasHoy = $ingredientesBajoStock = $totalIngredientes = 0;
    $porcentajeStock = 0;
}

$display_name = trim($_SESSION['nombre'] ?? '');
if ($display_name === '') {
    $display_name = strstr($_SESSION['email'] ?? '', '@', true) ?: ($_SESSION['email'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zyma - Estadísticas</title>
<link rel="icon" type="image/png" href="assets/favicon.png">
<link rel="stylesheet" href="styles.css?v=20260513-1">
<style>
/* Estilos específicos para estadísticas visuales */
.stats-visual-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.2rem;
  margin-bottom: 2rem;
}

.stat-visual-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 16px rgba(60, 16, 16, 0.08);
  border: 1px solid rgba(69, 5, 12, 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-visual-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(60, 16, 16, 0.12);
}

.stat-visual-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.stat-visual-card.critical::before { background: linear-gradient(90deg, #dc2626, #ef4444); }
.stat-visual-card.warning::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.stat-visual-card.success::before { background: linear-gradient(90deg, #10b981, #34d399); }
.stat-visual-card.info::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }

.stat-visual-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  font-size: 1.5rem;
}

.stat-visual-card.critical .stat-visual-icon { background: #fef2f2; }
.stat-visual-card.warning .stat-visual-icon { background: #fffbeb; }
.stat-visual-card.success .stat-visual-icon { background: #ecfdf5; }
.stat-visual-card.info .stat-visual-icon { background: #eff6ff; }

.stat-visual-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--deep-red);
  line-height: 1;
  margin-bottom: 0.3rem;
}

.stat-visual-label {
  font-size: 0.9rem;
  color: #6b7280;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.stat-visual-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
}

.badge-critical { background: #fef2f2; color: #dc2626; }
.badge-warning { background: #fffbeb; color: #f59e0b; }
.badge-success { background: #ecfdf5; color: #10b981; }

/* Sección de inventario visual */
.inventory-visual-section {
  background: var(--white);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 16px rgba(60, 16, 16, 0.08);
  border: 1px solid rgba(69, 5, 12, 0.06);
  margin-bottom: 2rem;
}

.inventory-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.inventory-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--deep-red);
  margin: 0;
}

.stock-overview {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.stock-percentage {
  font-size: 2rem;
  font-weight: 800;
  color: var(--deep-red);
}

/* Barras de progreso para ingredientes */
.ingredient-visual-grid {
  display: grid;
  gap: 1rem;
}

.ingredient-visual-card {
  background: #fdfbf9;
  border: 1px solid rgba(69, 5, 12, 0.06);
  border-radius: 12px;
  padding: 1.2rem;
  transition: all 0.3s ease;
}

.ingredient-visual-card:hover {
  background: var(--white);
  box-shadow: 0 4px 12px rgba(60, 16, 16, 0.08);
}

.ingredient-visual-card.critical {
  border-color: #fecaca;
  background: #fef2f2;
}

.ingredient-visual-card.warning {
  border-color: #fde68a;
  background: #fffbeb;
}

.ingredient-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}

.ingredient-name {
  font-weight: 700;
  color: var(--deep-red);
  font-size: 1rem;
}

.ingredient-status {
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 700;
}

.status-critical { background: #fecaca; color: #991b1b; }
.status-warning { background: #fde68a; color: #92400e; }
.status-normal { background: #bbf7d0; color: #065f46; }

.ingredient-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
  margin-bottom: 0.8rem;
  font-size: 0.85rem;
  color: #6b7280;
}

.ingredient-details strong {
  color: var(--deep-red);
}

.progress-bar-container {
  background: #e5e7eb;
  border-radius: 8px;
  height: 8px;
  overflow: hidden;
  position: relative;
}

.progress-bar-fill {
  height: 100%;
  border-radius: 8px;
  transition: width 0.5s ease;
}

.progress-bar-fill.critical { background: linear-gradient(90deg, #dc2626, #ef4444); }
.progress-bar-fill.warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.progress-bar-fill.normal { background: linear-gradient(90deg, #10b981, #34d399); }

/* Botón volver mejorado */
.btn-back-dashboard {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.5rem;
  background: var(--white);
  color: var(--deep-red);
  border: 2px solid var(--light-gray);
  border-radius: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
  margin-bottom: 2rem;
}

.btn-back-dashboard:hover {
  background: var(--deep-red);
  color: var(--white);
  border-color: var(--deep-red);
  transform: translateX(-4px);
}

/* Responsive */
@media (max-width: 768px) {
  .stats-visual-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .stat-visual-value {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .stats-visual-grid {
    grid-template-columns: 1fr;
  }
}
</style>
</head>
<body>

<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
        <a href="perfil.php">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
        <a href="logout.php">Cerrar Sesión</a>
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
        <a href="incidencias.php">Incidencias</a>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <div class="main-content">
    <h2 style="font-size: 2rem; font-weight: 800; color: var(--deep-red); margin-bottom: 0.5rem;">
      📊 Dashboard de Estadísticas
    </h2>
    <p style="color: #6b7280; margin-bottom: 2rem;">Visualiza el estado de inventario y pedidos en tiempo real</p>
    
    <!-- Botón volver -->
    <a href="trabajador.php" class="btn-back-dashboard">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
      Volver al Panel de Control
    </a>
    
    <!-- Tarjetas de estadísticas visuales -->
    <div class="stats-visual-grid">
      <!-- Pedidos Pendientes -->
      <div class="stat-visual-card critical">
        <div class="stat-visual-icon">🚨</div>
        <div class="stat-visual-value"><?= $pedidosPendientes ?></div>
        <div class="stat-visual-label">Pedidos Pendientes</div>
        <?php if ($pedidosPendientes > 0): ?>
          <span class="stat-visual-badge badge-critical">Requiere atención</span>
        <?php endif; ?>
      </div>
      
      <!-- Pedidos en Proceso -->
      <div class="stat-visual-card warning">
        <div class="stat-visual-icon">⏳</div>
        <div class="stat-visual-value"><?= $pedidosEnProceso ?></div>
        <div class="stat-visual-label">En Preparación</div>
        <?php if ($pedidosEnProceso > 0): ?>
          <span class="stat-visual-badge badge-warning">En curso</span>
        <?php endif; ?>
      </div>
      
      <!-- Pedidos Hoy -->
      <div class="stat-visual-card info">
        <div class="stat-visual-icon">📅</div>
        <div class="stat-visual-value"><?= $pedidosHoy ?></div>
        <div class="stat-visual-label">Pedidos Hoy</div>
        <span class="stat-visual-badge badge-success">Día actual</span>
      </div>
      
      <!-- Ventas Hoy -->
      <div class="stat-visual-card success">
        <div class="stat-visual-icon">💰</div>
        <div class="stat-visual-value" style="font-size: 1.8rem;">€<?= number_format($ventasHoy, 2, ',', '.') ?></div>
        <div class="stat-visual-label">Ventas Hoy</div>
        <span class="stat-visual-badge badge-success">Ingresos</span>
      </div>
    </div>
    
    <!-- Sección de Inventario Visual -->
    <div class="inventory-visual-section">
      <div class="inventory-header">
        <div>
          <h3 class="inventory-title">📦 Inventario de Ingredientes</h3>
          <p style="color: #6b7280; margin-top: 0.3rem;">Estado actualizado de stock en tiempo real</p>
        </div>
        <div class="stock-overview">
          <div style="text-align: center;">
            <div class="stock-percentage"><?= $porcentajeStock ?>%</div>
            <div style="font-size: 0.85rem; color: #6b7280;">Stock saludable</div>
          </div>
        </div>
      </div>
      
      <?php if (!empty($ingredientes)): ?>
        <div class="ingredient-visual-grid">
          <?php foreach ($ingredientes as $ing): ?>
            <?php
            $nivel = $ing['nivel_stock'];
            $porcentajeStockItem = $ing['stock_minimo'] > 0 ? min(100, round(($ing['cantidad'] / ($ing['stock_minimo'] * 2)) * 100, 1)) : 100;
            $estadoTexto = $nivel === 'critico' ? 'Crítico' : ($nivel === 'bajo' ? 'Bajo' : 'Normal');
            ?>
            <div class="ingredient-visual-card <?= $nivel === 'critico' ? 'critical' : ($nivel === 'bajo' ? 'warning' : '') ?>">
              <div class="ingredient-top">
                <span class="ingredient-name"><?= htmlspecialchars($ing['nombre']) ?></span>
                <span class="ingredient-status status-<?= $nivel ?>"><?= $estadoTexto ?></span>
              </div>
              <div class="ingredient-details">
                <div><strong><?= number_format($ing['cantidad'], 0, ',', '.') ?> <?= htmlspecialchars($ing['unidad']) ?></strong></div>
                <div>Mín: <?= number_format($ing['stock_minimo'], 0, ',', '.') ?> <?= htmlspecialchars($ing['unidad']) ?></div>
              </div>
              <div class="progress-bar-container">
                <div class="progress-bar-fill <?= $nivel ?>" style="width: <?= $porcentajeStockItem ?>%;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state" style="text-align: center; padding: 3rem;">
          <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
          <p style="color: #6b7280; font-size: 1.1rem;">No hay ingredientes registrados en la base de datos.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Resumen de stock bajo -->
    <?php if ($ingredientesBajoStock > 0): ?>
      <div class="alert alert-error" style="border-radius: 12px; padding: 1.2rem; margin-top: 1rem;">
        <strong>⚠️ Atención:</strong> Hay <?= $ingredientesBajoStock ?> ingrediente<?= $ingredientesBajoStock > 1 ? 's' : '' ?> con stock bajo o crítico que requiere reposición.
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
const quickDropdown = document.getElementById('quickDropdown');

if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
}

if (quickDropdown) {
  const quickMenuBtn = document.getElementById('quickMenuBtn');
  if (quickMenuBtn) {
    quickMenuBtn.addEventListener('click', () => quickDropdown.classList.toggle('show'));
  }
}

// Auto-refresh cada 30 segundos
const AUTO_REFRESH_MS = 30000;
setInterval(() => {
  if (document.hidden) return;
  if (dropdownMenu && dropdownMenu.classList.contains('show')) return;
  if (quickDropdown && quickDropdown.classList.contains('show')) return;
  window.location.reload();
}, AUTO_REFRESH_MS);
</script>

<script src="assets/mobile-header.js?v=20260513-1"></script>

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

<?php require_once 'language_selector.php'; ?>
  <script src="assets/animations.js?v=20260513-1" defer></script>
<?php include 'cookie_popup.php'; ?>
</body>
</html>
