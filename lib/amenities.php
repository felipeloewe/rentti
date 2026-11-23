<?php
declare(strict_types=1);

/**
 * Amenity / feature catalog for admin pickers and public ficha.
 * Short labels + icon keys; prefer drawings over dense copy.
 */

function rentti_feature_bool_keys(): array
{
    return array_keys(rentti_unit_features());
}

function rentti_common_amenity_keys(): array
{
    return array_keys(rentti_common_amenities());
}

function rentti_all_amenity_bool_keys(): array
{
    return array_merge(
        rentti_feature_bool_keys(),
        rentti_common_amenity_keys(),
        ['storage', 'furnished', 'pets_allowed', 'pool', 'condominium']
    );
}

/** Unit-level características (checkmark list on ficha). */
function rentti_unit_features(): array
{
    return [
        'feat_termopanel' => [
            'label' => 'Termopanel',
            'hint' => 'Ventanas con termopanel',
            'icon' => 'window',
        ],
        'feat_cocina_equipada' => [
            'label' => 'Cocina equipada',
            'hint' => 'Horno, encimera y campana',
            'icon' => 'kitchen',
        ],
        'feat_gres_porcelanato' => [
            'label' => 'Gres porcelanato',
            'hint' => 'Baños y terraza',
            'icon' => 'tiles',
        ],
        'feat_revestimiento_eifs' => [
            'label' => 'Revestimiento EIFS',
            'hint' => 'Exterior EIFS',
            'icon' => 'facade',
        ],
        'feat_piso_fotolaminado' => [
            'label' => 'Piso fotolaminado',
            'hint' => '',
            'icon' => 'floor',
        ],
        'feat_cocina_integrada' => [
            'label' => 'Cocina integrada',
            'hint' => '',
            'icon' => 'open-kitchen',
        ],
    ];
}

/** Building common areas (icon grid on ficha). */
function rentti_common_amenities(): array
{
    return [
        'amenity_salon_gourmet' => [
            'label' => 'Salón Gourmet',
            'hint' => '',
            'icon' => 'chef',
        ],
        'amenity_conserjeria' => [
            'label' => 'Conserjería',
            'hint' => '',
            'icon' => 'concierge',
        ],
        'amenity_ecommerce' => [
            'label' => 'Sala E-Commerce',
            'hint' => '',
            'icon' => 'box',
        ],
        'amenity_bicicletero' => [
            'label' => 'Bicicletero',
            'hint' => '',
            'icon' => 'bike',
        ],
        'amenity_gimnasio' => [
            'label' => 'Gimnasio',
            'hint' => '',
            'icon' => 'gym',
        ],
        'amenity_cowork' => [
            'label' => 'Espacio Co-Work',
            'hint' => '',
            'icon' => 'cowork',
        ],
    ];
}

/** Extra lifestyle toggles kept from MVP. */
function rentti_extra_flags(): array
{
    return [
        'furnished' => [
            'label' => 'Amoblado',
            'hint' => '',
            'icon' => 'sofa',
        ],
        'pets_allowed' => [
            'label' => 'Mascotas',
            'hint' => '',
            'icon' => 'paw',
        ],
        'pool' => [
            'label' => 'Piscina',
            'hint' => '',
            'icon' => 'pool',
        ],
        'condominium' => [
            'label' => 'Condominio',
            'hint' => '',
            'icon' => 'building',
        ],
    ];
}

