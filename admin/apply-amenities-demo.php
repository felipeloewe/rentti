<?php
declare(strict_types=1);
/**
 * One-click: apply Felipe reference amenity set to the first published property.
 * Login required. Safe to remove after demo fill.
 */
require_once __DIR__ . '/../lib/bootstrap.php';
rentti_require_login();

$code = trim((string) ($_GET['code'] ?? 'RTY-2026-0001'));
$property = rentti_get_property_by_code($code);
if (!$property) {
    $row = rentti_db()->query("SELECT id FROM properties WHERE status = 'publicada' ORDER BY id ASC LIMIT 1")->fetch();
    $property = $row ? rentti_get_property((int) $row['id']) : null;
}
if (!$property) {
    http_response_code(404);
    echo 'No property found.';
    exit;
}

foreach (array_keys(rentti_unit_features()) as $k) {
    $property[$k] = 1;
}
foreach (array_keys(rentti_common_amenities()) as $k) {
    $property[$k] = 1;
}
$property['storage'] = 1;
$property['storage_price_uf'] = 90;
$property['parking_price_uf'] = 390;
$property['parking_note'] = 'Exclusivo para deptos. 2D';
$property['furnished'] = 1;
$property['pets_allowed'] = 1;
$property['condominium'] = 1;
if ($property['parking_covered'] === null || (int) $property['parking_covered'] < 1) {
    $property['parking_covered'] = 1;
}
rentti_save_property($property);
rentti_replace_nearby((int) $property['id'], rentti_demo_nearby_points());

rentti_flash('ok', 'Amenities + zona de referencia aplicados a ' . $property['code']);
rentti_redirect('/admin/property-edit.php?id=' . (int) $property['id'] . '&step=2');
