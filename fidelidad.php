<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Obtener o crear registro de fidelidad
$stmt = $pdo->prepare("SELECT * FROM loyalt_users WHERE id_usuario = ?");
$stmt->execute([$userId]);
$loyalty = $stmt->fetch();

if (!$loyalty) {
    $stmt = $pdo->prepare("INSERT INTO loyalt_users (id_usuario, puntos, nivel) VALUES (?, 0, 'bronce')");
    $stmt->execute([$userId]);
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare("SELECT * FROM loyalt_users WHERE id_usuario = ?");
    $stmt->execute([$userId]);
    $loyalty = $stmt->fetch();
}

// Obtener historial de transacciones
$stmt = $pdo->prepare("SELECT * FROM loyalt_transactions WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 50");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll();

// Obtener recompensas disponibles
$stmt = $pdo->query("SELECT * FROM loyalt_rewards WHERE activo = 1 ORDER BY puntos_necesarios ASC");
$rewards = $stmt->fetchAll();

// Calcular siguiente nivel
$levels = [
    'bronce' => ['next' => 'silver', 'points_needed' => 200],
    'silver' => ['next' => 'gold', 'points_needed' => 500],
    'gold' => ['next' => null, 'points_needed' => null]
];

$currentLevel = $loyalty['nivel'];
$nextLevelInfo = $levels[$currentLevel];
$pointsForNextLevel = $nextLevelInfo['points_needed'] - $loyalty['puntos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="loyalty.title">Fidelidad - Zyma</title>
    <link rel="stylesheet" href="assets/estilos.css?v=20260428">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="nav-bar">
        <a href="index.php" class="nav-logo">Zyma</a>
        <div class="nav-links">
            <a href="usuario.php" data-i18n="nav.myProfile">Mi perfil</a>
            <a href="carta.php" data-i18n="nav.viewMenu">Ver carta</a>
            <a href="tickets.php" data-i18n="nav.tickets">Tickets</a>
            <a href="fidelidad.php" data-i18n="loyalty.title">Fidelidad</a>
            <a href="logout.php" data-i18n="nav.logout">Cerrar Sesión</a>
        </div>
    </nav>

    <main class="main-container">
        <section class="welcome-section">
            <p class="welcome-kicker" data-i18n="loyalty.title">Programa de Fidelidad</p>
            <h1 class="section-title" data-i18n="loyalty.subtitle">Gana puntos con cada pedido y canjéalos por recompensas.</h1>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?= $loyalty['puntos'] ?></span>
                <span class="stat-label" data-i18n="loyalty.points">Puntos</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= ucfirst($loyalty['nivel']) ?></span>
                <span class="stat-label" data-i18n="loyalty.level">Nivel</span>
            </div>
            <?php if ($nextLevelInfo['next']): ?>
            <div class="stat-card">
                <span class="stat-number"><?= $pointsForNextLevel ?></span>
                <span class="stat-label" data-i18n="loyalty.nextReward">Siguiente recompensa en:</span>
            </div>
            <?php endif; ?>
        </section>

        <section class="card-section">
            <h2 class="section-title" data-i18n="loyalty.claimReward">Canjear recompensa</h2>
            <div class="menu-grid">
                <?php foreach ($rewards as $reward): ?>
                <div class="menu-card">
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($reward['nombre']) ?></h3>
                        <p class="card-desc"><?= htmlspecialchars($reward['descripcion']) ?></p>
                        <p class="card-price"><?= $reward['puntos_necesarios'] ?> <span data-i18n="loyalty.points">puntos</span></p>
                        <?php if ($loyalty['puntos'] >= $reward['puntos_necesarios']): ?>
                        <form method="POST" action="procesar_recompensa.php">
                            <input type="hidden" name="reward_id" value="<?= $reward['id'] ?>">
                            <button type="submit" class="btn-cart" data-i18n="loyalty.claimReward">Canjear</button>
                        </form>
                        <?php else: ?>
                        <p class="text-muted">Necesitas <?= $reward['puntos_necesarios'] - $loyalty['puntos'] ?> más puntos</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card-section">
            <h2 class="section-title" data-i18n="loyalty.history">Historial de puntos</h2>
            <?php if (empty($transactions)): ?>
                <p class="empty-state" data-i18n="loyalty.noHistory">Aún no tienes historial de puntos.</p>
            <?php else: ?>
                <table class="admin-users-table">
                    <thead>
                        <tr>
                            <th data-i18n="loyalty.date">Fecha</th>
                            <th data-i18n="loyalty.concept">Concepto</th>
                            <th>Puntos</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($tx['fecha'])) ?></td>
                            <td><?= htmlspecialchars($tx['concepto']) ?></td>
                            <td class="<?= $tx['tipo'] === 'ganado' ? 'text-success' : 'text-danger' ?>">
                                <?= $tx['tipo'] === 'ganado' ? '+' : '' ?><?= $tx['puntos'] ?>
                            </td>
                            <td><?= $tx['monto'] ? '€' . number_format($tx['monto'], 2) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p data-i18n="footer.rights">&copy; 2025/2026 Zyma. Todos los derechos reservados.</p>
        <a href="politica_cookies.php" data-i18n="footer.cookiePolicy">Política de Cookies</a>
        <a href="politica_privacidad.php" data-i18n="footer.privacy">Política de Privacidad</a>
        <a href="aviso_legal.php" data-i18n="footer.legal">Aviso Legal</a>
    </footer>

    <?php require_once 'language_selector.php'; ?>
</body>
</html>
