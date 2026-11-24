<?php
declare(strict_types=1);

function rentti_next_code(PDO $pdo): string
{
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties WHERE code LIKE 'RTY-{$year}-%'");
    $n = (int) $stmt->fetchColumn() + 1;
    return sprintf('RTY-%s-%04d', $year, $n);
}

function rentti_property_defaults(): array
{
    $now = rentti_now();
    return [
        'id' => null,
        'code' => '',
        'property_type' => 'departamento',
        'operation' => 'arriendo',
        'status' => 'borrador',
        'featured' => 0,
        'seasonal_rent' => 0,
        'rent_amount' => null,
        'rent_currency' => 'CLP',
        'common_expenses' => null,
        'region' => 'Metropolitana',
        'commune' => '',
        'sector' => '',
        'street' => '',
        'street_number' => '',
        'unit' => '',
        'referential_address' => '',
        'lat' => null,
        'lng' => null,
        'show_map' => 0,
        'bedrooms' => null,
        'bathrooms' => null,
        'suites' => null,
        'living_rooms' => null,
        'useful_area' => null,
        'total_area' => null,
        'land_area' => null,
        'built_area' => null,
        'terrace_area' => null,
        'floor_number' => '',
        'year_built' => null,
        'floors' => null,
        'elevators' => null,
        'parking_covered' => null,
        'parking_open' => null,
        'storage' => 0,
        'storage_price_uf' => null,
        'parking_price_uf' => null,
        'parking_note' => '',
        'furnished' => 0,
        'pets_allowed' => 0,
        'pool' => 0,
        'condominium' => 0,
        'feat_termopanel' => 0,
        'feat_cocina_equipada' => 0,
        'feat_gres_porcelanato' => 0,
        'feat_revestimiento_eifs' => 0,
        'feat_piso_fotolaminado' => 0,
        'feat_cocina_integrada' => 0,
        'amenity_salon_gourmet' => 0,
        'amenity_conserjeria' => 0,
        'amenity_ecommerce' => 0,
        'amenity_bicicletero' => 0,
        'amenity_gimnasio' => 0,
        'amenity_cowork' => 0,
        'delivery_label' => '',
        'project_name' => '',
        'price_from_uf' => null,
        'promo_badge' => '',
        'promo_line_1' => '',
        'promo_line_2' => '',
        'summary_dorms_min' => null,
        'summary_dorms_max' => null,
        'summary_baths_min' => null,
        'summary_baths_max' => null,
        'summary_m2_min' => null,
        'summary_m2_max' => null,
        'tipologias_json' => '[]',
        'orientation' => '',
        'heating_type' => '',
        'title' => '',
        'description' => '',
        'visit_instructions' => '',
        'internal_notes' => '',
        'owner_doc_type' => 'RUT',
        'owner_doc_number' => '',
        'owner_first_name' => '',
        'owner_last_name' => '',
        'owner_maternal_last_name' => '',
        'owner_email' => '',
        'owner_phone' => '',
        'owner_mobile' => '',
        'created_at' => $now,
        'updated_at' => $now,
        'published_at' => null,
    ];
}

