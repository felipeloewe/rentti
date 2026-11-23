<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

$filters = [
    'commune' => trim((string) ($_GET['comuna'] ?? '')),
    'property_type' => trim((string) ($_GET['tipo'] ?? '')),
    'price_min' => trim((string) ($_GET['precio_min'] ?? '')),
    'price_max' => trim((string) ($_GET['precio_max'] ?? '')),
    'q' => trim((string) ($_GET['q'] ?? '')),
];

$rows = rentti_public_listings($filters);
$out = [];
foreach ($rows as $row) {
    $out[] = [
        'code' => $row['code'],
        'title' => $row['title'],
        'type' => $row['property_type'],
        'type_label' => rentti_type_label($row['property_type']),
        'commune' => $row['commune'],
        'region' => $row['region'],
        'address' => $row['referential_address'],
        'price' => $row['rent_amount'] !== null ? (float) $row['rent_amount'] : null,
        'currency' => $row['rent_currency'],
        'price_label' => rentti_format_price(
            $row['rent_amount'] !== null ? (float) $row['rent_amount'] : null,
            $row['rent_currency']
        ),
        'bedrooms' => $row['bedrooms'],
        'bathrooms' => $row['bathrooms'],
        'featured' => (bool) $row['featured'],
        'cover' => !empty($row['cover_filename']) ? rentti_photo_url($row['cover_filename']) : null,
        'url' => '/propiedad.php?code=' . rawurlencode($row['code']),
    ];
}

echo json_encode(['count' => count($out), 'items' => $out], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
