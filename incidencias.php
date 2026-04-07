<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

$mensaje = '';
$error = '';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS incidencias_clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            asunto VARCHAR(120) NOT NULL,
            categoria VARCHAR(40) NOT NULL DEFAULT 'general',
            prioridad VARCHAR(20) NOT NULL DEFAULT 'media',
            descripcion TEXT NOT NULL,
            estado VARCHAR(20) NOT NULL DEFAULT 'abierta',
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_usuario (id_usuario),
            INDEX idx_estado (estado),
            CONSTRAINT fk_incidencias_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {
    $error = 'No se pudo preparar el sistema de incidencias.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_issue') {
        $asunto = trim($_POST['asunto'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'general');
        $prioridad = trim($_POST['prioridad'] ?? 'media');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $categoriasPermitidas = ['pedido', 'pago', 'cuenta', 'producto', 'tecnico', 'general'];
        $prioridadesPermitidas = ['baja', 'media', 'alta'];

        if ($asunto === '' || $descripcion === '') {
            $error = 'El asunto y la descripción son obligatorios.';
        } elseif (strlen($asunto) > 120) {
            $error = 'El asunto no puede superar 120 caracteres.';
        } elseif (!in_array($categoria, $categoriasPermitidas, true)) {
            $error = 'La categoría seleccionada no es válida.';
        } elseif (!in_array($prioridad, $prioridadesPermitidas, true)) {
            $error = 'La prioridad seleccionada no es válida.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO incidencias_clientes (id_usuario, asunto, categoria, prioridad, descripcion)
                    VALUES (:id_usuario, :asunto, :categoria, :prioridad, :descripcion)
                ");
                $stmt->execute([
                    ':id_usuario' => $_SESSION['user_id'],
                    ':asunto' => $asunto,
                    ':categoria' => $categoria,
                    ':prioridad' => $prioridad,
                    ':descripcion' => $descripcion,
                ]);
                $mensaje = 'Incidencia creada correctamente.';
            } catch (Exception $e) {
                $error = 'No se pudo registrar la incidencia.';
            }
        }
    }
}

$incidencias = [];
$resumenIncidencias = [
    'abiertas' => 0,
    'proceso' => 0,
    'cerradas' => 0,
];

