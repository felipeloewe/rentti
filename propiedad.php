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
  <header class="site-header site-header--light">
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
        <?php
          $hasProject = rentti_property_has_project_summary($property);
          $tipologias = rentti_decode_tipologias($property['tipologias_json'] ?? '[]');
          $displayName = ($property['project_name'] ?? '') !== ''
            ? (string) $property['project_name']
            : ($property['title'] ?: $property['referential_address']);
        ?>

        <?php if (($property['delivery_label'] ?? '') !== ''): ?>
          <p class="detail-delivery">Entrega <?= h((string) $property['delivery_label']) ?></p>
        <?php endif; ?>

        <p class="listing-card__meta"><?= h(rentti_type_label($property['property_type'])) ?> · <?= h($property['commune']) ?>, <?= h($property['region']) ?></p>
        <h1><?= h($displayName) ?></h1>
        <p class="detail-addr"><?= h($property['referential_address']) ?><?php if ($property['commune']): ?>, <?= h($property['commune']) ?><?php endif; ?></p>

        <?php if ($property['price_from_uf'] !== null && $property['price_from_uf'] !== ''): ?>
          <div class="detail-from-price">
            <p class="detail-price">
              Proyecto desde <?= h(rentti_format_price((float) $property['price_from_uf'], 'UF')) ?>
              <?php if (($property['promo_badge'] ?? '') !== ''): ?>
                <span class="promo-badge"><?= h((string) $property['promo_badge']) ?></span>
              <?php endif; ?>
            </p>
            <?php
              $uf = (float) rentti_config()['uf_clp'];
              if ($uf > 0):
            ?>
              <p class="detail-gc"><?= h(rentti_format_price((float) $property['price_from_uf'] * $uf, 'CLP')) ?></p>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <p class="detail-price"><?= h(rentti_price_both($property['rent_amount'] !== null ? (float) $property['rent_amount'] : null, $property['rent_currency'])) ?></p>
          <?php if ($property['common_expenses']): ?>
            <p class="detail-gc">Gastos comunes: <?= h(rentti_format_price((float) $property['common_expenses'], 'CLP')) ?></p>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (($property['promo_line_1'] ?? '') !== '' || ($property['promo_line_2'] ?? '') !== ''): ?>
          <div class="promo-lines">
            <?php if (($property['promo_line_1'] ?? '') !== ''): ?>
              <p class="promo-lines__accent"><?= h((string) $property['promo_line_1']) ?></p>
            <?php endif; ?>
            <?php if (($property['promo_line_2'] ?? '') !== ''): ?>
              <p><?= h((string) $property['promo_line_2']) ?></p>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php
          $dormRange = rentti_range_label($property['summary_dorms_min'] ?? null, $property['summary_dorms_max'] ?? null, ' dorm.');
          $bathRange = rentti_range_label($property['summary_baths_min'] ?? null, $property['summary_baths_max'] ?? null, ' baños');
          $m2Range = rentti_range_label($property['summary_m2_min'] ?? null, $property['summary_m2_max'] ?? null, ' m² útiles');
        ?>
        <?php if ($dormRange || $bathRange || $m2Range): ?>
          <ul class="project-summary-icons" aria-label="Resumen del proyecto">
            <?php if ($dormRange): ?>
              <li><?= rentti_project_icon_svg('bed') ?><span><?= h($dormRange) ?></span></li>
            <?php endif; ?>
            <?php if ($bathRange): ?>
              <li><?= rentti_project_icon_svg('bath') ?><span><?= h($bathRange) ?></span></li>
            <?php endif; ?>
            <?php if ($m2Range): ?>
              <li><?= rentti_project_icon_svg('area') ?><span><?= h($m2Range) ?></span></li>
            <?php endif; ?>
          </ul>
        <?php else: ?>
          <ul class="detail-facts">
            <?php if ($property['bedrooms'] !== null): ?><li><?= (int) $property['bedrooms'] ?> dorm.</li><?php endif; ?>
            <?php if ($property['bathrooms'] !== null): ?><li><?= (int) $property['bathrooms'] ?> baños</li><?php endif; ?>
            <?php if ($property['useful_area'] !== null): ?><li><?= h((string) $property['useful_area']) ?> m² útiles</li><?php endif; ?>
            <?php if ($property['built_area'] !== null): ?><li><?= h((string) $property['built_area']) ?> m² construidos</li><?php endif; ?>
          </ul>
        <?php endif; ?>

        <?php if ($tipologias): ?>
          <section class="detail-section tipologias">
            <h2>Modelos con unidades</h2>
            <div class="tipologia-grid">
              <?php foreach ($tipologias as $tip): ?>
                <article class="tipologia-card">
                  <?php if (($tip['image_url'] ?? '') !== ''): ?>
                    <div class="tipologia-card__media">
                      <img src="<?= h((string) $tip['image_url']) ?>" alt="<?= h(($tip['name'] ?? '') !== '' ? (string) $tip['name'] : 'Plano') ?>" loading="lazy" />
                    </div>
                  <?php else: ?>
                    <div class="tipologia-card__media tipologia-card__media--empty" aria-hidden="true"></div>
                  <?php endif; ?>
                  <ul class="tipologia-card__stats">
                    <?php
                      $m2 = rentti_range_label($tip['m2_min'] ?? null, $tip['m2_max'] ?? null, ' m² totales');
                      $dormLabel = $tip['dorms'] !== null ? ((int) $tip['dorms'] === 1 ? '1 dormitorio' : ((int) $tip['dorms'] . ' dormitorios')) : '';
                      $bathLabel = $tip['baths'] !== null ? ((int) $tip['baths'] === 1 ? '1 baño' : ((int) $tip['baths'] . ' baños')) : '';
                    ?>
                    <?php if ($m2 !== ''): ?>
                      <li><?= rentti_project_icon_svg('area') ?><span><?= h($m2) ?></span></li>
                    <?php endif; ?>
                    <?php if ($dormLabel !== ''): ?>
                      <li><?= rentti_project_icon_svg('bed') ?><span><?= h($dormLabel) ?></span></li>
                    <?php endif; ?>
                    <?php if ($bathLabel !== ''): ?>
                      <li><?= rentti_project_icon_svg('bath') ?><span><?= h($bathLabel) ?></span></li>
                    <?php endif; ?>
                  </ul>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php
          $unitFeatures = rentti_active_unit_features($property);
          $commonAmenities = rentti_active_common_amenities($property);
          $extraFlags = [];
          foreach (rentti_extra_flags() as $ek => $emeta) {
              if (!empty($property[$ek])) {
                  $extraFlags[$ek] = $emeta;
              }
          }
          $hasParking = $property['parking_price_uf'] !== null && $property['parking_price_uf'] !== ''
              || ($property['parking_note'] ?? '') !== ''
              || ($property['parking_covered'] !== null && (int) $property['parking_covered'] > 0)
              || ($property['parking_open'] !== null && (int) $property['parking_open'] > 0);
          $hasStorage = !empty($property['storage'])
              || ($property['storage_price_uf'] !== null && $property['storage_price_uf'] !== '');
        ?>

        <?php if ($unitFeatures): ?>
          <section class="detail-section detail-amenities">
            <h2>Características</h2>
            <ul class="feature-check-list">
              <?php foreach ($unitFeatures as $feat): ?>
                <li>
                  <span class="feature-check" aria-hidden="true"><?= rentti_amenity_icon_svg('check', 'feature-check__svg') ?></span>
                  <span class="feature-check-list__text">
                    <strong><?= h($feat['label']) ?></strong>
                    <?php if ($feat['hint'] !== ''): ?>
                      <span class="feature-check-list__hint"><?= h($feat['hint']) ?></span>
                    <?php endif; ?>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <?php if ($hasParking): ?>
          <section class="detail-section detail-amenities">
            <h2>Estacionamiento</h2>
            <ul class="feature-check-list">
              <li>
                <span class="feature-check" aria-hidden="true"><?= rentti_amenity_icon_svg('parking', 'feature-check__svg') ?></span>
                <span class="feature-check-list__text">
                  <?php if ($property['parking_price_uf'] !== null && $property['parking_price_uf'] !== ''): ?>
                    <strong><?= h(rentti_format_price((float) $property['parking_price_uf'], 'UF')) ?></strong>
                  <?php else: ?>
                    <strong>Disponible</strong>
                  <?php endif; ?>
                  <?php if (($property['parking_note'] ?? '') !== ''): ?>
                    <span class="feature-check-list__hint"><?= h((string) $property['parking_note']) ?></span>
                  <?php endif; ?>
                </span>
              </li>
            </ul>
          </section>
        <?php endif; ?>

        <?php if ($hasStorage): ?>
          <section class="detail-section detail-amenities">
            <h2>Bodega</h2>
            <ul class="feature-check-list">
              <li>
                <span class="feature-check" aria-hidden="true"><?= rentti_amenity_icon_svg('storage', 'feature-check__svg') ?></span>
                <span class="feature-check-list__text">
                  <?php if ($property['storage_price_uf'] !== null && $property['storage_price_uf'] !== ''): ?>
                    <strong><?= h(rentti_format_price((float) $property['storage_price_uf'], 'UF')) ?></strong>
                  <?php else: ?>
                    <strong>Incluida</strong>
                  <?php endif; ?>
                </span>
              </li>
            </ul>
          </section>
        <?php endif; ?>

        <?php if ($commonAmenities): ?>
          <section class="detail-section detail-amenities">
            <h2>Áreas comunes</h2>
            <ul class="amenity-icon-grid">
              <?php foreach ($commonAmenities as $amenity): ?>
                <li>
                  <?= rentti_amenity_icon_svg($amenity['icon']) ?>
                  <span><?= h($amenity['label']) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <?php if ($extraFlags): ?>
          <section class="detail-section detail-amenities">
            <h2>Otros</h2>
            <ul class="amenity-icon-grid amenity-icon-grid--compact">
              <?php foreach ($extraFlags as $flag): ?>
                <li>
                  <?= rentti_amenity_icon_svg($flag['icon']) ?>
                  <span><?= h($flag['label']) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <?php if ($property['description']): ?>
          <section class="detail-section">
            <h2>Descripción</h2>
            <p><?= nl2br(h($property['description'])) ?></p>
          </section>
        <?php endif; ?>

        <?php
          $nearbyPoints = rentti_list_nearby((int) $property['id']);
          $nearbyGrouped = rentti_group_nearby($nearbyPoints);
          $hasNearby = count($nearbyPoints) > 0;
        ?>
        <?php if ($hasNearby): ?>
          <section class="detail-section zone-info" data-zone-info>
            <h2 class="zone-info__title">
              <?= rentti_zone_icon_svg('pin', 'zone-info__pin') ?>
              Información de la zona
            </h2>
            <div class="zone-tabs" role="tablist" aria-label="Información de la zona">
              <?php $firstTab = true; foreach (rentti_zone_tabs() as $tabKey => $tabMeta):
                $count = count($nearbyGrouped[$tabKey] ?? []);
                if ($count === 0) {
                    continue;
                }
              ?>
                <button
                  type="button"
                  class="zone-tabs__btn<?= $firstTab ? ' is-active' : '' ?>"
                  role="tab"
                  aria-selected="<?= $firstTab ? 'true' : 'false' ?>"
                  data-zone-tab="<?= h($tabKey) ?>"
                >
                  <?= rentti_zone_icon_svg($tabMeta['icon'], 'zone-tabs__icon') ?>
                  <span><?= h($tabMeta['label']) ?></span>
                </button>
              <?php $firstTab = false; endforeach; ?>
            </div>

            <?php $firstPanel = true; foreach (rentti_zone_tabs() as $tabKey => $tabMeta):
              $items = $nearbyGrouped[$tabKey] ?? [];
              if (!$items) {
                  continue;
              }
            ?>
              <div
                class="zone-panel<?= $firstPanel ? ' is-active' : '' ?>"
                role="tabpanel"
                data-zone-panel="<?= h($tabKey) ?>"
                <?= $firstPanel ? '' : 'hidden' ?>
              >
                <?php
                  $subLabels = rentti_zone_subtypes_by_tab()[$tabKey] ?? [];
                  $useColumns = $subLabels !== [];
                ?>
                <?php if ($useColumns):
                  $cols = rentti_group_by_subtype($items, $subLabels);
                  $colCount = count(array_filter($cols));
                ?>
                  <div class="zone-transit-grid<?= $tabKey === 'salud' ? ' zone-transit-grid--2' : '' ?>" style="<?= $tabKey === 'salud' ? '' : '' ?>">
                    <?php foreach ($subLabels as $subKey => $subLabel):
                      $colItems = $cols[$subKey] ?? [];
                      if (!$colItems) {
                          continue;
                      }
                    ?>
                      <div class="zone-transit-col">
                        <h3><?= h($subLabel) ?></h3>
                        <ul class="zone-place-list">
                          <?php foreach ($colItems as $place): ?>
                            <li class="zone-place">
                              <p class="zone-place__name"><?= h((string) $place['name']) ?></p>
                              <?php if ($tabKey === 'salud' || $tabKey === 'educacion' || $tabKey === 'verdes' || $tabKey === 'comercios'): ?>
                                <?php
                                  $walkLabel = rentti_zone_walk_distance_label(
                                      $place['walk_min'] !== null && $place['walk_min'] !== '' ? (int) $place['walk_min'] : null,
                                      $place['distance_m'] !== null && $place['distance_m'] !== '' ? (int) $place['distance_m'] : null
                                  );
                                ?>
                                <?php if ($walkLabel !== ''): ?>
                                  <p class="zone-place__meta"><?= rentti_zone_icon_svg('walk', 'zone-pill__icon') ?><?= h($walkLabel) ?></p>
                                <?php endif; ?>
                              <?php else: ?>
                                <div class="zone-pills">
                                  <?php if ($place['distance_m'] !== null && $place['distance_m'] !== ''): ?>
                                    <span class="zone-pill"><?= rentti_zone_icon_svg('pin', 'zone-pill__icon') ?><?= (int) $place['distance_m'] ?> mt.</span>
                                  <?php endif; ?>
                                  <?php if ($place['walk_min'] !== null && $place['walk_min'] !== ''): ?>
                                    <span class="zone-pill"><?= rentti_zone_icon_svg('walk', 'zone-pill__icon') ?><?= (int) $place['walk_min'] ?> min.</span>
                                  <?php endif; ?>
                                  <?php if ($place['drive_min'] !== null && $place['drive_min'] !== ''): ?>
                                    <span class="zone-pill"><?= rentti_zone_icon_svg('car', 'zone-pill__icon') ?><?= (int) $place['drive_min'] ?> min.</span>
                                  <?php endif; ?>
                                </div>
                              <?php endif; ?>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <ul class="zone-place-list zone-place-list--grid">
                    <?php foreach ($items as $place): ?>
                      <li class="zone-place">
                        <p class="zone-place__name"><?= h((string) $place['name']) ?></p>
                        <?php
                          $walkLabel = rentti_zone_walk_distance_label(
                              $place['walk_min'] !== null && $place['walk_min'] !== '' ? (int) $place['walk_min'] : null,
                              $place['distance_m'] !== null && $place['distance_m'] !== '' ? (int) $place['distance_m'] : null
                          );
                        ?>
                        <?php if ($walkLabel !== ''): ?>
                          <p class="zone-place__meta"><?= rentti_zone_icon_svg('walk', 'zone-pill__icon') ?><?= h($walkLabel) ?></p>
                        <?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            <?php $firstPanel = false; endforeach; ?>
          </section>
          <script>
            (function () {
              const root = document.querySelector('[data-zone-info]');
              if (!root) return;
              const tabs = [...root.querySelectorAll('[data-zone-tab]')];
              const panels = [...root.querySelectorAll('[data-zone-panel]')];
              tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                  const key = btn.getAttribute('data-zone-tab');
                  tabs.forEach(t => {
                    const on = t === btn;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                  });
                  panels.forEach(p => {
                    const on = p.getAttribute('data-zone-panel') === key;
                    p.classList.toggle('is-active', on);
                    p.hidden = !on;
                  });
                });
              });
            })();
          </script>
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
