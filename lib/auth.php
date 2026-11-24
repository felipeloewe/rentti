<?php
declare(strict_types=1);

function rentti_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $cfg = rentti_config();
    session_name((string) $cfg['session_name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

function rentti_is_logged_in(): bool
{
    rentti_session_start();
    return !empty($_SESSION['rentti_admin']);
}

function rentti_require_login(): void
{
    if (!rentti_is_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function rentti_login(string $user, string $password): bool
{
    $cfg = rentti_config();
    $expectedUser = (string) $cfg['admin_user'];
    if (!hash_equals($expectedUser, $user)) {
        return false;
    }

    $ok = false;
    $hash = (string) ($cfg['admin_pass_hash'] ?? '');
    if ($hash !== '' && password_verify($password, $hash)) {
        $ok = true;
    }

    $plain = (string) ($cfg['admin_password_plain'] ?? '');
    if (!$ok && $plain !== '' && hash_equals($plain, $password)) {
        $ok = true;
    }

    if (!$ok) {
        return false;
    }

    rentti_session_start();
    session_regenerate_id(true);
    $_SESSION['rentti_admin'] = $expectedUser;
    return true;
}

function rentti_logout(): void
{
    rentti_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

function rentti_auth_configured(): bool
{
    $cfg = rentti_config();
    return ((string) ($cfg['admin_pass_hash'] ?? '') !== '')
        || ((string) ($cfg['admin_password_plain'] ?? '') !== '');
}
