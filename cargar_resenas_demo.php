<?php
/**
 * cargar_resenas_demo.php
 * Carga reseñas de demostración en la base de datos
 */

session_start();

// Solo trabajadores
if (!isset($_SESSION['user_id']) || !$_SESSION['worker_code']) {
    die("Acceso denegado");
}

require_once 'config.php';

// Reseñas de demostración
$resenas_demo = [
    [
        'nombre_usuario' => 'María López',
        'email_usuario' => 'maria.lopez@email.com',
        'producto' => 'BBQ Hotdog',
        'puntuacion' => 5,
        'comentario' => 'El mejor hotdog que he probado en años. El pan está recién hecho y los ingredientes son de excelente calidad. ¡Vuelvo seguro!'
    ],
    [
        'nombre_usuario' => 'Carlos Mendez',
        'email_usuario' => 'carlos.mendez@email.com',
        'producto' => 'Hotdog',
        'puntuacion' => 5,
        'comentario' => 'Impresionante la atención. Pedí un hotdog con especificaciones y lo hicieron perfecto. Las proporciones son generosas.'
    ],
    [
        'nombre_usuario' => 'Andrea Ruiz',
        'email_usuario' => 'andrea.ruiz@email.com',
        'producto' => 'Nachos',
        'puntuacion' => 5,
        'comentario' => 'Los nachos están increíbles como acompañamiento. Recomiendo probar la salsa casera, tiene ese toque especial que no encuentras en otros lugares.'
    ],
    [
        'nombre_usuario' => 'Juan García',
        'email_usuario' => 'juan.garcia@email.com',
        'producto' => 'Vegan Hotdog',
        'puntuacion' => 5,
        'comentario' => 'Muy buen precio para la calidad que ofrece. La variedad vegana es excelente, por fin un lugar que nos atiende a todos.'
    ],
    [
        'nombre_usuario' => 'Patricia Fernández',
        'email_usuario' => 'patricia.fern@email.com',
        'producto' => 'Hotdog',
        'puntuacion' => 5,
        'comentario' => 'Mi familia adore este lugar. Los niños piden el menú especial y nosotros degustamos el auténtico sabor. ¡Excelente relación calidad-precio!'
    ],
    [
        'nombre_usuario' => 'Roberto Silva',
        'email_usuario' => 'roberto.silva@email.com',
        'producto' => 'BBQ Hotdog',
        'puntuacion' => 5,
        'comentario' => 'Los detalles marcan la diferencia. Desde el empaque hasta el sabor, todo grita calidad. Sin duda, mi lugar favorito para comer.'
    ]
];

