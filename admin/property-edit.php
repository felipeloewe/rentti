<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/layout.php';

rentti_require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$property = $id > 0 ? rentti_get_property($id) : null;
if (!$property) {
    rentti_flash('error', 'Propiedad no encontrada.');
    rentti_redirect('/admin/');
}

$step = max(1, min(6, (int) ($_GET['step'] ?? $_POST['step'] ?? 1)));
$errors = [];

if (rentti_request_is_post()) {
    if (!rentti_verify_csrf($_POST['csrf'] ?? null)) {
        $errors[] = 'Token CSRF inválido.';
    } else {
        $action = (string) ($_POST['form_action'] ?? 'save');

        if ($action === 'delete_property') {
            rentti_delete_property($id);
            rentti_flash('ok', 'Propiedad eliminada.');
            rentti_redirect('/admin/');
        }

        if ($action === 'upload_photo' && !empty($_FILES['photo']['tmp_name'])) {
            try {
                rentti_add_photo($id, (string) $_FILES['photo']['tmp_name'], (string) ($_FILES['photo']['name'] ?? 'photo'));
                rentti_flash('ok', 'Foto subida.');
            } catch (Throwable $e) {
                rentti_flash('error', $e->getMessage());
            }
            rentti_redirect('/admin/property-edit.php?id=' . $id . '&step=6');
        }

        if ($action === 'set_cover') {
            rentti_set_cover($id, (int) ($_POST['photo_id'] ?? 0));
            rentti_flash('ok', 'Portada actualizada.');
            rentti_redirect('/admin/property-edit.php?id=' . $id . '&step=6');
        }

        if ($action === 'delete_photo') {
            rentti_delete_photo($id, (int) ($_POST['photo_id'] ?? 0));
            rentti_flash('ok', 'Foto eliminada.');
            rentti_redirect('/admin/property-edit.php?id=' . $id . '&step=6');
        }

        if ($action === 'reorder_photos') {
            $order = array_filter(array_map('intval', explode(',', (string) ($_POST['order'] ?? ''))));
            rentti_reorder_photos($id, $order);
            rentti_flash('ok', 'Orden de fotos guardado.');
            rentti_redirect('/admin/property-edit.php?id=' . $id . '&step=6');
        }

        $updated = rentti_property_from_request($_POST, $property);
        $updated['id'] = $id;
        $updated['code'] = $property['code'];
        $updated['created_at'] = $property['created_at'];
        $updated['published_at'] = $property['published_at'];

        $errors = rentti_validate_property($updated);
        if (!$errors) {
            rentti_save_property($updated);
            if (!empty($_POST['nearby_editor'])) {
                rentti_replace_nearby($id, rentti_nearby_from_request($_POST));
            }
            $next = (int) ($_POST['next_step'] ?? $step);
            $next = max(1, min(6, $next));
            rentti_flash('ok', 'Guardado.');
            rentti_redirect('/admin/property-edit.php?id=' . $id . '&step=' . $next);
        }
        $property = array_merge($property, $updated);
    }
}

$photos = rentti_list_photos($id);
$nearbyPoints = rentti_list_nearby($id);
$steps = [
    1 => 'Tipo y arriendo',
    2 => 'Ubicación',
    3 => 'Características',
    4 => 'Textos',
    5 => 'Propietario',
    6 => 'Estado y fotos',
];

