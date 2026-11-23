<?php
declare(strict_types=1);

function admin_layout_start(string $title, string $active = 'properties'): void
{
    $flash = rentti_take_flash();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($title) ?> — RENTTI Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet" />
  <?php rentti_brand_head_tags(); ?>
  <link rel="stylesheet" href="/css/tokens.css" />
  <link rel="stylesheet" href="/admin/admin.css" />
</head>
<body class="admin">
  <header class="admin-top">
    <a class="admin-brand" href="/admin/"><?= rentti_brand_logo('coral', 'horizontal', 'brand-logo brand-logo--admin') ?></a>
    <nav class="admin-nav" aria-label="Admin">
      <a class="<?= $active === 'properties' ? 'is-active' : '' ?>" href="/admin/">Propiedades</a>
      <a href="/propiedades.php" target="_blank" rel="noopener">Ver sitio</a>
    </nav>
    <form method="post" action="/admin/logout.php" class="admin-logout">
      <input type="hidden" name="csrf" value="<?= h(rentti_csrf_token()) ?>" />
      <button type="submit" class="btn btn--ghost">Salir</button>
    </form>
  </header>
  <main class="admin-main">
    <?php if ($flash): ?>
      <div class="flash flash--<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div>
    <?php endif; ?>
<?php
}

function admin_layout_end(): void
{
    ?>
  </main>
</body>
</html>
<?php
}
