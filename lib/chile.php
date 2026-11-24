<?php
declare(strict_types=1);

function rentti_regions(): array
{
    return [
        'Metropolitana',
        'Valparaíso',
        'Biobío',
        'La Araucanía',
        'Los Lagos',
        'Maule',
        'O\'Higgins',
        'Coquimbo',
        'Antofagasta',
        'Ñuble',
        'Los Ríos',
        'Tarapacá',
        'Atacama',
        'Arica y Parinacota',
        'Magallanes',
        'Aysén',
    ];
}

function rentti_communes_by_region(): array
{
    return [
        'Metropolitana' => [
            'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central',
            'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja',
            'La Pintana', 'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo',
            'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 'Pedro Aguirre Cerda',
            'Peñalolén', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal',
            'Recoleta', 'Renca', 'San Joaquín', 'San Miguel', 'San Ramón',
            'Santiago', 'Vitacura', 'Puente Alto', 'San Bernardo', 'Maipú',
            'Colina', 'Lampa', 'Buín', 'Paine', 'Melipilla', 'Talagante',
            'Peñaflor', 'Isla de Maipo', 'El Monte', 'Padre Hurtado',
        ],
        'Valparaíso' => [
            'Valparaíso', 'Viña del Mar', 'Concón', 'Quilpué', 'Villa Alemana',
            'Quillota', 'Limache', 'Olmué', 'San Antonio', 'Algarrobo',
        ],
        'Biobío' => ['Concepción', 'Talcahuano', 'San Pedro de la Paz', 'Chiguayante', 'Los Ángeles'],
        'La Araucanía' => ['Temuco', 'Padre Las Casas', 'Villarrica', 'Pucón'],
        'Los Lagos' => ['Puerto Montt', 'Puerto Varas', 'Osorno', 'Castro'],
        'Maule' => ['Talca', 'Curicó', 'Linares'],
        'O\'Higgins' => ['Rancagua', 'San Fernando'],
        'Coquimbo' => ['La Serena', 'Coquimbo', 'Ovalle'],
        'Antofagasta' => ['Antofagasta', 'Calama'],
        'Ñuble' => ['Chillán'],
        'Los Ríos' => ['Valdivia'],
        'Tarapacá' => ['Iquique'],
        'Atacama' => ['Copiapó'],
        'Arica y Parinacota' => ['Arica'],
        'Magallanes' => ['Punta Arenas'],
        'Aysén' => ['Coyhaique'],
    ];
}

function rentti_communes_for(string $region): array
{
    $map = rentti_communes_by_region();
    $list = $map[$region] ?? [];
    $list = array_values(array_unique($list));
    sort($list, SORT_STRING | SORT_FLAG_CASE);
    return $list;
}

function rentti_property_types(): array
{
    return [
        'casa' => 'Casa',
        'departamento' => 'Departamento',
    ];
}

function rentti_statuses(): array
{
    return [
        'borrador' => 'Borrador',
        'publicada' => 'Publicada',
        'no_disponible' => 'No disponible',
    ];
}

function rentti_currencies(): array
{
    return ['CLP' => 'CLP $', 'UF' => 'UF'];
}

function rentti_orientations(): array
{
    return [
        '', 'Norte', 'Sur', 'Oriente', 'Poniente',
        'Nor-Oriente', 'Nor-Poniente', 'Sur-Oriente', 'Sur-Poniente',
    ];
}

function rentti_heating_types(): array
{
    return [
        '', 'Ninguna', 'Eléctrica', 'Central', 'Caldera/Central',
        'Losa radiante', 'Estufa mural', 'Chimenea', 'Aire acondicionado',
    ];
}
