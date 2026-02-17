<?php
/**
 * ===============================================================================
 * EJEMPLOS_INTEGRACION_VALORACIONES.php
 * ===============================================================================
 * 
 * Este archivo contiene ejemplos de código para integrar el sistema de
 * valoraciones en diferentes partes del proyecto Zyma.
 * 
 * NO es un archivo ejecutable. Solo consulta este código y cópialo donde
 * necesites las funcionalidades.
 */

/*
 * ===============================================================================
 * EJEMPLO 1: Mostrar Promedio de Valoraciones en Dashboard
 * ===============================================================================
 * 
 * Ubicación: usuario.php, después del header
 * Propósito: Mostrar un resumen rápido de las valoraciones
 */

?>

<?php
// CÓDIGO A COPIAR EN usuario.php (después del header, antes del main content)

// Cargar estadísticas de valoraciones
$promedio_estrellas = 0;
$total_opiniones = 0;

try {
    $stmt = $pdo->prepare("
        SELECT 
            AVG(puntuacion) as promedio,
            COUNT(*) as total
        FROM valoraciones
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    
    $promedio_estrellas = $stats['promedio'] ? round($stats['promedio'], 1) : 0;
    $total_opiniones = $stats['total'] ?? 0;
} catch (Exception $e) {
    error_log("Error cargando valoraciones: " . $e->getMessage());
}
?>

<!-- HTML A MOSTRAR EN DASHBOARD -->
<?php if ($total_opiniones > 0): ?>
    <div class="ratings-widget">
        <h3>Nuestras Valoraciones</h3>
        <div class="ratings-widget-content">
            <div class="ratings-display">
                <span class="rating-number"><?= $promedio_estrellas ?>/5</span>
                <span class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i <= round($promedio_estrellas) ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                </span>
                <span class="rating-count"><?= $total_opiniones ?> opiniones</span>
            </div>
            <a href="valoraciones.php" class="btn-link">Ver todas las opiniones →</a>
        </div>
    </div>
<?php endif; ?>

<?php
// CSS PARA user.php (añadir a styles.css)
?>

.ratings-widget {
    background: linear-gradient(135deg, #EECF6D, #f0d956);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(238, 207, 109, 0.2);
}

.ratings-widget h3 {
    color: #720E07;
    margin-bottom: 1rem;
    font-weight: 700;
}

.ratings-widget-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.ratings-display {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.rating-number {
    font-size: 1.6rem;
    font-weight: 800;
    color: #720E07;
}

.rating-stars {
    display: flex;
    gap: 0.2rem;
}

.rating-stars .star {
    color: #ccc;
    font-size: 1.2rem;
}

.rating-stars .star.filled {
    color: #720E07;
}

.rating-count {
    font-size: 0.85rem;
    color: #45050C;
    font-weight: 600;
}

.btn-link {
    color: #720E07;
    text-decoration: none;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.25s ease;
}

.btn-link:hover {
    background: rgba(114, 14, 7, 0.1);
}

<?php
/*
 * ===============================================================================
 * EJEMPLO 2: Cargar y Mostrar Widget Mini de Valoraciones
 * ===============================================================================
 * 
 * Ubicación: carta.php o confirmacion_pedido.php
 * Propósito: Mostrar pequeño widget con valoración promedio
 */
?>

<?php
// CÓDIGO PARA MINI WIDGET
function obtenerPromedio() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT AVG(puntuacion) FROM valoraciones");
        $resultado = $stmt->fetchColumn();
        return $resultado ? round($resultado, 1) : 0;
    } catch (Exception $e) {
        return 0;
    }
}

$promedio = obtenerPromedio();
?>

<!-- HTML MINI WIDGET -->
<div class="mini-rating-widget">
    <span class="mini-rating-label">Calificación:</span>
    <div class="mini-rating-display">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="mini-star <?= $i <= $promedio ? 'active' : '' ?>">★</span>
        <?php endfor; ?>
    </div>
    <span class="mini-rating-value"><?= $promedio ?>/5</span>
</div>

<?php
// CSS PARA MINI WIDGET
?>

.mini-rating-widget {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 3px solid #EECF6D;
}

.mini-rating-label {
    font-size: 0.85rem;
    color: #666;
    font-weight: 600;
}

.mini-rating-display {
    display: flex;
    gap: 0.2rem;
}

.mini-star {
    color: #ddd;
    font-size: 0.9rem;
}

.mini-star.active {
    color: #EECF6D;
}

.mini-rating-value {
    font-size: 0.85rem;
    color: #720E07;
    font-weight: 700;
}

<?php
/*
 * ===============================================================================
 * EJEMPLO 3: Crear Notificación Cuando Hay Nueva Valoración
 * ===============================================================================
 * 
 * Ubicación: archivo nuevo o admin.php
 * Propósito: Notificar cuando un cliente comparte opinión
 */
?>

<?php
// FUNCIÓN PARA ENVIAR NOTIFICACIÓN
function notificarNuevaValoracion($pdo, $id_usuario, $puntuacion) {
    try {
        // Obtener datos del usuario
        $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id_usuario]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) return false;
        
        $nombre_usuario = $usuario['nombre'] ?? strstr($usuario['email'], '@', true);
        
        // Crear mensaje
        $mensaje = "$nombre_usuario valoró el restaurante con $puntuacion ⭐";
        
        // Insertar en notificaciones (para admin)
        $stmt = $pdo->prepare("
            INSERT INTO notificaciones 
            (id_usuario, mensaje, leida, fecha)
            VALUES (?, ?, 0, NOW())
        ");
        
        // ID 1 generalmente es el admin
        $stmt->execute([1, $mensaje]);
        
        return true;
    } catch (Exception $e) {
        error_log("Error en notificación: " . $e->getMessage());
        return false;
    }
}

