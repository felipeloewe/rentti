<?php
declare(strict_types=1);

/** Tabs for “Información de la zona”. */
function rentti_zone_tabs(): array
{
    return [
        'zonas_cercanas' => [
            'label' => 'Zonas Cercanas',
            'icon' => 'walk',
        ],
        'educacion' => [
            'label' => 'Áreas Educativas',
            'icon' => 'graduation',
        ],
        'verdes' => [
            'label' => 'Áreas Verdes',
            'icon' => 'tree',
        ],
        'comerciales' => [
            'label' => 'Áreas Comerciales',
            'icon' => 'basket',
        ],
    ];
}

/** Transit columns under Zonas Cercanas (Chile-friendly). */
function rentti_zone_subtypes(): array
{
    return [
        'buses' => 'Parada de Autobuses',
        'micros' => 'Micros',
        'metro' => 'Metro / Trenes',
    ];
}

function rentti_zone_icon_svg(string $icon, string $class = 'zone-icon'): string
{
    $stroke = 'currentColor';
    $common = 'fill="none" stroke="' . $stroke . '" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"';
    $paths = match ($icon) {
        'pin' => '<path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/>',
        'walk' => '<circle cx="12" cy="5" r="2"/><path d="M12 7v4l-3 6M12 11l3 2 2 5M9 13l-2 1"/>',
        'graduation' => '<path d="M3 9l9-4 9 4-9 4-9-4z"/><path d="M7 11.5v4.2c0 .8 2.2 2.3 5 2.3s5-1.5 5-2.3v-4.2"/><path d="M21 9v6"/>',
        'tree' => '<path d="M12 22v-6"/><path d="M8 16a4 4 0 1 1 8 0"/><path d="M9.5 12a3.5 3.5 0 1 1 5 0"/><path d="M10.5 8.5a2.5 2.5 0 1 1 3 0"/>',
        'basket' => '<path d="M4 8h16l-1.2 10.2A2 2 0 0 1 16.8 20H7.2a2 2 0 0 1-2-1.8L4 8z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
        'car' => '<path d="M4 14h16v4H4z"/><path d="M5 14 7 9h10l2 5"/><circle cx="8" cy="18" r="1.2"/><circle cx="16" cy="18" r="1.2"/>',
        'bus' => '<rect x="5" y="4" width="14" height="14" rx="2"/><path d="M5 12h14M9 18v2M15 18v2"/><circle cx="9" cy="15" r="1"/><circle cx="15" cy="15" r="1"/>',
        'train' => '<rect x="6" y="4" width="12" height="13" rx="2"/><path d="M6 11h12M9 17l-2 3M15 17l2 3"/><circle cx="9.5" cy="14" r="1"/><circle cx="14.5" cy="14" r="1"/>',
        default => '<circle cx="12" cy="12" r="7"/>',
    };
    return '<svg class="' . h($class) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" ' . $common . '>' . $paths . '</svg>';
}

function rentti_list_nearby(int $propertyId): array
{
    $stmt = rentti_db()->prepare(
        'SELECT * FROM property_nearby_points
         WHERE property_id = ?
         ORDER BY category ASC, subtype ASC, sort_order ASC, id ASC'
    );
    $stmt->execute([$propertyId]);
    return $stmt->fetchAll();
}

function rentti_nearby_from_request(array $src): array
{
    $raw = $src['nearby'] ?? null;
    if (!is_array($raw)) {
        return [];
    }
    $tabs = array_keys(rentti_zone_tabs());
    $subtypes = array_keys(rentti_zone_subtypes());
    $out = [];
    $order = 0;
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $category = (string) ($row['category'] ?? 'zonas_cercanas');
        if (!in_array($category, $tabs, true)) {
            $category = 'zonas_cercanas';
        }
        $subtype = trim((string) ($row['subtype'] ?? ''));
        if ($category === 'zonas_cercanas') {
            if (!in_array($subtype, $subtypes, true)) {
                $subtype = 'buses';
            }
        } else {
            $subtype = '';
        }
        $out[] = [
            'category' => $category,
            'subtype' => $subtype,
            'name' => $name,
            'distance_m' => rentti_nullable_int($row['distance_m'] ?? null),
            'walk_min' => rentti_nullable_int($row['walk_min'] ?? null),
            'drive_min' => rentti_nullable_int($row['drive_min'] ?? null),
            'sort_order' => $order++,
        ];
    }
    return $out;
}

