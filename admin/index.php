<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/layout.php';

rentti_require_login();

if (isset($_GET['new'])) {
    $id = rentti_create_draft();
    rentti_flash('ok', 'Borrador creado. Completa el flujo guiado.');
    rentti_redirect('/admin/property-edit.php?id=' . $id . '&step=1');
}

$filters = [
    'status' => trim((string) ($_GET['status'] ?? '')),
    'property_type' => trim((string) ($_GET['tipo'] ?? '')),
    'commune' => trim((string) ($_GET['comuna'] ?? '')),
    'price_min' => trim((string) ($_GET['precio_min'] ?? '')),
    'price_max' => trim((string) ($_GET['precio_max'] ?? '')),
    'q' => trim((string) ($_GET['q'] ?? '')),
];
$rows = rentti_list_properties($filters);

admin_layout_start('Propiedades', 'properties');
?>
  <div class="admin-head">
    <div>
      <h1>Propiedades</h1>
      <p><?= count($rows) ?> resultado<?= count($rows) === 1 ? '' : 's' ?> · MVP arriendo</p>
    </div>
    <a class="btn btn--primary" href="/admin/?new=1">Nueva propiedad</a>
  </div>

  <form class="filters" method="get" action="/admin/">
    <div class="field">
      <label for="status">Estado</label>
      <select id="status" name="status">
        <option value="">Todos</option>
        <?php foreach (rentti_statuses() as $k => $label): ?>
          <option value="<?= h($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo">
        <option value="">Todos</option>
        <?php foreach (rentti_property_types() as $k => $label): ?>
          <option value="<?= h($k) ?>" <?= $filters['property_type'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="comuna">Comuna</label>
      <input id="comuna" name="comuna" type="search" value="<?= h($filters['commune']) ?>" placeholder="Providencia…" />
    </div>
    <div class="field">
      <label for="precio_min">Precio desde</label>
      <input id="precio_min" name="precio_min" type="number" min="0" step="any" value="<?= h($filters['price_min']) ?>" />
    </div>
    <div class="field">
      <label for="precio_max">Precio hasta</label>
      <input id="precio_max" name="precio_max" type="number" min="0" step="any" value="<?= h($filters['price_max']) ?>" />
    </div>
    <div class="field field--actions">
      <button class="btn btn--primary" type="submit">Filtrar</button>
      <a class="btn btn--ghost" href="/admin/">Limpiar</a>
    </div>
  </form>

  <div class="table-wrap">
    <?php if (!$rows): ?>
      <div class="empty">No hay propiedades con estos filtros. Crea la primera con “Nueva propiedad”.</div>
    <?php else: ?>
      <table class="props">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Código</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Dirección</th>
            <th>Comuna</th>
            <th>Precio</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td>
                <?php if (!empty($row['cover_filename'])): ?>
                  <img class="thumb" src="<?= h(rentti_photo_url($row['cover_filename'])) ?>" alt="" />
                <?php else: ?>
                  <span class="thumb--empty" aria-hidden="true"></span>
                <?php endif; ?>
              </td>
              <td><code><?= h($row['code']) ?></code></td>
              <td><?= h(rentti_type_label($row['property_type'])) ?></td>
              <td><span class="badge badge--<?= h($row['status']) ?>"><?= h(rentti_status_label($row['status'])) ?></span></td>
              <td><?= h($row['referential_address'] ?: '—') ?></td>
              <td><?= h($row['commune'] ?: '—') ?></td>
              <td><?= h(rentti_format_price($row['rent_amount'] !== null ? (float) $row['rent_amount'] : null, $row['rent_currency'])) ?></td>
              <td>
                <a class="btn btn--ghost btn--small" href="/admin/property-edit.php?id=<?= (int) $row['id'] ?>">Editar</a>
                <?php if ($row['status'] === 'publicada'): ?>
                  <a class="btn btn--ghost btn--small" href="/propiedad.php?code=<?= h(urlencode($row['code'])) ?>" target="_blank" rel="noopener">Ficha</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php
admin_layout_end();
