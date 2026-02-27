<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * valoraciones.php
 * Página que muestra valoraciones de productos individuales.
 * Permite a usuarios logueados valorar cada producto del restaurante.
 */

session_start();
require_once 'config.php';

// Verificar y crear tabla de valoraciones si no existe
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'valoraciones'");
    if (!$stmt->fetch()) {
        // Tabla no existe, crearla directamente
        $crearTabla = "CREATE TABLE IF NOT EXISTS valoraciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
            id_producto INT NOT NULL,
            FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
            puntuacion INT NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
            comentario TEXT,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_usuario_producto (id_usuario, id_producto),
            INDEX idx_usuario (id_usuario),
            INDEX idx_producto (id_producto),
            INDEX idx_puntuacion (puntuacion),
            INDEX idx_fecha (fecha_creacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($crearTabla);
        error_log("Tabla de valoraciones creada automáticamente");
    }
} catch (Exception $e) {
    error_log("Error verificando tabla de valoraciones: " . $e->getMessage());
}

// Determinar si el usuario está logueado
$is_logged_in = !empty($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$is_worker = !empty($_SESSION['worker_code']);

// Obtener ID del producto desde URL si existe
$producto_id_desde_url = isset($_GET['producto']) ? (int)$_GET['producto'] : 0;

// Obtener todos los productos
try {
    $stmt = $pdo->query("SELECT id, nombre, precio, imagen FROM productos ORDER BY nombre");
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error obteniendo productos: " . $e->getMessage());
    $productos = [];
}

// Obtener estadísticas de valoraciones POR PRODUCTO y valoraciones del usuario actual
$valoraciones_por_producto = [];
$mis_valoraciones = [];

try {
    // Obtener promedio y total de valoraciones por cada producto
    foreach ($productos as $producto) {
        $producto_id = $producto['id'];
        
        // Estadísticas del producto
        $stmt = $pdo->prepare("
            SELECT 
                AVG(puntuacion) as promedio,
                COUNT(*) as total,
                GROUP_CONCAT(puntuacion) as puntuaciones
            FROM valoraciones
            WHERE id_producto = :id_producto
        ");
        $stmt->execute([':id_producto' => $producto_id]);
        $stats = $stmt->fetch() ?? [];
        
        // Calcular distribución de puntuaciones para este producto
        $distribucion = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        if (!empty($stats['puntuaciones'])) {
            $puntuaciones = explode(',', $stats['puntuaciones']);
            foreach ($puntuaciones as $p) {
                $distribucion[(int)$p]++;
            }
        }
        
        $valoraciones_por_producto[$producto_id] = [
            'promedio' => !empty($stats['promedio']) ? round($stats['promedio'], 1) : 0,
            'total' => !empty($stats['total']) ? (int)$stats['total'] : 0,
            'distribucion' => $distribucion
        ];
        
        // Obtener valoración del usuario actual para este producto (si está logueado)
        if ($is_logged_in && $user_id) {
            $stmt = $pdo->prepare("
                SELECT id, puntuacion, comentario
                FROM valoraciones
                WHERE id_usuario = :id_usuario AND id_producto = :id_producto
            ");
            $stmt->execute([
                ':id_usuario' => $user_id,
                ':id_producto' => $producto_id
            ]);
            $valor = $stmt->fetch();
            if ($valor) {
                $mis_valoraciones[$producto_id] = $valor;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error en valoraciones.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zyma - Valoraciones y Opiniones</title>
    <link rel="stylesheet" href="styles.css?v=20260227-4">
    <style>
        /* Estilos adicionales para valoraciones - Ver más abajo en el archivo de CSS */
    </style>
</head>
<body>

<?php
// Incluir header unificado solo si el usuario está logueado
if ($is_logged_in) {
    $show_cart = true;
    $show_notif = true;
    $home_link = 'usuario.php';
    require_once 'header.php';
}
?>

<!-- Contenedor principal -->
<div class="container">
    <main class="main-content">
        
        <!-- Título de la página -->
        <div class="center mb-4">
            <h1 class="welcome">Valoraciones de Productos</h1>
            <p class="muted lead">
                Comparte tu opinión sobre los productos de Zyma.
                <?php if ($is_logged_in): ?>
                    Tu valoración ayuda a otros clientes a tomar mejores decisiones.
                <?php else: ?>
                    Inicia sesión para valorar nuestros productos.
                <?php endif; ?>
            </p>
        </div>

        <!-- Grid de productos con valoraciones -->
        <?php if (empty($productos)): ?>
            <div class="empty-state">
                <p>No hay productos disponibles en este momento.</p>
            </div>
        <?php else: ?>
            <div class="products-ratings-grid">
                <?php foreach ($productos as $producto): ?>
                    <?php 
                        $pid = $producto['id'];
                        // Si viene producto desde URL, mostrar solo ese
                        if ($producto_id_desde_url > 0 && $pid !== $producto_id_desde_url) {
                            continue;
                        }
                        $stats = $valoraciones_por_producto[$pid] ?? ['promedio' => 0, 'total' => 0, 'distribucion' => []];
                        $mi_valoracion = $mis_valoraciones[$pid] ?? null;
                    ?>
                    <div class="product-rating-card">
                        
                        <!-- Imagen del producto -->
                        <div class="product-rating-image">
                            <?php if (!empty($producto['imagen'])): ?>
                                <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                            <?php else: ?>
                                <div class="product-placeholder">Sin imagen</div>
                            <?php endif; ?>
                        </div>

                        <!-- Información del producto -->
                        <div class="product-rating-info">
                            <h3 class="product-name"><?= htmlspecialchars($producto['nombre']) ?></h3>
                            <div class="product-price">$<?= number_format($producto['precio'], 2, ',', '.') ?></div>

                            <!-- Estadísticas de valoración -->
                            <div class="product-rating-stats">
                                <div class="rating-score-small">
                                    <span class="rating-number"><?= $stats['promedio'] ?>/5</span>
                                    <div class="rating-stars-small">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star-small <?= $i <= floor($stats['promedio']) ? 'filled' : '' ?>">*</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count-small">(<?= $stats['total'] ?> opinión<?= $stats['total'] !== 1 ? 'es' : '' ?>)</span>
                                </div>
                            </div>

                            <!-- Formulario para valorar (solo si está logueado) -->
                            <?php if ($is_logged_in): ?>
                                <form class="quick-rating-form" data-product-id="<?= $pid ?>" data-product-name="<?= htmlspecialchars($producto['nombre']) ?>">
                                    
                                    <!-- Selector de estrellas con imágenes -->
                                    <div class="quick-star-selector" data-product-id="<?= $pid ?>">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input 
                                                type="radio" 
                                                name="puntuacion_<?= $pid ?>" 
                                                value="<?= $i ?>" 
                                                id="star-<?= $pid ?>-<?= $i ?>"
                                                class="quick-star-radio"
                                                <?php if ($mi_valoracion && $mi_valoracion['puntuacion'] === $i): ?>
                                                    checked
                                                <?php endif; ?>
                                            >
                                            <label for="star-<?= $pid ?>-<?= $i ?>" class="quick-star-label" data-star="<?= $i ?>">
                                                <img src="assets/estrellaNegra.png" alt="Estrella <?= $i ?>" class="star-img-empty">
                                                <img src="assets/estrellaSelecionada.png" alt="Estrella <?= $i ?> seleccionada" class="star-img-filled">
                                            </label>
                                        <?php endfor; ?>
                                    </div>

                                    <!-- Campo de comentario (oculto por defecto) -->
                                    <textarea 
                                        name="comentario_<?= $pid ?>" 
                                        class="quick-comment-field"
                                        placeholder="Comparte tu opinión..."
                                        maxlength="300"
                                        rows="2"
                                        style="display: none;"
                                    ><?php if ($mi_valoracion && !empty($mi_valoracion['comentario'])): ?><?= htmlspecialchars($mi_valoracion['comentario']) ?><?php endif; ?></textarea>

                                    <!-- Botón enviar -->
                                    <button type="submit" class="btn-rate-product">
                                        <?php if ($mi_valoracion): ?>
                                            Actualizar
                                        <?php else: ?>
                                            Valorar
                                        <?php endif; ?>
                                    </button>

                                    <div class="rate-message" style="display: none;"></div>
                                </form>
                            <?php else: ?>
                                <p class="login-hint">
                                    <a href="login.php">Inicia sesión</a> para valorar este producto
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Opiniones de otros usuarios -->
                        <?php if ($stats['total'] > 0): ?>
                            <?php
                                // Obtener todas las opiniones del producto ANTES de mostrar el botón
                                $opiniones = [];
                                try {
                                    $stmt = $pdo->prepare("
                                        SELECT v.id, v.puntuacion, v.comentario, v.fecha_creacion, u.nombre, u.email,
                                               rv.respuesta, rv.fecha_creacion as fecha_respuesta
                                        FROM valoraciones v
                                        JOIN usuarios u ON v.id_usuario = u.id
                                        LEFT JOIN respuestas_valoraciones rv ON v.id = rv.id_valoracion
                                        WHERE v.id_producto = :id_producto
                                        ORDER BY v.fecha_creacion DESC
                                    ");
                                    $stmt->execute([':id_producto' => $pid]);
                                    $opiniones = $stmt->fetchAll();
                                } catch (Exception $e) {
                                    error_log("Error obteniendo opiniones: " . $e->getMessage());
                                }
                            ?>
                            <div class="product-recent-opinions">
                                <button id="reviewToggle-<?= $pid ?>" class="review-toggle-btn" onclick="toggleReviewsDropdown(<?= $pid ?>)" style="cursor: pointer; padding: 10px; background: linear-gradient(135deg, #d4af37, #f4e4a6); border-radius: 6px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-weight: 600; width: 100%; border: none; font-size: 1em;">
                                    Ver resenas (<?= $stats['total'] ?>)
                                </button>
                                <div id="reviewsDropdown-<?= $pid ?>" style="display: none; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-top: 10px; max-height: 400px; overflow-y: auto;">
                                <?php if (count($opiniones) > 0): ?>
                                    <?php foreach ($opiniones as $op): ?>
                                        <div class="opinion-snippet">
                                            <div class="opinion-header">
                                                <div class="opinion-user-info">
                                                    <span class="opinion-name">
                                                        <?= !empty($op['nombre']) 
                                                            ? htmlspecialchars($op['nombre'])
                                                            : htmlspecialchars(strstr($op['email'], '@', true));
                                                        ?>
                                                    </span>
                                                    <span class="opinion-date">
                                                        <?php
                                                            $fecha = new DateTime($op['fecha_creacion']);
                                                            echo $fecha->format('d/m/Y H:i');
                                                        ?>
                                                    </span>
                                                </div>
                                                <span class="opinion-stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <img src="assets/<?= $i <= $op['puntuacion'] ? 'estrellaSelecionada' : 'estrellaNegra' ?>.png" 
                                                             alt="Estrella" 
                                                             class="star-opinion-img"
                                                             title="<?= $op['puntuacion'] ?>/5">
                                                    <?php endfor; ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($op['comentario'])): ?>
                                                <p class="opinion-text"><?= htmlspecialchars($op['comentario']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($op['respuesta'])): ?>
                                                <div style="background: #e8f4f8; padding: 1rem; border-radius: 6px; margin-top: 1rem; border-left: 4px solid #2196F3;">
                                                    <div style="font-weight: 700; color: #0c47a1; margin-bottom: 0.5rem;">
                                                        [Respuesta del restaurante Zyma]:
                                                    </div>
                                                    <p style="color: #555; margin: 0; line-height: 1.5;">
                                                        <?= htmlspecialchars($op['respuesta']) ?>
                                                    </p>
                                                    <small style="color: #999; display: block; margin-top: 0.5rem;">
                                                        <?php
                                                            $fecha = new DateTime($op['fecha_respuesta']);
                                                            echo 'Respondido: ' . $fecha->format('d/m/Y H:i');
                                                        ?>
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <?php // Opcion de responder eliminada ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: #666; text-align: center;">No hay reseñas disponibles.</p>
                                <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Botón para volver a inicio (si está logueado) -->
        <?php if ($is_logged_in): ?>
            <div class="btn-row center mt-4">
                <a href="usuario.php" class="btn-secondary">Volver al inicio</a>
                <a href="carta.php" class="btn-secondary">Ver Carta</a>
            </div>
        <?php endif; ?>

    </main>
</div>

<!-- Footer -->
<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

<!-- Script para manejar el formulario de valoraciones de productos -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formas = document.querySelectorAll('.quick-rating-form');

        formas.forEach(form => {
            const productId = form.dataset.productId;
            const productName = form.dataset.productName;
            const starRadios = form.querySelectorAll('.quick-star-radio');
            const starLabels = form.querySelectorAll('.quick-star-label');
            const commentField = form.querySelector('.quick-comment-field');
            const submitBtn = form.querySelector('.btn-rate-product');
            const rateMessage = form.querySelector('.rate-message');

            // Función para actualizar estrellas de izquierda a derecha
            function updateStars(upToStar) {
                starLabels.forEach((label) => {
                    const starNumber = parseInt(label.getAttribute('data-star'));
                    const filledImg = label.querySelector('.star-img-filled');
                    const emptyImg = label.querySelector('.star-img-empty');
                    
                    if (starNumber <= upToStar) {
                        // Llenar estrellas de izquierda a derecha
                        filledImg.style.opacity = '1';
                        emptyImg.style.opacity = '0';
                    } else {
                        // Vaciar estrellas a la derecha
                        filledImg.style.opacity = '0';
                        emptyImg.style.opacity = '1';
                    }
                });
            }

            // Hover: mostrar preview de izquierda a derecha
            starLabels.forEach((label) => {
                label.addEventListener('mouseenter', function() {
                    const starNumber = parseInt(label.getAttribute('data-star'));
                    updateStars(starNumber);
                });
            });

            // Al salir del selector, restaurar estado guardado
            const starSelector = form.querySelector('.quick-star-selector');
            starSelector.addEventListener('mouseleave', function() {
                restoreStarState();
            });

            function restoreStarState() {
                const checkedRadio = form.querySelector('input[name="puntuacion_' + productId + '"]:checked');
                if (checkedRadio) {
                    updateStars(parseInt(checkedRadio.value));
                } else {
                    updateStars(0);
                }
            }

            // Al hacer click en una estrella
            starRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    commentField.style.display = 'block';
                    updateStars(parseInt(this.value));
                });
            });

            // Enviar forma por AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const selectedRating = form.querySelector('input[name="puntuacion_' + productId + '"]:checked');
                if (!selectedRating) {
                    showMessage('Por favor selecciona una puntuación', 'error', rateMessage);
                    return;
                }

                const data = {
                    id_producto: productId,
                    puntuacion: selectedRating.value,
                    comentario: commentField.value
                };

                const formData = new FormData();
                formData.append('id_producto', data.id_producto);
                formData.append('puntuacion', data.puntuacion);
                formData.append('comentario', data.comentario);

                fetch('guardar_valoracion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showMessage('¡' + productName + ' valorado! Gracias por tu opinión.', 'success', rateMessage);
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        const errorMsg = result.error || 'Error al guardar la valoración';
                        showMessage(errorMsg, 'error', rateMessage);
                        console.error('Error en guardar_valoracion.php:', result);
                    }
                })
                .catch(error => {
                    console.error('Error de red:', error);
                    showMessage('Error de conexión. Intenta de nuevo.', 'error', rateMessage);
                });
            });

            function showMessage(msg, type, element) {
                element.textContent = msg;
                element.className = 'rate-message message-' + type;
                element.style.display = 'block';

                if (type === 'success') {
                    setTimeout(() => {
                        element.style.display = 'none';
                    }, 3000);
                }
            }

            // Inicializar estado de estrellas al cargar
            restoreStarState();
        });
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    #reviewsModal .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    #reviewsModal .review-author {
        font-weight: 600;
        font-size: 1.1em;
        color: #333;
    }
    
    #reviewsModal .review-date {
        font-size: 0.85em;
        color: #999;
    }
    
    #reviewsModal .review-stars {
        display: flex;
        gap: 3px;
        margin: 10px 0 15px 0;
    }
    
    #reviewsModal .review-text {
        font-size: 1.05em;
        line-height: 1.6;
        color: #555;
        margin: 15px 0;
        flex-grow: 1;
    }
    
    #reviewsModal .review-response {
        background: #f0f7ff;
        padding: 15px;
        border-left: 4px solid #2196F3;
        border-radius: 6px;
        margin-top: 15px;
        font-size: 0.95em;
        color: #0c47a1;
    }
