<?php
/**
 * Copy to config.local.php on the server (or set env vars).
 * Never commit config.local.php.
 *
 * Env overrides (preferred on Softnet when available):
 *   RENTTI_ADMIN_USER
 *   RENTTI_ADMIN_PASSWORD   — plain password; hashed at runtime for verify
 *   RENTTI_SESSION_NAME
 */
return [
    'admin_user' => 'admin',
    // Generate with: php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
    'admin_pass_hash' => '',
    'session_name' => 'rentti_admin',
    'uf_clp' => 41391.69, // optional display conversion; update periodically
];