// USAR EN guardar_valoracion.php (después de INSERT/UPDATE):
/*
$puntuacion = $_POST['puntuacion'];
notificarNuevaValoracion($pdo, $_SESSION['user_id'], $puntuacion);
*/
?>

<?php
/*
 * ===============================================================================
 * EJEMPLO 4: Filtrar Valoraciones por Puntuación
 * ===============================================================================
 * 
 * Ubicación: valoraciones.php (función nueva)
 * Propósito: Permitir filtrar opiniones por 1, 2, 3, 4 o 5 estrellas
 */
?>

<?php
// FUNCIÓN HELPER
function obtenerValoracionesFiltradas($pdo, $filtro = null) {
    try {
        $sql = "
            SELECT v.id, v.puntuacion, v.comentario, v.fecha_creacion,
                   u.nombre, u.email
            FROM valoraciones v
            JOIN usuarios u ON v.id_usuario = u.id
        ";
        
        if ($filtro && is_numeric($filtro) && $filtro >= 1 && $filtro <= 5) {
            $sql .= " WHERE v.puntuacion = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY v.fecha_creacion DESC");
            $stmt->execute([$filtro]);
        } else {
            $stmt = $pdo->prepare($sql . " ORDER BY v.fecha_creacion DESC");
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// USAR:
$filtro = $_GET['filtro'] ?? null;
$valoraciones = obtenerValoracionesFiltradas($pdo, $filtro);
?>

<!-- HTML BOTONES FILTRO -->
<div class="filter-buttons">
    <a href="valoraciones.php" class="filter-btn <?= !isset($_GET['filtro']) ? 'active' : '' ?>">
        Todos
    </a>
    <?php for ($i = 5; $i >= 1; $i--): ?>
        <a href="?filtro=<?= $i ?>" class="filter-btn <?= ($_GET['filtro'] ?? null) == $i ? 'active' : '' ?>">
            <?php for ($j = 1; $j <= $i; $j++): ?>
                <span class="filter-star">★</span>
            <?php endfor; ?>
        </a>
    <?php endfor; ?>
</div>

<?php
// CSS
?>

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 0.6rem 1.2rem;
    background: var(--light-gray);
    border-radius: 20px;
    text-decoration: none;
    color: var(--ink);
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.25s ease;
    cursor: pointer;
}

.filter-btn:hover {
    background: var(--gold);
}

.filter-btn.active {
    background: var(--dark-red);
    color: white;
}

.filter-star {
    color: var(--gold);
}

<?php
/*
 * ===============================================================================
 * EJEMPLO 5: Validar Que Usuario Compró Antes de Valorar
 * ===============================================================================
 * 
 * Ubicación: guardar_valoracion.php (en la sección de validación)
 * Propósito: Solo permitir valorar a usuarios que compraron
 */
?>

<?php
// VALIDACIÓN ADICIONAL EN guardar_valoracion.php

function usuarioTieneCompras($pdo, $id_usuario) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM pedidos 
            WHERE id_usuario = ? AND estado IN ('entregado', 'listo')
        ");
        $stmt->execute([$id_usuario]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    } catch (Exception $e) {
        return false;
    }
}