function rentti_get_property(int $id): ?array
{
    $stmt = rentti_db()->prepare('SELECT * FROM properties WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function rentti_get_property_by_code(string $code): ?array
{
    $stmt = rentti_db()->prepare('SELECT * FROM properties WHERE code = ?');
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function rentti_bool_int($value): int
{
    return !empty($value) ? 1 : 0;
}

function rentti_nullable_float($value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }
    return (float) str_replace(',', '.', (string) $value);
}

function rentti_nullable_int($value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }
    return (int) $value;
}

function rentti_property_from_request(array $src, ?array $existing = null): array
{
    $base = $existing ? array_merge(rentti_property_defaults(), $existing) : rentti_property_defaults();
    $types = array_keys(rentti_property_types());
    $statuses = array_keys(rentti_statuses());
    $currencies = array_keys(rentti_currencies());

    $type = (string) ($src['property_type'] ?? $base['property_type']);
    if (!in_array($type, $types, true)) {
        $type = 'departamento';
    }
    $status = (string) ($src['status'] ?? $base['status']);
    if (!in_array($status, $statuses, true)) {
        $status = 'borrador';
    }
    $currency = (string) ($src['rent_currency'] ?? $base['rent_currency']);
    if (!in_array($currency, $currencies, true)) {
        $currency = 'CLP';
    }

    $base['property_type'] = $type;
    $base['operation'] = 'arriendo';
    $base['status'] = $status;
    $base['featured'] = rentti_bool_int($src['featured'] ?? 0);
    $base['seasonal_rent'] = rentti_bool_int($src['seasonal_rent'] ?? 0);
    $base['rent_amount'] = rentti_nullable_float($src['rent_amount'] ?? null);
    $base['rent_currency'] = $currency;
    $base['common_expenses'] = rentti_nullable_float($src['common_expenses'] ?? null);
    $base['region'] = trim((string) ($src['region'] ?? $base['region']));
    $base['commune'] = trim((string) ($src['commune'] ?? ''));
    $base['sector'] = trim((string) ($src['sector'] ?? ''));
    $base['street'] = trim((string) ($src['street'] ?? ''));
    $base['street_number'] = trim((string) ($src['street_number'] ?? ''));
    $base['unit'] = trim((string) ($src['unit'] ?? ''));
    $base['referential_address'] = trim((string) ($src['referential_address'] ?? ''));
    $base['lat'] = rentti_nullable_float($src['lat'] ?? null);
    $base['lng'] = rentti_nullable_float($src['lng'] ?? null);
    $base['show_map'] = rentti_bool_int($src['show_map'] ?? 0);
    $base['bedrooms'] = rentti_nullable_int($src['bedrooms'] ?? null);
    $base['bathrooms'] = rentti_nullable_int($src['bathrooms'] ?? null);
    $base['suites'] = rentti_nullable_int($src['suites'] ?? null);
    $base['living_rooms'] = rentti_nullable_int($src['living_rooms'] ?? null);
    $base['useful_area'] = rentti_nullable_float($src['useful_area'] ?? null);
    $base['total_area'] = rentti_nullable_float($src['total_area'] ?? null);
    $base['land_area'] = rentti_nullable_float($src['land_area'] ?? null);
    $base['built_area'] = rentti_nullable_float($src['built_area'] ?? null);
    $base['terrace_area'] = rentti_nullable_float($src['terrace_area'] ?? null);
    $base['floor_number'] = trim((string) ($src['floor_number'] ?? ''));
    $base['year_built'] = rentti_nullable_int($src['year_built'] ?? null);
    $base['floors'] = rentti_nullable_int($src['floors'] ?? null);
    $base['elevators'] = rentti_nullable_int($src['elevators'] ?? null);
    $base['parking_covered'] = rentti_nullable_int($src['parking_covered'] ?? null);
    $base['parking_open'] = rentti_nullable_int($src['parking_open'] ?? null);
    $base['storage'] = rentti_bool_int($src['storage'] ?? 0);
    $base['storage_price_uf'] = rentti_nullable_float($src['storage_price_uf'] ?? null);
    $base['parking_price_uf'] = rentti_nullable_float($src['parking_price_uf'] ?? null);
    $base['parking_note'] = trim((string) ($src['parking_note'] ?? ''));
    $base['furnished'] = rentti_bool_int($src['furnished'] ?? 0);
    $base['pets_allowed'] = rentti_bool_int($src['pets_allowed'] ?? 0);
    $base['pool'] = rentti_bool_int($src['pool'] ?? 0);
    $base['condominium'] = rentti_bool_int($src['condominium'] ?? 0);
    foreach (rentti_feature_bool_keys() as $featKey) {
        $base[$featKey] = rentti_bool_int($src[$featKey] ?? 0);
    }
    foreach (rentti_common_amenity_keys() as $amenityKey) {
        $base[$amenityKey] = rentti_bool_int($src[$amenityKey] ?? 0);
    }
    $base['delivery_label'] = trim((string) ($src['delivery_label'] ?? ''));
    $base['project_name'] = trim((string) ($src['project_name'] ?? ''));
    $base['price_from_uf'] = rentti_nullable_float($src['price_from_uf'] ?? null);
    $base['promo_badge'] = trim((string) ($src['promo_badge'] ?? ''));
    $base['promo_line_1'] = trim((string) ($src['promo_line_1'] ?? ''));
    $base['promo_line_2'] = trim((string) ($src['promo_line_2'] ?? ''));
    $base['summary_dorms_min'] = rentti_nullable_int($src['summary_dorms_min'] ?? null);
    $base['summary_dorms_max'] = rentti_nullable_int($src['summary_dorms_max'] ?? null);
    $base['summary_baths_min'] = rentti_nullable_int($src['summary_baths_min'] ?? null);
    $base['summary_baths_max'] = rentti_nullable_int($src['summary_baths_max'] ?? null);
    $base['summary_m2_min'] = rentti_nullable_float($src['summary_m2_min'] ?? null);
    $base['summary_m2_max'] = rentti_nullable_float($src['summary_m2_max'] ?? null);
    if (isset($src['tipologia']) && is_array($src['tipologia'])) {
        $base['tipologias_json'] = rentti_tipologias_from_request($src);
    } elseif (array_key_exists('tipologias_json', $src)) {
        $decoded = json_decode((string) $src['tipologias_json'], true);
        $base['tipologias_json'] = is_array($decoded)
            ? json_encode($decoded, JSON_UNESCAPED_UNICODE)
            : (string) ($base['tipologias_json'] ?? '[]');
    }
    $base['orientation'] = trim((string) ($src['orientation'] ?? ''));
    $base['heating_type'] = trim((string) ($src['heating_type'] ?? ''));
    $base['title'] = trim((string) ($src['title'] ?? ''));
    $base['description'] = trim((string) ($src['description'] ?? ''));
    $base['visit_instructions'] = trim((string) ($src['visit_instructions'] ?? ''));
    $base['internal_notes'] = trim((string) ($src['internal_notes'] ?? ''));
    $base['owner_doc_type'] = in_array(($src['owner_doc_type'] ?? 'RUT'), ['RUT', 'Pasaporte'], true)
        ? (string) $src['owner_doc_type']
        : 'RUT';
    $base['owner_doc_number'] = trim((string) ($src['owner_doc_number'] ?? ''));
    $base['owner_first_name'] = trim((string) ($src['owner_first_name'] ?? ''));
    $base['owner_last_name'] = trim((string) ($src['owner_last_name'] ?? ''));
    $base['owner_maternal_last_name'] = trim((string) ($src['owner_maternal_last_name'] ?? ''));
    $base['owner_email'] = trim((string) ($src['owner_email'] ?? ''));
    $base['owner_phone'] = trim((string) ($src['owner_phone'] ?? ''));
    $base['owner_mobile'] = trim((string) ($src['owner_mobile'] ?? ''));
    $base['updated_at'] = rentti_now();

    if ($status === 'publicada' && empty($base['published_at'])) {
        $base['published_at'] = rentti_now();
    }
    if ($status !== 'publicada') {
        // keep published_at history if it existed
    }

    if ($base['title'] === '' && $base['commune'] !== '') {
        $base['title'] = rentti_type_label($type) . ' en arriendo en ' . $base['commune'];
    }

    return $base;
}

function rentti_validate_property(array $p): array
{
    $errors = [];
    if ($p['referential_address'] === '') {
        $errors[] = 'La dirección referencial es obligatoria.';
    }
    if ($p['commune'] === '') {
        $errors[] = 'La comuna es obligatoria.';
    }
    if ($p['status'] === 'publicada' && $p['rent_amount'] === null) {
        $errors[] = 'Para publicar se requiere precio de arriendo.';
    }
    return $errors;
}

function rentti_save_property(array $p): int
{
    $pdo = rentti_db();
    $fields = [
        'code', 'property_type', 'operation', 'status', 'featured', 'seasonal_rent',
        'rent_amount', 'rent_currency', 'common_expenses', 'region', 'commune', 'sector',
        'street', 'street_number', 'unit', 'referential_address', 'lat', 'lng', 'show_map',
        'bedrooms', 'bathrooms', 'suites', 'living_rooms', 'useful_area', 'total_area',
        'land_area', 'built_area', 'terrace_area', 'floor_number', 'year_built', 'floors',
        'elevators', 'parking_covered', 'parking_open', 'storage', 'storage_price_uf',
        'parking_price_uf', 'parking_note', 'furnished',
        'pets_allowed', 'pool', 'condominium',
        'feat_termopanel', 'feat_cocina_equipada', 'feat_gres_porcelanato',
        'feat_revestimiento_eifs', 'feat_piso_fotolaminado', 'feat_cocina_integrada',
        'amenity_salon_gourmet', 'amenity_conserjeria', 'amenity_ecommerce',
        'amenity_bicicletero', 'amenity_gimnasio', 'amenity_cowork',
        'delivery_label', 'project_name', 'price_from_uf',
        'promo_badge', 'promo_line_1', 'promo_line_2',
        'summary_dorms_min', 'summary_dorms_max', 'summary_baths_min', 'summary_baths_max',
        'summary_m2_min', 'summary_m2_max', 'tipologias_json',
        'orientation', 'heating_type',
        'title', 'description', 'visit_instructions', 'internal_notes',
        'owner_doc_type', 'owner_doc_number', 'owner_first_name', 'owner_last_name',
        'owner_maternal_last_name', 'owner_email', 'owner_phone', 'owner_mobile',
        'created_at', 'updated_at', 'published_at',
    ];

    if (empty($p['id'])) {
        if ($p['code'] === '') {
            $p['code'] = rentti_next_code($pdo);
        }
        $p['created_at'] = $p['created_at'] ?: rentti_now();
        $cols = implode(', ', $fields);
        $placeholders = implode(', ', array_map(fn ($f) => ':' . $f, $fields));
        $stmt = $pdo->prepare("INSERT INTO properties ($cols) VALUES ($placeholders)");
        foreach ($fields as $f) {
            $stmt->bindValue(':' . $f, $p[$f]);
        }
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    $sets = implode(', ', array_map(fn ($f) => "$f = :$f", $fields));
    $stmt = $pdo->prepare("UPDATE properties SET $sets WHERE id = :id");
    foreach ($fields as $f) {
        $stmt->bindValue(':' . $f, $p[$f]);
    }
    $stmt->bindValue(':id', (int) $p['id'], PDO::PARAM_INT);
    $stmt->execute();
    return (int) $p['id'];
}

function rentti_create_draft(): int
{
    $p = rentti_property_defaults();
    $p['code'] = rentti_next_code(rentti_db());
    $p['title'] = 'Nuevo arriendo (borrador)';
    return rentti_save_property($p);
}

function rentti_delete_property(int $id): void
{
    $photos = rentti_list_photos($id);
    $pdo = rentti_db();
    $stmt = $pdo->prepare('DELETE FROM properties WHERE id = ?');
    $stmt->execute([$id]);
    foreach ($photos as $photo) {
        $path = RENTTI_ROOT . '/uploads/properties/' . $photo['filename'];
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

function rentti_list_properties(array $filters = []): array
{
    $sql = 'SELECT p.*, (
        SELECT filename FROM property_photos ph
        WHERE ph.property_id = p.id
        ORDER BY ph.is_cover DESC, ph.sort_order ASC, ph.id ASC
        LIMIT 1
    ) AS cover_filename FROM properties p WHERE 1=1';
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= ' AND p.status = ?';
        $params[] = $filters['status'];
    }
    if (!empty($filters['property_type'])) {
        $sql .= ' AND p.property_type = ?';
        $params[] = $filters['property_type'];
    }
    if (!empty($filters['commune'])) {
        $sql .= ' AND p.commune LIKE ?';
        $params[] = '%' . $filters['commune'] . '%';
    }
    if (isset($filters['price_min']) && $filters['price_min'] !== '' && $filters['price_min'] !== null) {
        $sql .= ' AND p.rent_amount >= ?';
        $params[] = (float) $filters['price_min'];
    }
    if (isset($filters['price_max']) && $filters['price_max'] !== '' && $filters['price_max'] !== null) {
        $sql .= ' AND p.rent_amount <= ?';
        $params[] = (float) $filters['price_max'];
    }
    if (!empty($filters['q'])) {
        $sql .= ' AND (p.code LIKE ? OR p.title LIKE ? OR p.referential_address LIKE ? OR p.commune LIKE ?)';
        $q = '%' . $filters['q'] . '%';
        array_push($params, $q, $q, $q, $q);
    }

    $sql .= ' ORDER BY p.updated_at DESC';
    $stmt = rentti_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function rentti_public_listings(array $filters = []): array
{
    $filters['status'] = 'publicada';
    return rentti_list_properties($filters);
}

function rentti_list_photos(int $propertyId): array
{
    $stmt = rentti_db()->prepare(
        'SELECT * FROM property_photos WHERE property_id = ? ORDER BY is_cover DESC, sort_order ASC, id ASC'
    );
    $stmt->execute([$propertyId]);
    return $stmt->fetchAll();
}

function rentti_add_photo(int $propertyId, string $tmpPath, string $originalName): array
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpPath) ?: '';
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG o WebP.');
    }
    if (filesize($tmpPath) > 8 * 1024 * 1024) {
        throw new RuntimeException('La imagen supera 8 MB.');
    }

    $ext = $allowed[$mime];
    $filename = sprintf('p%d_%s.%s', $propertyId, bin2hex(random_bytes(8)), $ext);
    $dest = RENTTI_ROOT . '/uploads/properties/' . $filename;
    if (!move_uploaded_file($tmpPath, $dest) && !rename($tmpPath, $dest)) {
        throw new RuntimeException('No se pudo guardar la imagen.');
    }
    @chmod($dest, 0644);

    $pdo = rentti_db();
    $cstmt = $pdo->prepare('SELECT COUNT(*) FROM property_photos WHERE property_id = ?');
    $cstmt->execute([$propertyId]);
    $count = (int) $cstmt->fetchColumn();

    $orderStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM property_photos WHERE property_id = ?');
    $orderStmt->execute([$propertyId]);
    $order = (int) $orderStmt->fetchColumn();

    $isCover = $count === 0 ? 1 : 0;
    $ins = $pdo->prepare(
        'INSERT INTO property_photos (property_id, filename, sort_order, is_cover, created_at)
         VALUES (?, ?, ?, ?, ?)'
    );
    $ins->execute([$propertyId, $filename, $order, $isCover, rentti_now()]);

    return [
        'id' => (int) $pdo->lastInsertId(),
        'filename' => $filename,
        'is_cover' => $isCover,
        'sort_order' => $order,
    ];
}