</style>

<script>
    let allReviews = [];
    let currentReviewIndex = 0;
    
    function toggleReviewsDropdown(productId, reviews) {
        const dropdown = document.getElementById('reviewsDropdown-' + productId);
        const button = document.getElementById('reviewToggle-' + productId);
        
        if (!dropdown) return;
        
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
            button.style.background = 'linear-gradient(135deg, #c9a027, #e8d68a)';
        } else {
            dropdown.style.display = 'none';
            button.style.background = 'linear-gradient(135deg, #d4af37, #f4e4a6)';
        }
    }
    
    function closeReviewsModal() {
        document.getElementById('reviewsModal').style.display = 'none';
    }
    
    function showCurrentReview() {
        if (allReviews.length === 0) return;
        
        const review = allReviews[currentReviewIndex];
        const starsHtml = Array.from({length: 5}, (_, i) => 
            `<img src="assets/${i < review.puntuacion ? 'estrellaSelecionada' : 'estrellaNegra'}.png" alt="estrella" style="width: 20px; height: 20px;">`
        ).join('');
        
        const date = new Date(review.fecha_creacion).toLocaleDateString('es-ES');
        const author = review.nombre || review.email.split('@')[0];
        
        let content = `
            <div class="review-header">
                <div>
                    <div class="review-author">${author}</div>
                    <div class="review-date">${date}</div>
                </div>
                <div style="text-align: right;">
                    <div class="review-stars">${starsHtml}</div>
                    <div style="font-weight: 600; color: #d4af37;">${review.puntuacion}/5</div>
                </div>
            </div>
        `;
        
        if (review.comentario) {
            content += `<div class="review-text">${review.comentario}</div>`;
        }
        
        if (review.respuesta) {
            content += `
                <div class="review-response">
                    <strong>Respuesta del restaurante:</strong><br>
                    ${review.respuesta}
                </div>
            `;
        }
        
        document.getElementById('reviewContent').innerHTML = content;
        document.getElementById('reviewCounter').textContent = `${currentReviewIndex + 1} / ${allReviews.length}`;
    }
    
    function nextReview() {
        if (currentReviewIndex < allReviews.length - 1) {
            currentReviewIndex++;
            showCurrentReview();
        }
    }
    
    function prevReview() {
        if (currentReviewIndex > 0) {
            currentReviewIndex--;
            showCurrentReview();
        }
    }
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReviewsModal();
        }
    });
</script>

<?php if ($is_logged_in): ?>
    <script src="assets/mobile-header.js?v=20260211-6"></script>
<?php endif; ?>

</body>
</html>
