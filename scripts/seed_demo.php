<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';

$pdo = rentti_db();
$existing = (int) $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'publicada'")->fetchColumn();
if ($existing > 0) {
    echo "Already have published properties ($existing). Skip seed.\n";
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
$p['furnished'] = 1;
$p['pets_allowed'] = 1;
$p['condominium'] = 1;
$p['title'] = 'Departamento en arriendo en Providencia';
$p['description'] = "Luminoso departamento de 2 dormitorios en Providencia, cerca del metro Manuel Montt.\nLiving-comedor conectado, cocina equipada y terraza.";
$p['visit_instructions'] = 'Coordinar con corredor RENTTI.';
$p['owner_first_name'] = 'Demo';
$p['owner_last_name'] = 'Propietario';
$p['owner_email'] = 'demo@rentti.cl';
$p['owner_doc_number'] = '11.111.111-1';
$p['published_at'] = rentti_now();

$id = rentti_save_property($p);
echo "Seeded property id=$id code={$p['code']}\n";