function rentti_set_cover(int $propertyId, int $photoId): void
{
    $pdo = rentti_db();
    $pdo->prepare('UPDATE property_photos SET is_cover = 0 WHERE property_id = ?')->execute([$propertyId]);
    $pdo->prepare('UPDATE property_photos SET is_cover = 1 WHERE id = ? AND property_id = ?')
        ->execute([$photoId, $propertyId]);
}

function rentti_reorder_photos(int $propertyId, array $orderedIds): void
{
    $pdo = rentti_db();
    $stmt = $pdo->prepare('UPDATE property_photos SET sort_order = ? WHERE id = ? AND property_id = ?');
    foreach (array_values($orderedIds) as $i => $id) {
        $stmt->execute([$i, (int) $id, $propertyId]);
    }
}

function rentti_delete_photo(int $propertyId, int $photoId): void
{
    $pdo = rentti_db();
    $stmt = $pdo->prepare('SELECT * FROM property_photos WHERE id = ? AND property_id = ?');
    $stmt->execute([$photoId, $propertyId]);
    $photo = $stmt->fetch();
    if (!$photo) {
        return;
    }
    $pdo->prepare('DELETE FROM property_photos WHERE id = ?')->execute([$photoId]);
    $path = RENTTI_ROOT . '/uploads/properties/' . $photo['filename'];
    if (is_file($path)) {
        @unlink($path);
    }
    if ((int) $photo['is_cover'] === 1) {
        $next = $pdo->prepare(
            'SELECT id FROM property_photos WHERE property_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1'
        );
        $next->execute([$propertyId]);
        $nid = $next->fetchColumn();
        if ($nid) {
            rentti_set_cover($propertyId, (int) $nid);
        }
    }
}
