<?php
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

function zymaEnsureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function zymaRoleFromWorkerCode(?string $workerCode): string
{
    $workerCode = trim((string) $workerCode);
    if ($workerCode === 'ADMIN') {
        return 'admin';
    }
    if ($workerCode !== '') {
        return 'worker';
    }
    return 'client';
}

function zymaCurrentRole(): string
{
    zymaEnsureSessionStarted();

    if (empty($_SESSION['user_id'])) {
        return 'guest';
    }

    $role = $_SESSION['user_role'] ?? '';
    if (in_array($role, ['client', 'worker', 'admin'], true)) {
        return $role;
    }

    return 'client';
}

function zymaIsLoggedIn(): bool
{
    return zymaCurrentRole() !== 'guest';
}

function zymaHomeForRole(?string $role = null): string
{
    $role = $role ?? zymaCurrentRole();

    if ($role === 'admin') {
        return 'admin.php';
    }
    if ($role === 'worker') {
        return 'trabajador.php';
    }
    if ($role === 'client') {
        return 'usuario.php';
    }

    return 'login.php';
}

function zymaSetAuthenticatedUser(array $user, string $role): void
{
    zymaEnsureSessionStarted();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nombre'] = $user['nombre'] ?? '';
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['worker_code'] = $user['worker_code'] ?? null;
    $_SESSION['user_role'] = $role;
}

function zymaClearAuthenticatedUser(): void
{
    zymaEnsureSessionStarted();
    unset(
        $_SESSION['user_id'],
        $_SESSION['email'],
        $_SESSION['worker_code'],
        $_SESSION['nombre'],
        $_SESSION['user_role']
    );
}

function zymaRedirect(string $target): void
{
    header('Location: ' . $target);
    exit;
}

function zymaRedirectToHomeForCurrentRole(): void
{
    zymaRedirect(zymaHomeForRole());
}

function zymaRequireLogin(): void
{
    if (!zymaIsLoggedIn()) {
        zymaRedirect('login.php');
    }
}

function zymaRequireRole(string $role): void
{
    zymaRequireLogin();

    if (zymaCurrentRole() !== $role) {
        zymaRedirectToHomeForCurrentRole();
    }
}

function zymaRequireAnyRole(array $roles): void
{
    zymaRequireLogin();

    if (!in_array(zymaCurrentRole(), $roles, true)) {
        zymaRedirectToHomeForCurrentRole();
    }
}
