<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

session_start();

// Solo trabajadores
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("Acceso denegado");
}

require_once 'config.php';

// Crear tablas si no existen
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS respuestas_valoraciones (
        id INT PRIMARY KEY AUTO_INCREMENT,
        id_valoracion INT NOT NULL,
        FOREIGN KEY (id_valoracion) REFERENCES valoraciones(id) ON DELETE CASCADE,
        respuesta TEXT NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_valoracion (id_valoracion),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS notificaciones_respuestas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        id_usuario INT NOT NULL,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
        id_valoracion INT NOT NULL,
        FOREIGN KEY (id_valoracion) REFERENCES valoraciones(id) ON DELETE CASCADE,
        leida BOOLEAN DEFAULT FALSE,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario (id_usuario),
        INDEX idx_leida (leida),
        INDEX idx_fecha (fecha_creacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {
    error_log("Error creando tablas: " . $e->getMessage());
}

// Procesar acciones
$mensaje = '';
$tipo_alerta = '';

// Borrar valoración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar'])) {
    $id = (int)$_POST['id_valoracion'];
    try {
        $stmt = $pdo->prepare("DELETE FROM valoraciones WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = "✓ Valoración eliminada correctamente";
        $tipo_alerta = "success";
    } catch (Exception $e) {
        $mensaje = "✗ Error: " . $e->getMessage();
        $tipo_alerta = "error";
    }
}

// Responder a valoración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
    $id_valoracion = (int)$_POST['id_valoracion'];
    $respuesta = trim($_POST['respuesta']);
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    
    if (empty($respuesta)) {
        $error = "La respuesta no puede estar vacía";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error]);
            exit;
        }
        $mensaje = "✗ " . $error;
        $tipo_alerta = "error";
    } else if (strlen($respuesta) > 500) {
        $error = "La respuesta no puede superar 500 caracteres";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error]);
            exit;
        }
        $mensaje = "✗ " . $error;
        $tipo_alerta = "error";
    } else {
        try {
            // Verificar si ya existe respuesta
            $stmt = $pdo->prepare("SELECT id FROM respuestas_valoraciones WHERE id_valoracion = ?");
            $stmt->execute([$id_valoracion]);
            $existe = $stmt->fetch();
            
            if ($existe) {
                // Actualizar respuesta existente
                $stmt = $pdo->prepare("UPDATE respuestas_valoraciones SET respuesta = ? WHERE id_valoracion = ?");
                $stmt->execute([$respuesta, $id_valoracion]);
            } else {
                // Insertar nueva respuesta
                $stmt = $pdo->prepare("INSERT INTO respuestas_valoraciones (id_valoracion, respuesta) VALUES (?, ?)");
                $stmt->execute([$id_valoracion, $respuesta]);
                
                // Obtener datos para notificación
                $stmtVal = $pdo->prepare("SELECT id_usuario FROM valoraciones WHERE id = ?");
                $stmtVal->execute([$id_valoracion]);
                $valoracion = $stmtVal->fetch();
                
                if ($valoracion) {
                    // Crear notificación
                    $stmtNotif = $pdo->prepare("
                        INSERT INTO notificaciones_respuestas (id_usuario, id_valoracion, leida)
                        VALUES (?, ?, FALSE)
                    ");
                    $stmtNotif->execute([$valoracion['id_usuario'], $id_valoracion]);
                }
            }
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Respuesta guardada correctamente']);
                exit;
            }
            
            $mensaje = "✓ Respuesta guardada correctamente";
            $tipo_alerta = "success";
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $mensaje = "✗ Error: " . $e->getMessage();
            $tipo_alerta = "error";
        }
    }
}