try {
    $stmt = $pdo->prepare("
        SELECT id, asunto, categoria, prioridad, descripcion, estado, fecha_creacion, fecha_actualizacion
        FROM incidencias_clientes
        WHERE id_usuario = :id_usuario
        ORDER BY fecha_actualizacion DESC, fecha_creacion DESC
    ");
    $stmt->execute([':id_usuario' => $_SESSION['user_id']]);
    $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($incidencias as $incidencia) {
        if (($incidencia['estado'] ?? '') === 'cerrada') {
            $resumenIncidencias['cerradas']++;
        } elseif (($incidencia['estado'] ?? '') === 'en_proceso') {
            $resumenIncidencias['proceso']++;
        } else {
            $resumenIncidencias['abiertas']++;
        }
    }
} catch (Exception $e) {
    if ($error === '') {
        $error = 'No se pudieron cargar tus incidencias.';
    }
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
    <title>Gestión de Incidencias - Zyma</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="styles.css?v=20260320-3">
</head>
<body>
<header class="landing-header">
  <div class="landing-bar">
    <div class="profile-section">
      <button class="profile-btn" id="profileBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="white">
          <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4 1.79 4 4 4zm0 2c1.52 0 5.1 1.34 5.1 5v1H6.9v-1c0-3.66 3.58-5 5.1-5z"/>
        </svg>
      </button>
      <span class="user-name"><?= htmlspecialchars($display_name) ?></span>
      <div class="dropdown" id="dropdownMenu">
        <a href="perfil.php">Mi perfil</a>
        <a href="politica_cookies.php" class="open-cookie-preferences">Personalizar cookies</a>
        <a href="logout.php">Cerrar sesión</a>
      </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menú rápido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="usuario.php">Inicio</a>
        <a href="carta.php">Ver carta</a>
        <a href="valoraciones.php">Valoraciones</a>
        <a href="incidencias.php">Incidencias</a>
        <a href="tickets.php">Tickets de compra</a>
      </div>
    </div>

    <div class="cart-section">
      <a href="carrito.php" class="cart-btn">
        <img src="assets/cart-icon.png" alt="Carrito">
        <span class="cart-count"><?= count($_SESSION['cart'] ?? []) ?></span>
      </a>
    </div>
  </div>
</header>

<main class="container support-page">
    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="support-hero">
        <div class="support-hero-copy">
            <span class="support-kicker">Soporte y atención</span>
            <h1>Gestión de incidencias</h1>
            <p>Reporta cualquier problema con tu pedido, tu cuenta, un pago o cualquier otro tema. Te ayudaremos lo antes posible.</p>
        </div>
        <div class="support-summary">
            <article class="support-summary-card">
                <strong><?= $resumenIncidencias['abiertas'] ?></strong>
                <span>abiertas</span>
            </article>
            <article class="support-summary-card">
                <strong><?= $resumenIncidencias['proceso'] ?></strong>
                <span>en proceso</span>
            </article>
            <article class="support-summary-card">
                <strong><?= $resumenIncidencias['cerradas'] ?></strong>
                <span>cerradas</span>
            </article>
        </div>
    </section>

    <div class="support-grid">
        <section class="profile-card support-form-card">
            <div class="profile-card-header">
                <span class="profile-section-kicker">Nueva incidencia</span>
                <h2>Cuéntanos qué ha pasado</h2>
                <p>Describe el problema con claridad para que podamos ayudarte más rápido.</p>
            </div>

            <form method="POST" action="incidencias.php" class="support-form">
                <input type="hidden" name="action" value="create_issue">

                <label for="asunto">
                    Asunto <span class="required">*</span>
                    <input type="text" id="asunto" name="asunto" maxlength="120" required placeholder="Ejemplo: Problema con un pedido">
                </label>

                <div class="support-form-split">
                    <label for="categoria">
                        Categoría
                        <select id="categoria" name="categoria">
                            <option value="pedido">Pedido</option>
                            <option value="pago">Pago</option>
                            <option value="cuenta">Cuenta</option>
                            <option value="producto">Producto</option>
                            <option value="tecnico">Técnico</option>
                            <option value="general">General</option>
                        </select>
                    </label>

                    <label for="prioridad">
                        Prioridad
                        <select id="prioridad" name="prioridad">
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="baja">Baja</option>
                        </select>
                    </label>
                </div>

                <label for="descripcion">
                    Descripción <span class="required">*</span>
                    <textarea id="descripcion" name="descripcion" rows="6" required placeholder="Explica la incidencia con el mayor detalle posible"></textarea>
                </label>

                <button type="submit">Enviar incidencia</button>
            </form>
        </section>

        <section class="profile-card support-list-card">
            <div class="profile-card-header">
                <span class="profile-section-kicker">Seguimiento</span>
                <h2>Mis incidencias</h2>
                <p>Consulta el estado de cada incidencia registrada desde tu cuenta.</p>
            </div>

            <?php if (empty($incidencias)): ?>
                <p class="empty-state">Todavía no has creado incidencias.</p>
            <?php else: ?>
                <div class="support-issues-list">
                    <?php foreach ($incidencias as $incidencia): ?>
                        <article class="support-issue-card support-state-<?= htmlspecialchars($incidencia['estado']) ?>">
                            <div class="support-issue-head">
                                <div>
                                    <h3><?= htmlspecialchars($incidencia['asunto']) ?></h3>
                                    <p><?= htmlspecialchars(ucfirst($incidencia['categoria'])) ?> · Prioridad <?= htmlspecialchars($incidencia['prioridad']) ?></p>
                                </div>
                                <span class="badge-status <?= ($incidencia['estado'] === 'cerrada') ? 'badge-estado-entregado' : (($incidencia['estado'] === 'en_proceso') ? 'badge-estado-preparando' : 'badge-estado-pendiente') ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', ucfirst($incidencia['estado']))) ?>
                                </span>
                            </div>
                            <p class="support-issue-description"><?= nl2br(htmlspecialchars($incidencia['descripcion'])) ?></p>
                            <div class="support-issue-meta">
                                <span>Creada: <?= date('d/m/Y H:i', strtotime($incidencia['fecha_creacion'])) ?></span>
                                <span>Actualizada: <?= date('d/m/Y H:i', strtotime($incidencia['fecha_actualizacion'])) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="support-bottom-nav">
        <p>¿Necesitas tu comprobante de compra? <a href="tickets.php">Ver tickets de compra</a></p>
    </div>
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

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
if (profileBtn && dropdownMenu) {
  profileBtn.addEventListener('click', () => {
    dropdownMenu.classList.toggle('show');
  });

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