// USAR EN guardar_valoracion.php:
/*
if (!usuarioTieneCompras($pdo, $id_usuario)) {
    http_response_code(403);
    echo json_encode(['error' => 'Solo usuarios con pedidos pueden valorar.']);
    exit;
}
*/
?>

<?php
/*
 * ===============================================================================
 * EJEMPLO 6: Exportar Valoraciones a CSV
 * ===============================================================================
 * 
 * Ubicación: archivo nuevo "exportar_valoraciones.php"
 * Propósito: Exportar todas las valoraciones para análisis
 */
?>

<?php
// ARCHIVO: exportar_valoraciones.php

session_start();

// Solo admin puede exportar
if (@$_SESSION['worker_code'] !== 'ADMIN_CODE') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

require_once 'config.php';

try {
    $stmt = $pdo->query("
        SELECT v.id, v.puntuacion, v.comentario, v.fecha_creacion,
               u.nombre, u.email
        FROM valoraciones v
        JOIN usuarios u ON v.id_usuario = u.id
        ORDER BY v.fecha_creacion DESC
    ");
    $valoraciones = $stmt->fetchAll();
    
    // Headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="valoraciones_' . date('Y-m-d') . '.csv"');
    
    // BOM para UTF-8
    echo "\xEF\xBB\xBF";
    
    // Encabezados CSV
    echo "ID,Usuario,Email,Puntuación,Comentario,Fecha\n";
    
    // Datos
    foreach ($valoraciones as $v) {
        echo sprintf(
            '%d,"%s","%s",%d,"%s",%s' . "\n",
            $v['id'],
            str_replace('"', '""', $v['nombre'] ?? ''),
            str_replace('"', '""', $v['email']),
            $v['puntuacion'],
            str_replace('"', '""', $v['comentario'] ?? ''),
            $v['fecha_creacion']
        );
    }
    exit;
    
} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>

<?php
/*
 * ===============================================================================
 * EJEMPLO 7: Mostrar Reporte de Valoraciones en Admin
 * ===============================================================================
 * 
 * Ubicación: estadisticas.php o admin.php
 * Propósito: Dashboard con análisis de valoraciones
 */
?>

<?php
// FUNCIÓN PARA OBTENER ANÁLISIS
function obtenerAnalisisValoraciones($pdo) {
    $resultado = [
        'total' => 0,
        'promedio' => 0,
        'distribucion' => [],
        'tendencia' => [],
        'ultima_valoracion' => null
    ];
    
    try {
        // Total y promedio
        $stmt = $pdo->query("
            SELECT COUNT(*) as total, AVG(puntuacion) as promedio
            FROM valoraciones
        ");
        $data = $stmt->fetch();
        $resultado['total'] = $data['total'];
        $resultado['promedio'] = $data['promedio'] ? round($data['promedio'], 2) : 0;
        
        // Distribución
        $stmt = $pdo->query("
            SELECT puntuacion, COUNT(*) as cantidad
            FROM valoraciones
            GROUP BY puntuacion
            ORDER BY puntuacion
        ");
        foreach ($stmt->fetchAll() as $row) {
            $resultado['distribucion'][$row['puntuacion']] = $row['cantidad'];
        }
        
        // Tendencia (últimos 30 días)
        $stmt = $pdo->query("
            SELECT DATE(fecha_creacion) as fecha, AVG(puntuacion) as promedio, COUNT(*) as cantidad
            FROM valoraciones
            WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(fecha_creacion)
            ORDER BY fecha DESC
            LIMIT 30
        ");
        $resultado['tendencia'] = $stmt->fetchAll();
        
        // Última valoración
        $stmt = $pdo->query("
            SELECT v.*, u.nombre FROM valoraciones v
            JOIN usuarios u ON v.id_usuario = u.id
            ORDER BY v.fecha_creacion DESC
            LIMIT 1
        ");
        $resultado['ultima_valoracion'] = $stmt->fetch();
        
    } catch (Exception $e) {
        error_log("Error en análisis: " . $e->getMessage());
    }
    
    return $resultado;
}

// USAR:
$analisis = obtenerAnalisisValoraciones($pdo);
?>

<!-- HTML REPORTE -->
<div class="admin-ratings-report">
    <h2>Análisis de Valoraciones</h2>
    
    <div class="report-stats">
        <div class="stat-box">
            <span class="stat-label">Total Valoraciones</span>
            <span class="stat-value"><?= $analisis['total'] ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-label">Promedio</span>
            <span class="stat-value"><?= $analisis['promedio'] ?>/5</span>
        </div>
    </div>
    
    <div class="distribution-chart">
        <h3>Distribución</h3>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <?php $cantidad = $analisis['distribucion'][$i] ?? 0; ?>
            <div class="chart-row">
                <span><?= $i ?>★</span>
                <div class="bar" style="width: <?= ($cantidad / max(array_values($analisis['distribucion']), 1)) * 100 ?>%"></div>
                <span><?= $cantidad ?></span>
            </div>
        <?php endfor; ?>
    </div>
</div>

<?php
/*
 * ===============================================================================
 * EJEMPLO 8: Enviar Email Cuando Hay Nueva Valoración
 * ===============================================================================
 * 
 * Ubicación: guardar_valoracion.php (después de INSERT/UPDATE)
 * Propósito: Notificar al admin por email
 */
?>

<?php
// FUNCIÓN ENVIAR EMAIL
function enviarEmailNuevaValoracion($pdo, $id_usuario, $puntuacion, $comentario) {
    try {
        $usuario = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?")->execute([$id_usuario])->fetch();
        $admin = $pdo->prepare("SELECT email FROM usuarios WHERE worker_code = ?")->execute(['ADMIN_CODE'])->fetch();
        
        if (!$admin) return false;
        
        $nombre = $usuario['nombre'] ?? strstr($usuario['email'], '@', true);
        $estrellas = str_repeat('⭐', $puntuacion) . str_repeat('☆', 5 - $puntuacion);
        
        $asunto = "Nueva valoración de $nombre - $estrellas";
        $cuerpo = "
            <h2>Nueva Valoración Recibida</h2>
            <p><strong>Cliente:</strong> $nombre</p>
            <p><strong>Email:</strong> {$usuario['email']}</p>
            <p><strong>Puntuación:</strong> $puntuacion/5 $estrellas</p>
            <p><strong>Comentario:</strong></p>
            <p>{$comentario}</p>
            <p><a href='http://localhost/zyma-main/valoraciones.php'>Ver en Zyma</a></p>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@zyma.com" . "\r\n";
        
        return mail($admin['email'], $asunto, $cuerpo, $headers);
    } catch (Exception $e) {
        error_log("Error email: " . $e->getMessage());
        return false;
    }
}

// USAR EN guardar_valoracion.php:
/*
enviarEmailNuevaValoracion($pdo, $id_usuario, $puntuacion, $comentario);
*/
?>

<?php
/*
 * ===============================================================================
 * FIN DE EJEMPLOS
 * ===============================================================================
 * 
 * Estos son ejemplos avanzados. No es necesario implementar todos para que
 * funcione el sistema básico.
 * 
 * Para más ayuda, consulta:
 * - README_VALORACIONES.md (documentación completa)
 * - INSTALACION_VALORACIONES.md (guía de instalación)
 * - valoraciones.php (código principal)
 * - guardar_valoracion.php (procesamiento)
 */
?>