function rentti_amenity_icon_svg(string $icon, string $class = 'amenity-icon'): string
{
    $stroke = 'currentColor';
    $common = 'fill="none" stroke="' . $stroke . '" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"';
    $paths = match ($icon) {
        'window' => '<rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="M12 5v14M4 12h16"/>',
        'kitchen' => '<path d="M5 20V10l3-5h8l3 5v10"/><path d="M9 20v-4h6v4"/><path d="M8 10h8"/>',
        'tiles' => '<rect x="4" y="4" width="7" height="7" rx="0.5"/><rect x="13" y="4" width="7" height="7" rx="0.5"/><rect x="4" y="13" width="7" height="7" rx="0.5"/><rect x="13" y="13" width="7" height="7" rx="0.5"/>',
        'facade' => '<path d="M4 20V8l8-4 8 4v12"/><path d="M9 20v-6h6v6"/><path d="M4 11h16"/>',
        'floor' => '<path d="M3 17l9-5 9 5-9 5-9-5z"/><path d="M3 12l9-5 9 5"/>',
        'open-kitchen' => '<path d="M4 20V9h16v11"/><path d="M4 13h16"/><path d="M8 9V6h3"/><circle cx="16" cy="16" r="1.2"/>',
        'chef' => '<path d="M8 10c0-2.2 1.8-4 4-4s4 1.8 4 4"/><path d="M7 10h10v2a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-2z"/><path d="M9 14v6h6v-6"/>',
        'concierge' => '<circle cx="12" cy="8" r="3"/><path d="M5 20c1.5-3.5 4-5 7-5s5.5 1.5 7 5"/><path d="M9 5.5 12 3l3 2.5"/>',
        'box' => '<path d="M4 8l8-4 8 4v9l-8 4-8-4V8z"/><path d="M4 8l8 4 8-4M12 12v9"/>',
        'bike' => '<circle cx="6.5" cy="16" r="3"/><circle cx="17.5" cy="16" r="3"/><path d="M6.5 16 10 9h4l3.5 7M10 9l2 7h3"/>',
        'gym' => '<path d="M3 12h3M18 12h3"/><path d="M6 9v6M18 9v6"/><path d="M8 12h8"/><path d="M8 10v4M16 10v4"/>',
        'cowork' => '<rect x="3" y="5" width="10" height="7" rx="1"/><path d="M6 16h4M8 12v4"/><path d="M15 10h5v9H9v-3"/><circle cx="17.5" cy="7" r="1"/>',
        'sofa' => '<path d="M4 14v4h16v-4"/><path d="M4 14a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2"/><path d="M6 12V9a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v3"/>',
        'paw' => '<circle cx="8" cy="9" r="1.6"/><circle cx="12" cy="7.5" r="1.6"/><circle cx="16" cy="9" r="1.6"/><path d="M8.5 15c1.2-2 2.8-2.5 3.5-2.5S14.3 13 15.5 15c.4.7-.2 1.5-1 1.5H9.5c-.8 0-1.4-.8-1-1.5z"/>',
        'pool' => '<path d="M4 14c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0 3-1 4.5 0"/><path d="M4 18c1.5 1 3 1 4.5 0s3-1 4.5 0 3 1 4.5 0 3-1 4.5 0"/><path d="M7 6v6M12 4v8M17 7v5"/>',
        'building' => '<rect x="5" y="4" width="14" height="16" rx="1"/><path d="M9 8h2M13 8h2M9 12h2M13 12h2M9 16h6"/>',
        'parking' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 16V8h4a3 3 0 0 1 0 6H9z"/>',
        'storage' => '<path d="M4 8h16v12H4z"/><path d="M4 8l8-4 8 4"/><path d="M10 14h4"/>',
        'check' => '<path d="M5 12.5 9.5 17 19 7.5"/>',
        default => '<circle cx="12" cy="12" r="7"/>',
    };

    return '<svg class="' . h($class) . '" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" ' . $common . '>' . $paths . '</svg>';
}

function rentti_active_unit_features(array $property): array
{
    $out = [];
    foreach (rentti_unit_features() as $key => $meta) {
        if (!empty($property[$key])) {
            $out[$key] = $meta;
        }
    }
    return $out;
}

function rentti_active_common_amenities(array $property): array
{
    $out = [];
    foreach (rentti_common_amenities() as $key => $meta) {
        if (!empty($property[$key])) {
            $out[$key] = $meta;
        }
    }
    return $out;
}
