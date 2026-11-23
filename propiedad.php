<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/bootstrap.php';

$code = trim((string) ($_GET['code'] ?? ''));
$property = $code !== '' ? rentti_get_property_by_code($code) : null;

if (!$property || $property['status'] !== 'publicada') {
    http_response_code(404);
    $title = 'Propiedad no disponible';
    $property = null;
} else {
    $title = $property['title'] ?: ($property['referential_address'] . ' — RENTTI');
}

$photos = $property ? rentti_list_photos((int) $property['id']) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/css/tokens.css" />
  <link rel="stylesheet" href="/css/base.css" />
  <link rel="stylesheet" href="/css/layout.css" />
  <link rel="stylesheet" href="/css/public-listings.css" />
</head>
<body>
  <header class="site-header">
    <a class="site-header__brand" href="/">RENTTI</a>
    <nav class="site-header__nav" aria-label="Principal">
      <a href="/propiedades.php">Arriendos</a>
      <a href="/#buscar">Buscar</a>
    </nav>
    <a class="btn site-header__cta" href="/#publicar">Publicar</a>
  </header>

  <main class="detail-page">
    <?php if (!$property): ?>
      <div class="listings-page__intro">
        <h1>No encontrada</h1>
        <p>Esta propiedad no está publicada o el enlace es inválido.</p>
        <p><a href="/propiedades.php">Ver arriendos</a></p>
      </div>
    <?php else: ?>
      <div class="detail-gallery">
        <?php if ($photos): ?>
          <?php foreach ($photos as $i => $photo): ?>
            <img
              class="<?= $i === 0 ? 'is-hero' : '' ?>"
              src="<?= h(rentti_photo_url($photo['filename'])) ?>"
              alt="<?= $i === 0 ? h($property['title'] ?: 'Foto principal') : '' ?>"
              <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
            />
          <?php endforeach; ?>
        <?php else: ?>
          <div class="listing-card__placeholder detail-gallery__empty" aria-hidden="true"></div>
        <?php endif; ?>
      </div>

      <div class="detail-body">
        <p class="listing-card__meta"><?= h(rentti_type_label($property['property_type'])) ?> en arriendo · <?= h($property['commune']) ?>, <?= h($property['region']) ?></p>
        <h1><?= h($property['title'] ?: $property['referential_address']) ?></h1>
        <p class="detail-addr"><?= h($property['referential_address']) ?></p>
        <p class="detail-price"><?= h(rentti_price_both($property['rent_amount'] !== null ? (float) $property['rent_amount'] : null, $property['rent_currency'])) ?></p>
        <?php if ($property['common_expenses']): ?>
          <p class="detail-gc">Gastos comunes: <?= h(rentti_format_price((float) $property['common_expenses'], 'CLP')) ?></p>
        <?php endif; ?>

        <ul class="detail-facts">
          <?php if ($property['bedrooms'] !== null): ?><li><?= (int) $property['bedrooms'] ?> dorm.</li><?php endif; ?>
          <?php if ($property['bathrooms'] !== null): ?><li><?= (int) $property['bathrooms'] ?> baños</li><?php endif; ?>
          <?php if ($property['useful_area'] !== null): ?><li><?= h((string) $property['useful_area']) ?> m² útiles</li><?php endif; ?>
          <?php if ($property['built_area'] !== null): ?><li><?= h((string) $property['built_area']) ?> m² construidos</li><?php endif; ?>
          <?php if (!empty($property['furnished'])): ?><li>Amoblado</li><?php endif; ?>
          <?php if (!empty($property['pets_allowed'])): ?><li>Mascotas OK</li><?php endif; ?>
          <?php if (!empty($property['pool'])): ?><li>Piscina</li><?php endif; ?>
          <?php if (!empty($property['condominium'])): ?><li>Condominio</li><?php endif; ?>
          <?php if (!empty($property['storage'])): ?><li>Bodega</li><?php endif; ?>
        </ul>

        <?php if ($property['description']): ?>
          <section class="detail-section">
            <h2>Descripción</h2>
            <p><?= nl2br(h($property['description'])) ?></p>
          </section>
        <?php endif; ?>

        <?php if (!empty($property['show_map']) && $property['lat'] !== null && $property['lng'] !== null): ?>
          <section class="detail-section">
            <h2>Ubicación</h2>
            <p>
              <a href="https://www.openstreetmap.org/?mlat=<?= h((string) $property['lat']) ?>&mlon=<?= h((string) $property['lng']) ?>#map=16/<?= h((string) $property['lat']) ?>/<?= h((string) $property['lng']) ?>" target="_blank" rel="noopener">
                Ver en mapa (<?= h((string) $property['lat']) ?>, <?= h((string) $property['lng']) ?>)
              </a>
            </p>
          </section>
        <?php endif; ?>

        <p class="detail-code">Código <?= h($property['code']) ?></p>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
