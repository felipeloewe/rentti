<?php
declare(strict_types=1);

function rentti_project_icon_svg(string $icon, string $class = 'project-icon'): string
{
    $stroke = 'currentColor';
    $common = 'fill="none" stroke="' . $stroke . '" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"';
    $paths = match ($icon) {
        'bed' => '<path d="M3 18V10a2 2 0 0 1 2-2h6v10M3 14h18M13 8h6a2 2 0 0 1 2 2v8M5 8V6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
        'bath' => '<path d="M4 12h16v2a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-2z"/><path d="M6 12V7a2 2 0 0 1 2-2h1"/><path d="M7 20h2M15 20h2"/>',
        'area' => '<path d="M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4"/><rect x="8" y="8" width="8" height="8" rx="1"/>',
        default => '<circle cx="12" cy="12" r="7"/>',
    };
    return '<svg class="' . h($class) . '" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" ' . $common . '>' . $paths . '</svg>';
}

function rentti_range_label($min, $max, string $suffix = ''): string
{
    $hasMin = $min !== null && $min !== '';
    $hasMax = $max !== null && $max !== '';
    if (!$hasMin && !$hasMax) {
        return '';
    }
    if ($hasMin && $hasMax && (float) $min !== (float) $max) {
        return rentti_format_range_num($min) . ' a ' . rentti_format_range_num($max) . $suffix;
    }
    $v = $hasMin ? $min : $max;
    return rentti_format_range_num($v) . $suffix;
}

function rentti_format_range_num($value): string
{
    $n = (float) $value;
    if (abs($n - round($n)) < 0.001) {
        return (string) (int) round($n);
    }
    return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
}

function rentti_decode_tipologias(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [];
    }
    $out = [];
    foreach ($data as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = trim((string) ($row['name'] ?? ''));
        $out[] = [
            'name' => $name,
            'image_url' => trim((string) ($row['image_url'] ?? '')),
            'm2_min' => isset($row['m2_min']) && $row['m2_min'] !== '' ? (float) $row['m2_min'] : null,
            'm2_max' => isset($row['m2_max']) && $row['m2_max'] !== '' ? (float) $row['m2_max'] : null,
            'dorms' => isset($row['dorms']) && $row['dorms'] !== '' ? (int) $row['dorms'] : null,
            'baths' => isset($row['baths']) && $row['baths'] !== '' ? (int) $row['baths'] : null,
        ];
    }
    return $out;
}

function rentti_tipologias_from_request(array $src): string
{
    $raw = $src['tipologia'] ?? null;
    if (!is_array($raw)) {
        // keep existing if editor not present
        return (string) ($src['tipologias_json'] ?? '[]');
    }
    $out = [];
    foreach ($raw as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = trim((string) ($row['name'] ?? ''));
        $image = trim((string) ($row['image_url'] ?? ''));
        $m2Min = rentti_nullable_float($row['m2_min'] ?? null);
        $m2Max = rentti_nullable_float($row['m2_max'] ?? null);
        $dorms = rentti_nullable_int($row['dorms'] ?? null);
        $baths = rentti_nullable_int($row['baths'] ?? null);
        if ($name === '' && $image === '' && $m2Min === null && $m2Max === null && $dorms === null && $baths === null) {
            continue;
        }
        $out[] = [
            'name' => $name !== '' ? $name : 'Modelo',
            'image_url' => $image,
            'm2_min' => $m2Min,
            'm2_max' => $m2Max,
            'dorms' => $dorms,
            'baths' => $baths,
        ];
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE);
}

function rentti_property_has_project_summary(array $p): bool
{
    return ($p['delivery_label'] ?? '') !== ''
        || ($p['project_name'] ?? '') !== ''
        || ($p['price_from_uf'] !== null && $p['price_from_uf'] !== '')
        || ($p['promo_badge'] ?? '') !== ''
        || ($p['promo_line_1'] ?? '') !== ''
        || ($p['promo_line_2'] ?? '') !== ''
        || ($p['summary_dorms_min'] !== null && $p['summary_dorms_min'] !== '')
        || ($p['summary_dorms_max'] !== null && $p['summary_dorms_max'] !== '')
        || ($p['summary_baths_min'] !== null && $p['summary_baths_min'] !== '')
        || ($p['summary_baths_max'] !== null && $p['summary_baths_max'] !== '')
        || ($p['summary_m2_min'] !== null && $p['summary_m2_min'] !== '')
        || ($p['summary_m2_max'] !== null && $p['summary_m2_max'] !== '');
}

function rentti_demo_tipologias(): array
{
    return [
        [
            'name' => 'Modelo 1D',
            'image_url' => '/assets/listings/kitchen.jpg',
            'm2_min' => 30.7,
            'm2_max' => 51.39,
            'dorms' => 1,
            'baths' => 1,
        ],
        [
            'name' => 'Modelo 2D',
            'image_url' => '/assets/listings/living.jpg',
            'm2_min' => 39.2,
            'm2_max' => 47.2,
            'dorms' => 2,
            'baths' => 1,
        ],
        [
            'name' => 'Modelo 2D2B',
            'image_url' => '/assets/listings/terrace.jpg',
            'm2_min' => 46.1,
            'm2_max' => 61.6,
            'dorms' => 2,
            'baths' => 2,
        ],
    ];
}
