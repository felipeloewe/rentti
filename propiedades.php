<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/bootstrap.php';

$filters = [
    'commune' => trim((string) ($_GET['comuna'] ?? '')),
    'property_type' => trim((string) ($_GET['tipo'] ?? '')),
    'price_min' => trim((string) ($_GET['precio_min'] ?? '')),
    'price_max' => trim((string) ($_GET['precio_max'] ?? '')),
];
$items = rentti_public_listings($filters);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Arriendos publicados — RENTTI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet" />
  <?php rentti_brand_head_tags(); ?>
  <link rel="stylesheet" href="/css/tokens.css" />
  <link rel="stylesheet" href="/css/base.css" />
  <link rel="stylesheet" href="/css/layout.css" />
  <link rel="stylesheet" href="/css/public-listings.css" />
</head>
<body>
  <header class="site-header site-header--light">
    <a class="site-header__brand" href="/"><?= rentti_brand_logo('coral', 'horizontal') ?></a>
    <nav class="site-header__nav" aria-label="Principal">
      <a href="/#buscar">Buscar</a>
      <a href="/propiedades.php" aria-current="page">Arriendos</a>
      <a href="/#como">Cómo funciona</a>
    </nav>
    <a class="btn site-header__cta" href="/#publicar">Publicar</a>
  </header>

  <main class="listings-page">
    <div class="listings-page__intro">
      <h1>Arriendos publicados</h1>
      <p><?= count($items) ?> propiedad<?= count($items) === 1 ? '' : 'es' ?> disponible<?= count($items) === 1 ? '' : 's' ?> en RENTTI.</p>
    </div>

    <form class="listings-filters" method="get">
      <label>
        <span>Comuna</span>
        <input type="search" name="comuna" value="<?= h($filters['commune']) ?>" placeholder="Providencia…" />
      </label>
      <label>
        <span>Tipo</span>
        <select name="tipo">
          <option value="">Todos</option>
          <?php foreach (rentti_property_types() as $k => $label): ?>
            <option value="<?= h($k) ?>" <?= $filters['property_type'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn--primary" type="submit">Filtrar</button>
    </form>

    <?php if (!$items): ?>
      <p class="listings-empty">Aún no hay arriendos publicados. Vuelve pronto.</p>
    <?php else: ?>
      <div class="listings-grid">
        <?php foreach ($items as $item): ?>
          <a class="listing-card" href="/propiedad.php?code=<?= h(urlencode($item['code'])) ?>">
            <div class="listing-card__media">
              <?php if (!empty($item['cover_filename'])): ?>
                <img src="<?= h(rentti_photo_url($item['cover_filename'])) ?>" alt="" loading="lazy" />
              <?php else: ?>
                <div class="listing-card__placeholder" aria-hidden="true"></div>
              <?php endif; ?>
            </div>
            <div class="listing-card__body">
              <p class="listing-card__meta"><?= h(rentti_type_label($item['property_type'])) ?> · <?= h($item['commune']) ?></p>
              <h2><?= h($item['title'] ?: $item['referential_address']) ?></h2>
              <p class="listing-card__addr"><?= h($item['referential_address']) ?></p>
              <p class="listing-card__price"><?= h(rentti_price_both($item['rent_amount'] !== null ? (float) $item['rent_amount'] : null, $item['rent_currency'])) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