function rentti_replace_nearby(int $propertyId, array $points): void
{
    $pdo = rentti_db();
    $pdo->prepare('DELETE FROM property_nearby_points WHERE property_id = ?')->execute([$propertyId]);
    if (!$points) {
        return;
    }
    $ins = $pdo->prepare(
        'INSERT INTO property_nearby_points
         (property_id, category, subtype, name, distance_m, walk_min, drive_min, sort_order, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $now = rentti_now();
    foreach ($points as $p) {
        $ins->execute([
            $propertyId,
            $p['category'],
            $p['subtype'] !== '' ? $p['subtype'] : null,
            $p['name'],
            $p['distance_m'],
            $p['walk_min'],
            $p['drive_min'],
            (int) $p['sort_order'],
            $now,
        ]);
    }
}

function rentti_group_nearby(array $points): array
{
    $grouped = [];
    foreach (array_keys(rentti_zone_tabs()) as $tab) {
        $grouped[$tab] = [];
    }
    foreach ($points as $p) {
        $cat = (string) ($p['category'] ?? '');
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [];
        }
        $grouped[$cat][] = $p;
    }
    return $grouped;
}

function rentti_group_transit(array $points): array
{
    $cols = [];
    foreach (array_keys(rentti_zone_subtypes()) as $sub) {
        $cols[$sub] = [];
    }
    foreach ($points as $p) {
        $sub = (string) ($p['subtype'] ?? 'buses');
        if (!isset($cols[$sub])) {
            $cols[$sub] = [];
        }
        $cols[$sub][] = $p;
    }
    return $cols;
}

function rentti_demo_nearby_points(): array
{
    return [
        ['category' => 'zonas_cercanas', 'subtype' => 'buses', 'name' => 'Manuel Montt / Providencia', 'distance_m' => 120, 'walk_min' => 2, 'drive_min' => 1, 'sort_order' => 0],
        ['category' => 'zonas_cercanas', 'subtype' => 'buses', 'name' => 'Pedro de Valdivia / Providencia', 'distance_m' => 450, 'walk_min' => 6, 'drive_min' => 2, 'sort_order' => 1],
        ['category' => 'zonas_cercanas', 'subtype' => 'micros', 'name' => 'Recorrido 405', 'distance_m' => 180, 'walk_min' => 3, 'drive_min' => 1, 'sort_order' => 2],
        ['category' => 'zonas_cercanas', 'subtype' => 'micros', 'name' => 'Recorrido 406', 'distance_m' => 220, 'walk_min' => 3, 'drive_min' => 2, 'sort_order' => 3],
        ['category' => 'zonas_cercanas', 'subtype' => 'metro', 'name' => 'Metro Manuel Montt', 'distance_m' => 350, 'walk_min' => 5, 'drive_min' => 2, 'sort_order' => 4],
        ['category' => 'educacion', 'subtype' => '', 'name' => 'Colegio San Ignacio', 'distance_m' => 800, 'walk_min' => 10, 'drive_min' => 4, 'sort_order' => 5],
        ['category' => 'educacion', 'subtype' => '', 'name' => 'Universidad Católica', 'distance_m' => 1500, 'walk_min' => 18, 'drive_min' => 6, 'sort_order' => 6],
        ['category' => 'verdes', 'subtype' => '', 'name' => 'Parque Bustamante', 'distance_m' => 600, 'walk_min' => 8, 'drive_min' => 3, 'sort_order' => 7],
        ['category' => 'verdes', 'subtype' => '', 'name' => 'Plaza Chile España', 'distance_m' => 400, 'walk_min' => 5, 'drive_min' => 2, 'sort_order' => 8],
        ['category' => 'comerciales', 'subtype' => '', 'name' => 'Mall Costanera Center', 'distance_m' => 1200, 'walk_min' => 15, 'drive_min' => 5, 'sort_order' => 9],
        ['category' => 'comerciales', 'subtype' => '', 'name' => 'Supermercado Jumbo', 'distance_m' => 500, 'walk_min' => 7, 'drive_min' => 3, 'sort_order' => 10],
    ];
}