$mensaje = '';
$tipo_alerta = '';
$cargadas = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cargar'])) {
    try {
        // Verificar tabla de valoraciones
        $verificacion = $pdo->query("SHOW TABLES LIKE 'valoraciones'")->fetch();
        if (!$verificacion) {
            throw new Exception("La tabla de valoraciones no existe. Instálala primero.");
        }

        foreach ($resenas_demo as $resena) {
            try {
                // Crear usuario si no existe
                $stmtUser = $pdo->prepare("
                    SELECT id FROM usuarios WHERE email = :email
                ");
                $stmtUser->execute([':email' => $resena['email_usuario']]);
                $usuario = $stmtUser->fetch();
                
                if (!$usuario) {
                    // Crear usuario de demostración
                    $stmtCreate = $pdo->prepare("
                        INSERT INTO usuarios (nombre, email, contraseña)
                        VALUES (:nombre, :email, :contraseña)
                    ");
                    $stmtCreate->execute([
                        ':nombre' => $resena['nombre_usuario'],
                        ':email' => $resena['email_usuario'],
                        ':contraseña' => password_hash('demo123', PASSWORD_DEFAULT)
                    ]);
                    $id_usuario = $pdo->lastInsertId();
                } else {
                    $id_usuario = $usuario['id'];
                }

                // Obtener producto
                $stmtProd = $pdo->prepare("
                    SELECT id FROM productos WHERE nombre LIKE :nombre
                ");
                $stmtProd->execute([':nombre' => '%' . $resena['producto'] . '%']);
                $producto = $stmtProd->fetch();
                
                if (!$producto) {
                    continue; // Saltar si no existe el producto
                }
                $id_producto = $producto['id'];

                // Verificar si ya existe esta valoración
                $stmtCheck = $pdo->prepare("
                    SELECT id FROM valoraciones 
                    WHERE id_usuario = :id_usuario AND id_producto = :id_producto
                ");
                $stmtCheck->execute([
                    ':id_usuario' => $id_usuario,
                    ':id_producto' => $id_producto
                ]);
                
                if (!$stmtCheck->fetch()) {
                    // Insertar valoración
                    $stmtVal = $pdo->prepare("
                        INSERT INTO valoraciones (id_usuario, id_producto, puntuacion, comentario)
                        VALUES (:id_usuario, :id_producto, :puntuacion, :comentario)
                    ");
                    $stmtVal->execute([
                        ':id_usuario' => $id_usuario,
                        ':id_producto' => $id_producto,
                        ':puntuacion' => $resena['puntuacion'],
                        ':comentario' => $resena['comentario']
                    ]);
                    $cargadas++;
                }
            } catch (Exception $e) {
                error_log("Error insertando reseña: " . $e->getMessage());
            }
        }

        $mensaje = "Se cargaron $cargadas reseñas de demostración correctamente.";
        $tipo_alerta = "success";
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_alerta = "error";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cargar Reseñas de Demostración</title>
<link rel="stylesheet" href="styles.css?v=20260217-1">
</head>
<body>
<div class="container">
    <div class="main-content">
        <h2 class="welcome">Cargar Reseñas de Demostración</h2>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_alerta ?> alert-spaced">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div style="background: #f5f5f5; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0;">
            <h3>¿Qué hace esto?</h3>
            <ul style="margin: 1rem 0; padding-left: 1.5rem;">
                <li>Crea usuarios de demostración con correos ficticios</li>
                <li>Crea 6 valoraciones (reseñas) de ejemplo con comentarios reales</li>
                <li>Cada reseña tiene 5 estrellas y está vinculada a un producto</li>
                <li>Estas reseñas aparecerán en el carrusel de la página principal</li>
            </ul>
        </div>

        <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; border-left: 4px solid #ffc107;">
            <strong>Nota:</strong> Estas reseñas son ficticias. Para ver reseñas reales de clientes, ellos deben valorar los productos en la sección "Valoraciones".
        </div>

        <form method="POST" style="margin: 2rem 0;">
            <button type="submit" name="cargar" class="btn-add-cart" style="font-size: 1.1rem; padding: 0.8rem 2rem;">
                Cargar 6 Reseñas de Demostración
            </button>
        </form>

        <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; border-left: 4px solid #2196F3;">
            <h3 style="color: #0c47a1;">Reseñas que se cargarán:</h3>
            <ol style="margin: 1rem 0; padding-left: 1.5rem;">
                <li><strong>María López</strong> - BBQ Hotdog: "El mejor hotdog que he probado..."</li>
                <li><strong>Carlos Mendez</strong> - Hotdog: "Impresionante la atención..."</li>
                <li><strong>Andrea Ruiz</strong> - Nachos: "Los nachos están increíbles..."</li>
                <li><strong>Juan García</strong> - Vegan Hotdog: "Muy buen precio para la calidad..."</li>
                <li><strong>Patricia Fernández</strong> - Hotdog: "Mi familia adoró este lugar..."</li>
                <li><strong>Roberto Silva</strong> - BBQ Hotdog: "Los detalles marcan la diferencia..."</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="trabajador.php" class="btn-secondary">Volver al Panel</a>
            <a href="usuario.php" class="btn-secondary">Ver Página Principal</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 Zyma. Todos los derechos reservados.</p>
</footer>

</body>
</html>
