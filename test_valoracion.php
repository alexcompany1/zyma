<?php
/**
 * test_valoracion.php
 * Script de prueba para testear manualmente el guardado de valoraciones
 */

session_start();

// Verificar si es trabajador
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("Acceso denegado. Solo trabajadores pueden ejecutar esto.");
}

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test de Valoración</title>
<link rel="stylesheet" href="styles.css?v=20260211-5">
</head>
<body>
<div class="container">
    <div class="main-content">
        <h2 class="welcome">Test de Valoración Manual</h2>
        
        <p>Este formulario permite enviar una prueba de valoración para verificar que el sistema funciona correctamente.</p>
        
        <?php
            // Obtener productos
            try {
                $stmt = $pdo->query("SELECT id, nombre FROM productos LIMIT 10");
                $productos = $stmt->fetchAll();
            } catch (Exception $e) {
                echo '<div class="alert alert-error alert-spaced">';
                echo 'Error obtiendo productos: ' . htmlspecialchars($e->getMessage());
                echo '</div>';
                $productos = [];
            }
        ?>
        
        <?php if (!empty($productos)): ?>
            <form method="POST" id="testForm" class="form-container" style="margin: 2rem 0;">
                <div class="form-group">
                    <label for="producto">Producto a valorar:</label>
                    <select name="id_producto" id="producto" required>
                        <option value="">-- Selecciona un producto --</option>
                        <?php foreach ($productos as $prod): ?>
                            <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="puntuacion">Puntuación (1-5):</label>
                    <select name="puntuacion" id="puntuacion" required>
                        <option value="">-- Selecciona puntuación --</option>
                        <option value="1">1 - Muy malo</option>
                        <option value="2">2 - Malo</option>
                        <option value="3">3 - Normal</option>
                        <option value="4">4 - Bueno</option>
                        <option value="5">5 - Excelente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="comentario">Comentario (opcional):</label>
                    <textarea name="comentario" id="comentario" placeholder="Escribe tu opinión..." style="height: 100px;"></textarea>
                </div>
                
                <button type="submit" class="btn-add-cart">Enviar Prueba</button>
            </form>
            
            <div id="resultado" style="margin-top: 2rem;"></div>
        <?php else: ?>
            <div class="alert alert-warning alert-spaced">
                No hay productos disponibles en la base de datos.
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem;">
            <a href="diagnostico_valoraciones.php" class="btn-secondary">Ver Diagnóstico</a>
            <a href="trabajador.php" class="btn-secondary">Volver al Panel</a>
        </div>
    </div>
</div>

<script>
document.getElementById('testForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultado = document.getElementById('resultado');
    
    resultado.innerHTML = '<p>Enviando prueba...</p>';
    
    try {
        const response = await fetch('guardar_valoracion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            resultado.innerHTML = `
                <div class="alert alert-success alert-spaced">
                    <strong>Exito!</strong> La valoración se guardó correctamente.<br>
                    Mensaje: ${data.mensaje}
                </div>
            `;
        } else {
            resultado.innerHTML = `
                <div class="alert alert-error alert-spaced">
                    <strong>Error:</strong> ${data.error}<br>
                    ${data.debug ? '<br>Debug: ' + data.debug : ''}
                </div>
            `;
        }
    } catch (error) {
        resultado.innerHTML = `
            <div class="alert alert-error alert-spaced">
                <strong>Error de conexión:</strong> ${error.message}
            </div>
        `;
    }
});
</script>

<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

</body>
</html>
