<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';

$pdo = rentti_db();
$forceAmenities = in_array('--force-amenities', $argv ?? [], true);

$existing = (int) $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'publicada'")->fetchColumn();
if ($existing > 0 && !$forceAmenities) {
    echo "Already have published properties ($existing). Skip seed.\n";
    echo "Tip: php scripts/seed_demo.php --force-amenities  (fills Felipe amenity set on first published)\n";
    exit(0);
}

if ($forceAmenities && $existing > 0) {
    $row = $pdo->query("SELECT id, code FROM properties WHERE status = 'publicada' ORDER BY id ASC LIMIT 1")->fetch();
    if (!$row) {
        echo "No published property to update.\n";
        exit(1);
    }
    $id = (int) $row['id'];
    $p = rentti_get_property($id);
    if (!$p) {
        echo "Property not found.\n";
        exit(1);
    }
    foreach (array_keys(rentti_unit_features()) as $k) {
        $p[$k] = 1;
    }
    foreach (array_keys(rentti_common_amenities()) as $k) {
        $p[$k] = 1;
    }
    $p['storage'] = 1;
    $p['storage_price_uf'] = 90;
    $p['parking_price_uf'] = 390;
    $p['parking_note'] = 'Exclusivo para deptos. 2D';
    $p['furnished'] = 1;
    $p['pets_allowed'] = 1;
    $p['condominium'] = 1;
    $p['parking_covered'] = max(1, (int) ($p['parking_covered'] ?? 0));
    $p['delivery_label'] = 'Segundo semestre 2027';
    $p['project_name'] = 'Providencia / Manuel Montt';
    $p['price_from_uf'] = 3049;
    $p['promo_badge'] = 'Hasta 5% OFF';
    $p['promo_line_1'] = 'Pie hasta 49 cuotas sin interés.';
    $p['promo_line_2'] = 'Regalo del 5% de tu pie.';
    $p['summary_dorms_min'] = 1;
    $p['summary_dorms_max'] = 2;
    $p['summary_baths_min'] = 1;
    $p['summary_baths_max'] = 2;
    $p['summary_m2_min'] = 29.2;
    $p['summary_m2_max'] = 56.83;
    $p['tipologias_json'] = json_encode(rentti_demo_tipologias(), JSON_UNESCAPED_UNICODE);
    rentti_save_property($p);
    rentti_replace_nearby($id, rentti_demo_nearby_points());
    echo "Updated amenities + zone + project on id=$id code={$p['code']}\n";
    exit(0);
}

$p = rentti_property_defaults();
$p['code'] = rentti_next_code($pdo);
$p['property_type'] = 'departamento';
$p['status'] = 'publicada';
$p['featured'] = 1;
$p['rent_amount'] = 650000;
$p['rent_currency'] = 'CLP';
$p['common_expenses'] = 80000;
$p['region'] = 'Metropolitana';
$p['commune'] = 'Providencia';
$p['sector'] = 'Manuel Montt';
$p['referential_address'] = 'Av. Providencia / Manuel Montt';
$p['bedrooms'] = 2;
$p['bathrooms'] = 2;
$p['useful_area'] = 68;
$p['total_area'] = 75;
$p['parking_covered'] = 1;
$p['furnished'] = 1;
$p['pets_allowed'] = 1;
$p['condominium'] = 1;
$p['storage'] = 1;
$p['storage_price_uf'] = 90;
$p['parking_price_uf'] = 390;
$p['parking_note'] = 'Exclusivo para deptos. 2D';
foreach (array_keys(rentti_unit_features()) as $k) {
    $p[$k] = 1;
}
foreach (array_keys(rentti_common_amenities()) as $k) {
    $p[$k] = 1;
}
$p['title'] = 'Departamento en arriendo en Providencia';
$p['description'] = "Luminoso departamento de 2 dormitorios en Providencia, cerca del metro Manuel Montt.\nLiving-comedor conectado, cocina equipada y terraza.";
$p['visit_instructions'] = 'Coordinar con corredor RENTTI.';
$p['owner_first_name'] = 'Demo';
$p['owner_last_name'] = 'Propietario';
$p['owner_email'] = 'demo@rentti.cl';
$p['owner_doc_number'] = '11.111.111-1';
$p['published_at'] = rentti_now();
$p['delivery_label'] = 'Segundo semestre 2027';
$p['project_name'] = 'Providencia / Manuel Montt';
$p['price_from_uf'] = 3049;
$p['promo_badge'] = 'Hasta 5% OFF';
$p['promo_line_1'] = 'Pie hasta 49 cuotas sin interés.';
$p['promo_line_2'] = 'Regalo del 5% de tu pie.';
$p['summary_dorms_min'] = 1;
$p['summary_dorms_max'] = 2;
$p['summary_baths_min'] = 1;
$p['summary_baths_max'] = 2;
$p['summary_m2_min'] = 29.2;
$p['summary_m2_max'] = 56.83;
$p['tipologias_json'] = json_encode(rentti_demo_tipologias(), JSON_UNESCAPED_UNICODE);

$id = rentti_save_property($p);
rentti_replace_nearby($id, rentti_demo_nearby_points());
echo "Seeded property id=$id code={$p['code']}\n";