// Obtener todas las valoraciones
$valoraciones = [];
try {
    $stmt = $pdo->prepare("
        SELECT v.id, v.puntuacion, v.comentario, v.fecha_creacion, u.nombre, u.email, p.nombre as producto,
               rv.respuesta, rv.id as id_respuesta
        FROM valoraciones v
        JOIN usuarios u ON v.id_usuario = u.id
        JOIN productos p ON v.id_producto = p.id
        LEFT JOIN respuestas_valoraciones rv ON v.id = rv.id_valoracion
        ORDER BY v.fecha_creacion DESC
    ");
    $stmt->execute();
    $valoraciones = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error obteniendo valoraciones: " . $e->getMessage());
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
<title>Gestionar Valoraciones - Zyma</title>
<link rel="stylesheet" href="styles.css?v=20260217-1">
<style>
    .valoracion-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--gold);
    }
    
    .valoracion-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .valoracion-usuario {
        font-weight: bold;
        color: var(--dark-red);
    }
    
    .valoracion-stars {
        display: flex;
        gap: 0.3rem;
    }
    
    .valoracion-stars img {
        width: 16px;
        height: 16px;
    }
    
    .valoracion-producto {
        background: var(--gold);
        color: var(--ink);
        padding: 0.3rem 0.8rem;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .valoracion-comentario {
        background: white;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
        border-left: 3px solid var(--gold);
    }
    
    .valoracion-acciones {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .btn-responder {
        background: var(--dark-red);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-responder:hover {
        background: var(--deep-red);
        transform: translateY(-2px);
    }
    
    .btn-borrar {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-borrar:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
    
    .respuesta-section {
        background: #e8f4f8;
        padding: 1rem;
        border-radius: 6px;
        margin-top: 1rem;
        border-left: 4px solid #2196F3;
    }
    
    .respuesta-existente {
        background: white;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
        font-style: italic;
        color: #555;
    }
    
    .form-respuesta {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .form-respuesta textarea {
        flex: 1;
        padding: 0.6rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        resize: vertical;
        min-height: 60px;
    }
    
    .form-respuesta button {
        background: #28a745;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .form-respuesta button:hover {
        background: #218838;
    }
    
    .toggle-respuesta {
        background: transparent;
        color: var(--dark-red);
        border: none;
        cursor: pointer;
        text-decoration: underline;
        padding: 0;
        font-weight: 600;
    }
    
    .respuesta-form-hidden {
        display: none;
    }
</style>
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
          <a href="logout.php">Cerrar sesion</a>
        </div>
    </div>

    <a href="usuario.php" class="landing-logo">
      <span class="landing-logo-text">Zyma</span>
    </a>

    <div class="quick-menu-section">
      <button class="quick-menu-btn" id="quickMenuBtn" aria-label="Menu rapido"></button>
      <div class="dropdown quick-dropdown" id="quickDropdown">
        <a href="trabajador.php">Panel</a>
        <a href="usuario.php">Inicio</a>
      </div>
    </div>
    <div class="landing-actions">
      <a href="trabajador.php" class="landing-link">Volver</a>
    </div>
  </div>
</header>

<div class="container">
  <div class="main-content">
    <h2 class="welcome">Gestionar Valoraciones</h2>
    <p class="muted mb-3">Responde y gestiona las valoraciones de los clientes</p>
    
    <?php if ($mensaje): ?>
      <div class="alert alert-<?= $tipo_alerta ?> alert-spaced">
        <?= htmlspecialchars($mensaje) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($valoraciones)): ?>
      <div class="alert alert-info alert-spaced">
        No hay valoraciones aún.
      </div>
    <?php else: ?>
      <div style="background: white; padding: 2rem; border-radius: 8px;">
        <h3>Total de valoraciones: <?= count($valoraciones) ?></h3>
        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #eee;">
        
        <?php foreach ($valoraciones as $val): ?>
          <div class="valoracion-card">
            <div class="valoracion-header">
              <div>
                <div class="valoracion-usuario"><?= htmlspecialchars($val['nombre']) ?></div>
                <small style="color: #999;"><?= htmlspecialchars($val['email']) ?></small>
              </div>
              <span class="valoracion-producto"><?= htmlspecialchars($val['producto']) ?></span>
              <div class="valoracion-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <img src="assets/<?= $i <= $val['puntuacion'] ? 'estrellaSelecionada' : 'estrellaNegra' ?>.png" alt="⭐">
                <?php endfor; ?>
              </div>
            </div>
            
            <?php if ($val['comentario']): ?>
              <div class="valoracion-comentario">
                "<?= htmlspecialchars($val['comentario']) ?>"
              </div>
            <?php endif; ?>
            
            <?php if ($val['respuesta']): ?>
              <div class="respuesta-section">
                <strong>✓ Respuesta del restaurante:</strong>
                <div class="respuesta-existente">
                  <?= htmlspecialchars($val['respuesta']) ?>
                </div>
              </div>
            <?php endif; ?>
            
            <div class="valoracion-acciones">
              <button class="btn-responder toggle-respuesta" data-id="<?= $val['id'] ?>">
                <?= $val['respuesta'] ? '✎ Editar respuesta' : '💬 Responder' ?>
              </button>
              
              <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta valoración?');">
                <input type="hidden" name="id_valoracion" value="<?= $val['id'] ?>">
                <button type="submit" name="borrar" class="btn-borrar">🗑️ Eliminar</button>
              </form>
            </div>
            
            <!-- Formulario de respuesta -->
            <form method="POST" class="respuesta-form-hidden" id="form-<?= $val['id'] ?>">
              <div class="respuesta-section">
                <input type="hidden" name="id_valoracion" value="<?= $val['id'] ?>">
                <div class="form-respuesta">
                  <textarea name="respuesta" placeholder="Escribe tu respuesta aquí..." required><?= $val['respuesta'] ? htmlspecialchars($val['respuesta']) : '' ?></textarea>
                  <button type="submit" name="responder">📤 Enviar</button>
                </div>
                <small style="color: #666; display: block; margin-top: 0.5rem;">
                  Máximo 500 caracteres. Se enviará una notificación al cliente.
                </small>
              </div>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer>
  <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<script>
// Dropdown del perfil
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

// Toggle de formularios de respuesta
document.querySelectorAll('.toggle-respuesta').forEach(btn => {
  btn.addEventListener('click', function() {
    const id = this.dataset.id;
    const form = document.getElementById(`form-${id}`);
    form.classList.toggle('respuesta-form-hidden');
  });
});
</script>
<script src="assets/mobile-header.js?v=20260211-6"></script>
</body>
</html>
