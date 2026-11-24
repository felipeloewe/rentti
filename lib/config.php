<?php
declare(strict_types=1);

function rentti_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $defaults = [
        'admin_user' => 'admin',
        'admin_pass_hash' => '',
        'admin_password_plain' => '',
        'session_name' => 'rentti_admin',
        'uf_clp' => 41391.69,
    ];

    $localPath = RENTTI_ROOT . '/config/config.local.php';
    $local = is_file($localPath) ? (require $localPath) : [];
    if (!is_array($local)) {
        $local = [];
    }

    $cfg = array_merge($defaults, $local);

    $envUser = getenv('RENTTI_ADMIN_USER');
    if ($envUser !== false && $envUser !== '') {
        $cfg['admin_user'] = $envUser;
    }
    $envPass = getenv('RENTTI_ADMIN_PASSWORD');
    if ($envPass !== false && $envPass !== '') {
        $cfg['admin_password_plain'] = $envPass;
    }
    $envSession = getenv('RENTTI_SESSION_NAME');
    if ($envSession !== false && $envSession !== '') {
        $cfg['session_name'] = $envSession;
    }

    return $cfg;
}

function rentti_ensure_storage(): void
{
    foreach ([RENTTI_ROOT . '/data', RENTTI_ROOT . '/uploads/properties'] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