admin_layout_start('Editar ' . $property['code'], 'properties');
?>
  <div class="admin-head">
    <div>
      <h1><?= h($property['title'] ?: $property['code']) ?></h1>
      <div class="wizard-meta">
        <span><code><?= h($property['code']) ?></code></span>
        <span class="badge badge--<?= h($property['status']) ?>"><?= h(rentti_status_label($property['status'])) ?></span>
        <span>Arriendo · <?= h(rentti_type_label($property['property_type'])) ?></span>
      </div>
    </div>
    <a class="btn btn--ghost" href="/admin/">← Listado</a>
  </div>

  <?php if ($errors): ?>
    <div class="flash flash--error">
      <?php foreach ($errors as $err): ?>
        <div><?= h($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="wizard-shell">
    <nav class="steps" aria-label="Pasos">
      <?php foreach ($steps as $n => $label): ?>
        <a class="<?= $step === $n ? 'is-active' : '' ?>" href="/admin/property-edit.php?id=<?= $id ?>&step=<?= $n ?>"><?= $n ?>. <?= h($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <form method="post" enctype="multipart/form-data" class="panel" id="wizard-form">
      <input type="hidden" name="csrf" value="<?= h(rentti_csrf_token()) ?>" />
      <input type="hidden" name="id" value="<?= $id ?>" />
      <input type="hidden" name="step" value="<?= $step ?>" />
      <input type="hidden" name="form_action" id="form_action" value="save" />
      <input type="hidden" name="next_step" id="next_step" value="<?= $step ?>" />

      <?php if ($step === 1): ?>
        <h2>1. Tipo y arriendo</h2>
        <p class="hint">MVP: solo operación arriendo. Casa y Departamento primero.</p>
        <div class="grid-2">
          <div class="field">
            <label for="property_type">Tipo</label>
            <select id="property_type" name="property_type" required>
              <?php foreach (rentti_property_types() as $k => $label): ?>
                <option value="<?= h($k) ?>" <?= $property['property_type'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="rent_currency">Moneda</label>
            <select id="rent_currency" name="rent_currency">
              <?php foreach (rentti_currencies() as $k => $label): ?>
                <option value="<?= h($k) ?>" <?= $property['rent_currency'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="rent_amount">Precio arriendo</label>
            <input id="rent_amount" name="rent_amount" type="number" min="0" step="any" value="<?= h($property['rent_amount'] !== null && $property['rent_amount'] !== '' ? (string) $property['rent_amount'] : '') ?>" />
          </div>
          <div class="field">
            <label for="common_expenses">Gastos comunes</label>
            <input id="common_expenses" name="common_expenses" type="number" min="0" step="any" value="<?= h($property['common_expenses'] !== null && $property['common_expenses'] !== '' ? (string) $property['common_expenses'] : '') ?>" />
          </div>
        </div>
        <div class="check-row">
          <label><input type="checkbox" name="seasonal_rent" value="1" <?= !empty($property['seasonal_rent']) ? 'checked' : '' ?> /> Es por temporada</label>
        </div>
        <!-- preserve later fields -->
        <?php rentti_hidden_rest($property, ['property_type','rent_currency','rent_amount','common_expenses','seasonal_rent']); ?>

      <?php elseif ($step === 2): ?>
        <h2>2. Ubicación</h2>
        <div class="grid-2">
          <div class="field">
            <label for="region">Región</label>
            <select id="region" name="region">
              <?php foreach (rentti_regions() as $region): ?>
                <option value="<?= h($region) ?>" <?= $property['region'] === $region ? 'selected' : '' ?>><?= h($region) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="commune">Comuna *</label>
            <select id="commune" name="commune" required>
              <option value="">Selecciona…</option>
              <?php foreach (rentti_communes_for((string) $property['region']) as $c): ?>
                <option value="<?= h($c) ?>" <?= $property['commune'] === $c ? 'selected' : '' ?>><?= h($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="sector">Sector</label>
            <input id="sector" name="sector" type="text" value="<?= h((string) $property['sector']) ?>" />
          </div>
          <div class="field">
            <label for="referential_address">Dirección referencial * (pública)</label>
            <input id="referential_address" name="referential_address" type="text" required value="<?= h((string) $property['referential_address']) ?>" placeholder="Av. Providencia / Manuel Montt" />
          </div>
          <div class="field">
            <label for="street">Calle (interna)</label>
            <input id="street" name="street" type="text" value="<?= h((string) $property['street']) ?>" />
          </div>
          <div class="field">
            <label for="street_number">Nº / Unidad</label>
            <div style="display:flex;gap:0.5rem">
              <input id="street_number" name="street_number" type="text" value="<?= h((string) $property['street_number']) ?>" placeholder="Nº" />
              <input id="unit" name="unit" type="text" value="<?= h((string) $property['unit']) ?>" placeholder="Depto" />
            </div>
          </div>
          <div class="field">
            <label for="lat">Lat</label>
            <input id="lat" name="lat" type="number" step="any" value="<?= h($property['lat'] !== null && $property['lat'] !== '' ? (string) $property['lat'] : '') ?>" />
          </div>
          <div class="field">
            <label for="lng">Lng</label>
            <input id="lng" name="lng" type="number" step="any" value="<?= h($property['lng'] !== null && $property['lng'] !== '' ? (string) $property['lng'] : '') ?>" />
          </div>
        </div>
        <div class="check-row">
          <label><input type="checkbox" name="show_map" value="1" <?= !empty($property['show_map']) ? 'checked' : '' ?> /> Mostrar mapa en ficha pública</label>
        </div>

        <section class="amenity-block" id="zona-editor">
          <h3 class="amenity-block__title">Información de la zona</h3>
          <p class="hint">Puntos cercanos para la ficha pública (tabs). Distancia en metros; caminata y auto en minutos.</p>
          <input type="hidden" name="nearby_editor" value="1" />
          <div class="nearby-table-wrap">
            <table class="nearby-table" id="nearby-table">
              <thead>
                <tr>
                  <th>Tab</th>
                  <th>Columna</th>
                  <th>Nombre</th>
                  <th>mt</th>
                  <th>caminata</th>
                  <th>auto</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="nearby-body">
                <?php
                $rows = $nearbyPoints ?: [];
                if (!$rows) {
                    $rows = [['category' => 'zonas_cercanas', 'subtype' => 'buses', 'name' => '', 'distance_m' => '', 'walk_min' => '', 'drive_min' => '']];
                }
                foreach ($rows as $i => $row):
                ?>
                  <tr class="nearby-row">
                    <td>
                      <select name="nearby[<?= (int) $i ?>][category]" class="nearby-cat">
                        <?php foreach (rentti_zone_tabs() as $tk => $tmeta): ?>
                          <option value="<?= h($tk) ?>" <?= ($row['category'] ?? '') === $tk ? 'selected' : '' ?>><?= h($tmeta['label']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
                      <select name="nearby[<?= (int) $i ?>][subtype]" class="nearby-sub">
                        <option value="">—</option>
                        <?php foreach (rentti_zone_subtypes() as $sk => $slabel): ?>
                          <option value="<?= h($sk) ?>" <?= ($row['subtype'] ?? '') === $sk ? 'selected' : '' ?>><?= h($slabel) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td><input type="text" name="nearby[<?= (int) $i ?>][name]" value="<?= h((string) ($row['name'] ?? '')) ?>" placeholder="Nombre del lugar" /></td>
                    <td><input type="number" min="0" name="nearby[<?= (int) $i ?>][distance_m]" value="<?= h($row['distance_m'] !== null && $row['distance_m'] !== '' ? (string) $row['distance_m'] : '') ?>" /></td>
                    <td><input type="number" min="0" name="nearby[<?= (int) $i ?>][walk_min]" value="<?= h($row['walk_min'] !== null && $row['walk_min'] !== '' ? (string) $row['walk_min'] : '') ?>" /></td>
                    <td><input type="number" min="0" name="nearby[<?= (int) $i ?>][drive_min]" value="<?= h($row['drive_min'] !== null && $row['drive_min'] !== '' ? (string) $row['drive_min'] : '') ?>" /></td>
                    <td><button type="button" class="btn btn--ghost btn--small nearby-remove" title="Quitar">×</button></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <button type="button" class="btn btn--ghost btn--small" id="nearby-add" style="margin-top:0.65rem">+ Agregar punto</button>
        </section>

        <script>
          const communes = <?= json_encode(rentti_communes_by_region(), JSON_UNESCAPED_UNICODE) ?>;
          const regionEl = document.getElementById('region');
          const communeEl = document.getElementById('commune');
          const current = <?= json_encode((string) $property['commune'], JSON_UNESCAPED_UNICODE) ?>;
          regionEl.addEventListener('change', () => {
            const list = communes[regionEl.value] || [];
            communeEl.innerHTML = '<option value=\"\">Selecciona…</option>' + list.map(c => {
              const sel = c === current ? ' selected' : '';
              return `<option value=\"${c}\"${sel}>${c}</option>`;
            }).join('');
          });

          (function nearbyEditor() {
            const body = document.getElementById('nearby-body');
            const addBtn = document.getElementById('nearby-add');
            if (!body || !addBtn) return;
            const tabOpts = <?= json_encode(rentti_zone_tabs(), JSON_UNESCAPED_UNICODE) ?>;
            const subOpts = <?= json_encode(rentti_zone_subtypes(), JSON_UNESCAPED_UNICODE) ?>;

            function reindex() {
              [...body.querySelectorAll('.nearby-row')].forEach((tr, i) => {
                tr.querySelectorAll('[name]').forEach(el => {
                  el.name = el.name.replace(/nearby\[\d+]/, 'nearby[' + i + ']');
                });
              });
            }
            function syncSubtype(tr) {
              const cat = tr.querySelector('.nearby-cat');
              const sub = tr.querySelector('.nearby-sub');
              if (!cat || !sub) return;
              const on = cat.value === 'zonas_cercanas';
              sub.disabled = !on;
              if (!on) sub.value = '';
            }
            body.addEventListener('change', (e) => {
              if (e.target.classList.contains('nearby-cat')) {
                syncSubtype(e.target.closest('.nearby-row'));
              }
            });
            body.addEventListener('click', (e) => {
              if (e.target.classList.contains('nearby-remove')) {
                const rows = body.querySelectorAll('.nearby-row');
                if (rows.length <= 1) {
                  const tr = rows[0];
                  tr.querySelectorAll('input').forEach(i => i.value = '');
                  return;
                }
                e.target.closest('.nearby-row').remove();
                reindex();
              }
            });
            body.querySelectorAll('.nearby-row').forEach(syncSubtype);
            addBtn.addEventListener('click', () => {
              const i = body.querySelectorAll('.nearby-row').length;
              const tr = document.createElement('tr');
              tr.className = 'nearby-row';
              const tabHtml = Object.entries(tabOpts).map(([k, v]) => `<option value="${k}">${v.label}</option>`).join('');
              const subHtml = '<option value="">—</option>' + Object.entries(subOpts).map(([k, v]) => `<option value="${k}">${v}</option>`).join('');
              tr.innerHTML = `
                <td><select name="nearby[${i}][category]" class="nearby-cat">${tabHtml}</select></td>
                <td><select name="nearby[${i}][subtype]" class="nearby-sub">${subHtml}</select></td>
                <td><input type="text" name="nearby[${i}][name]" placeholder="Nombre del lugar" /></td>
                <td><input type="number" min="0" name="nearby[${i}][distance_m]" /></td>
                <td><input type="number" min="0" name="nearby[${i}][walk_min]" /></td>
                <td><input type="number" min="0" name="nearby[${i}][drive_min]" /></td>
                <td><button type="button" class="btn btn--ghost btn--small nearby-remove" title="Quitar">×</button></td>`;
              body.appendChild(tr);
              syncSubtype(tr);
            });
          })();
        </script>
        <?php rentti_hidden_rest($property, ['region','commune','sector','referential_address','street','street_number','unit','lat','lng','show_map']); ?>

      <?php elseif ($step === 3): ?>
        <h2>3. Características</h2>
        <p class="hint">Números básicos + amenities con icono (menos texto).</p>
        <div class="grid-3">
          <?php
          $nums = [
            'bedrooms' => 'Dormitorios',
            'bathrooms' => 'Baños',
            'suites' => 'Suites',
            'living_rooms' => 'Salas de estar',
            'useful_area' => 'Sup. útil (m²)',
            'total_area' => 'Sup. total (m²)',
            'land_area' => 'Sup. terreno (m²)',
            'built_area' => 'Sup. construida (m²)',
            'terrace_area' => 'Sup. terraza (m²)',
            'parking_covered' => 'Estac. cubiertos',
            'parking_open' => 'Estac. descubiertos',
            'year_built' => 'Año construcción',
            'floors' => 'Nº pisos',
            'elevators' => 'Ascensores',
          ];
          foreach ($nums as $key => $label):
          ?>
            <div class="field">
              <label for="<?= $key ?>"><?= h($label) ?></label>
              <input id="<?= $key ?>" name="<?= $key ?>" type="number" step="any" value="<?= h($property[$key] !== null && $property[$key] !== '' ? (string) $property[$key] : '') ?>" />
            </div>
          <?php endforeach; ?>
          <div class="field">
            <label for="floor_number">Piso</label>
            <input id="floor_number" name="floor_number" type="text" value="<?= h((string) $property['floor_number']) ?>" />
          </div>
          <div class="field">
            <label for="orientation">Orientación</label>
            <select id="orientation" name="orientation">
              <?php foreach (rentti_orientations() as $o): ?>
                <option value="<?= h($o) ?>" <?= (string) $property['orientation'] === $o ? 'selected' : '' ?>><?= h($o !== '' ? $o : '—') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="heating_type">Calefacción</label>
            <select id="heating_type" name="heating_type">
              <?php foreach (rentti_heating_types() as $o): ?>
                <option value="<?= h($o) ?>" <?= (string) $property['heating_type'] === $o ? 'selected' : '' ?>><?= h($o !== '' ? $o : '—') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <section class="amenity-block">
          <h3 class="amenity-block__title">Características</h3>
          <div class="amenity-picker" role="group" aria-label="Características de la unidad">
            <?php foreach (rentti_unit_features() as $k => $meta): ?>
              <label class="amenity-tile">
                <input type="checkbox" name="<?= h($k) ?>" value="1" <?= !empty($property[$k]) ? 'checked' : '' ?> />
                <span class="amenity-tile__face">
                  <?= rentti_amenity_icon_svg($meta['icon']) ?>
                  <span class="amenity-tile__label"><?= h($meta['label']) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="amenity-block">
          <h3 class="amenity-block__title">Estacionamiento</h3>
          <div class="grid-2">
            <div class="field">
              <label for="parking_price_uf">Precio (UF, opcional)</label>
              <input id="parking_price_uf" name="parking_price_uf" type="number" min="0" step="any" value="<?= h($property['parking_price_uf'] !== null && $property['parking_price_uf'] !== '' ? (string) $property['parking_price_uf'] : '') ?>" placeholder="ej. 390" />
            </div>
            <div class="field">
              <label for="parking_note">Nota / exclusividad</label>
              <input id="parking_note" name="parking_note" type="text" value="<?= h((string) ($property['parking_note'] ?? '')) ?>" placeholder="Exclusivo para deptos. 2D" />
            </div>
          </div>
        </section>

        <section class="amenity-block">
          <h3 class="amenity-block__title">Bodega</h3>
          <div class="amenity-picker amenity-picker--compact" role="group" aria-label="Bodega">
            <label class="amenity-tile">
              <input type="checkbox" name="storage" value="1" <?= !empty($property['storage']) ? 'checked' : '' ?> />
              <span class="amenity-tile__face">
                <?= rentti_amenity_icon_svg('storage') ?>
                <span class="amenity-tile__label">Incluye bodega</span>
              </span>
            </label>
          </div>
          <div class="field" style="max-width:14rem;margin-top:0.75rem">
            <label for="storage_price_uf">Precio (UF, opcional)</label>
            <input id="storage_price_uf" name="storage_price_uf" type="number" min="0" step="any" value="<?= h($property['storage_price_uf'] !== null && $property['storage_price_uf'] !== '' ? (string) $property['storage_price_uf'] : '') ?>" placeholder="ej. 90" />
          </div>
        </section>

        <section class="amenity-block">
          <h3 class="amenity-block__title">Áreas comunes</h3>
          <div class="amenity-picker" role="group" aria-label="Áreas comunes">
            <?php foreach (rentti_common_amenities() as $k => $meta): ?>
              <label class="amenity-tile">
                <input type="checkbox" name="<?= h($k) ?>" value="1" <?= !empty($property[$k]) ? 'checked' : '' ?> />
                <span class="amenity-tile__face">
                  <?= rentti_amenity_icon_svg($meta['icon']) ?>
                  <span class="amenity-tile__label"><?= h($meta['label']) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="amenity-block">
          <h3 class="amenity-block__title">Otros</h3>
          <div class="amenity-picker" role="group" aria-label="Otros">
            <?php foreach (rentti_extra_flags() as $k => $meta): ?>
              <label class="amenity-tile">
                <input type="checkbox" name="<?= h($k) ?>" value="1" <?= !empty($property[$k]) ? 'checked' : '' ?> />
                <span class="amenity-tile__face">
                  <?= rentti_amenity_icon_svg($meta['icon']) ?>
                  <span class="amenity-tile__label"><?= h($meta['label']) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </section>

        <?php
          $amenityVisible = array_merge(
              array_keys($nums),
              ['floor_number', 'orientation', 'heating_type', 'storage', 'storage_price_uf', 'parking_price_uf', 'parking_note'],
              rentti_feature_bool_keys(),
              rentti_common_amenity_keys(),
              array_keys(rentti_extra_flags())
          );
          rentti_hidden_rest($property, $amenityVisible);
        ?>

      <?php elseif ($step === 4): ?>
        <h2>4. Textos</h2>
        <div class="field" style="margin-bottom:1rem">
          <label for="title">Título</label>
          <input id="title" name="title" type="text" value="<?= h((string) $property['title']) ?>" />
        </div>
        <div class="field" style="margin-bottom:1rem">
          <label for="description">Descripción pública</label>
          <textarea id="description" name="description"><?= h((string) $property['description']) ?></textarea>
        </div>
        <div class="field" style="margin-bottom:1rem">
          <label for="visit_instructions">Forma de visitar (interno)</label>
          <textarea id="visit_instructions" name="visit_instructions"><?= h((string) $property['visit_instructions']) ?></textarea>
        </div>
        <div class="field">
          <label for="internal_notes">Notas internas</label>
          <textarea id="internal_notes" name="internal_notes"><?= h((string) $property['internal_notes']) ?></textarea>
        </div>
        <?php rentti_hidden_rest($property, ['title','description','visit_instructions','internal_notes']); ?>

      <?php elseif ($step === 5): ?>
        <h2>5. Propietario</h2>
        <div class="grid-2">
          <div class="field">
            <label for="owner_doc_type">Tipo documento</label>
            <select id="owner_doc_type" name="owner_doc_type">
              <option value="RUT" <?= $property['owner_doc_type'] === 'RUT' ? 'selected' : '' ?>>RUT</option>
              <option value="Pasaporte" <?= $property['owner_doc_type'] === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
            </select>
          </div>
          <div class="field">
            <label for="owner_doc_number">Nº documento</label>
            <input id="owner_doc_number" name="owner_doc_number" type="text" value="<?= h((string) $property['owner_doc_number']) ?>" />
          </div>
          <div class="field">
            <label for="owner_first_name">Nombre</label>
            <input id="owner_first_name" name="owner_first_name" type="text" value="<?= h((string) $property['owner_first_name']) ?>" />
          </div>
          <div class="field">
            <label for="owner_last_name">Apellido paterno</label>
            <input id="owner_last_name" name="owner_last_name" type="text" value="<?= h((string) $property['owner_last_name']) ?>" />
          </div>
          <div class="field">
            <label for="owner_maternal_last_name">Apellido materno</label>
            <input id="owner_maternal_last_name" name="owner_maternal_last_name" type="text" value="<?= h((string) $property['owner_maternal_last_name']) ?>" />
          </div>
          <div class="field">
            <label for="owner_email">Email</label>
            <input id="owner_email" name="owner_email" type="email" value="<?= h((string) $property['owner_email']) ?>" />
          </div>
          <div class="field">
            <label for="owner_phone">Teléfono</label>
            <input id="owner_phone" name="owner_phone" type="text" value="<?= h((string) $property['owner_phone']) ?>" />
          </div>
          <div class="field">
            <label for="owner_mobile">Celular</label>
            <input id="owner_mobile" name="owner_mobile" type="text" value="<?= h((string) $property['owner_mobile']) ?>" />
          </div>
        </div>
        <?php rentti_hidden_rest($property, ['owner_doc_type','owner_doc_number','owner_first_name','owner_last_name','owner_maternal_last_name','owner_email','owner_phone','owner_mobile']); ?>

      <?php else: ?>
        <h2>6. Estado y fotos</h2>
        <div class="grid-2" style="margin-bottom:1.25rem">
          <div class="field">
            <label for="status">Estado</label>
            <select id="status" name="status">
              <?php foreach (rentti_statuses() as $k => $label): ?>
                <option value="<?= h($k) ?>" <?= $property['status'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field" style="justify-content:end">
            <label>&nbsp;</label>
            <label style="display:flex;align-items:center;gap:0.4rem;font-weight:500">
              <input type="checkbox" name="featured" value="1" <?= !empty($property['featured']) ? 'checked' : '' ?> /> Destacada en el sitio
            </label>
          </div>
        </div>
        <?php rentti_hidden_rest($property, ['status','featured']); ?>

        <h2 style="font-size:1.05rem;margin-top:0.5rem">Fotos</h2>
        <p class="hint">JPG / PNG / WebP · máx. 8 MB. La primera foto se marca como portada; puedes cambiarla.</p>

        <div class="photos-grid" id="photos-grid">
          <?php foreach ($photos as $photo): ?>
            <div class="photo-tile" data-id="<?= (int) $photo['id'] ?>">
              <?php if (!empty($photo['is_cover'])): ?><span class="cover-tag">Portada</span><?php endif; ?>
              <img src="<?= h(rentti_photo_url($photo['filename'])) ?>" alt="" />
              <div class="tools">
                <?php if (empty($photo['is_cover'])): ?>
                  <button class="btn btn--ghost btn--small" type="submit" onclick="document.getElementById('form_action').value='set_cover'; this.form.photo_id.value='<?= (int) $photo['id'] ?>';">Portada</button>
                <?php endif; ?>
                <button class="btn btn--danger btn--small" type="submit" onclick="document.getElementById('form_action').value='delete_photo'; this.form.photo_id.value='<?= (int) $photo['id'] ?>'; return confirm('¿Eliminar foto?');">Borrar</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="photo_id" value="" />
        <input type="hidden" name="order" id="photo-order" value="" />

        <div class="field" style="margin-top:1rem">
          <label for="photo">Subir foto</label>
          <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" />
        </div>
        <div style="margin-top:0.75rem;display:flex;gap:0.5rem;flex-wrap:wrap">
          <button class="btn btn--ghost" type="submit" onclick="document.getElementById('form_action').value='upload_photo'">Subir foto</button>
          <?php if (count($photos) > 1): ?>
            <button class="btn btn--ghost" type="button" id="save-order-btn">Guardar orden (← →)</button>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="wizard-actions">
        <div>
          <?php if ($step > 1): ?>
            <button class="btn btn--ghost" type="submit" onclick="document.getElementById('next_step').value='<?= $step - 1 ?>'">← Anterior</button>
          <?php endif; ?>
        </div>
        <div class="right">
          <button class="btn btn--ghost" type="submit" onclick="document.getElementById('next_step').value='<?= $step ?>'">Guardar</button>
          <?php if ($step < 6): ?>
            <button class="btn btn--primary" type="submit" onclick="document.getElementById('next_step').value='<?= $step + 1 ?>'">Guardar y seguir →</button>
          <?php else: ?>
            <button class="btn btn--primary" type="submit" onclick="document.getElementById('next_step').value='6'">Guardar estado</button>
          <?php endif; ?>
        </div>
      </div>
    </form>

    <form method="post" onsubmit="return confirm('¿Eliminar esta propiedad de forma permanente?');" style="margin-top:0.5rem">
      <input type="hidden" name="csrf" value="<?= h(rentti_csrf_token()) ?>" />
      <input type="hidden" name="id" value="<?= $id ?>" />
      <input type="hidden" name="form_action" value="delete_property" />
      <button class="btn btn--danger" type="submit">Eliminar propiedad</button>
    </form>
  </div>

  <?php if ($step === 6 && count($photos) > 1): ?>
  <script>
    (function () {
      const grid = document.getElementById('photos-grid');
      const orderInput = document.getElementById('photo-order');
      const btn = document.getElementById('save-order-btn');
      if (!grid || !btn) return;
      btn.addEventListener('click', () => {
        const ids = [...grid.querySelectorAll('.photo-tile')].map(el => el.dataset.id);
        // rotate left as simple reorder demo: move first to end when holding shift? Instead open prompt.
        const next = prompt('Orden de IDs separados por coma', ids.join(','));
        if (!next) return;
        orderInput.value = next;
        const form = document.getElementById('wizard-form');
        document.getElementById('form_action').value = 'reorder_photos';
        form.submit();
      });
    })();
  </script>
  <?php endif; ?>
<?php
admin_layout_end();

/**
 * Persist fields from other wizard steps as hidden inputs so a partial POST
 * does not wipe the rest of the property.
 */
function rentti_hidden_rest(array $property, array $visible): void
{
    $skip = array_flip($visible);
    $skip['id'] = true;
    $skip['code'] = true;
    $skip['created_at'] = true;
    $skip['updated_at'] = true;
    $skip['published_at'] = true;
    $skip['cover_filename'] = true;
    foreach ($property as $key => $value) {
        if (isset($skip[$key])) {
            continue;
        }
        if (is_array($value)) {
            continue;
        }
        // checkboxes: only emit when truthy so unchecked stays off when visible elsewhere
        $boolKeys = array_merge(
            ['featured', 'seasonal_rent', 'show_map', 'storage', 'furnished', 'pets_allowed', 'pool', 'condominium'],
            rentti_feature_bool_keys(),
            rentti_common_amenity_keys()
        );
        if (in_array($key, $boolKeys, true)) {
            if (!empty($value)) {
                echo '<input type="hidden" name="' . h($key) . '" value="1" />' . "\n";
            }
            continue;
        }
        echo '<input type="hidden" name="' . h((string) $key) . '" value="' . h($value === null ? '' : (string) $value) . '" />' . "\n";
    }
}
