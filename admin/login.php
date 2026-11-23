<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';

rentti_session_start();
if (rentti_is_logged_in()) {
    rentti_redirect('/admin/');
}

$error = '';
if (rentti_request_is_post()) {
    if (!rentti_verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'Sesión inválida. Recarga e intenta de nuevo.';
    } elseif (!rentti_auth_configured()) {
        $error = 'Credenciales no configuradas. Crea config/config.local.php o define RENTTI_ADMIN_PASSWORD.';
    } else {
        $user = trim((string) ($_POST['user'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        if (rentti_login($user, $pass)) {
            rentti_redirect('/admin/');
        }
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ingresar — RENTTI Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet" />
  <?php rentti_brand_head_tags(); ?>
  <link rel="stylesheet" href="/css/tokens.css" />
  <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="login-page">
  <div class="login-card">
    <p class="hint" style="margin:0">Backoffice interno</p>
    <h1 class="login-brand"><?= rentti_brand_logo('navy', 'stacked', 'brand-logo brand-logo--login') ?></h1>
    <p class="lead">Publica y administra arriendos del sitio.</p>
    <?php if (!rentti_auth_configured()): ?>
      <div class="warn">Falta configurar la contraseña de admin (config.local.php o variable de entorno).</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="flash flash--error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post" class="stack" autocomplete="on">
      <input type="hidden" name="csrf" value="<?= h(rentti_csrf_token()) ?>" />
      <div class="field">
        <label for="user">Usuario</label>
        <input id="user" name="user" type="text" required autocomplete="username" value="<?= h($_POST['user'] ?? 'admin') ?>" />
      </div>
      <div class="field">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" />
      </div>
      <button class="btn btn--primary" type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
