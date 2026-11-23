<?php
declare(strict_types=1);

function rentti_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = RENTTI_ROOT . '/data/rentti.sqlite';
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    rentti_migrate($pdo);
    return $pdo;
}

function rentti_migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS properties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT NOT NULL UNIQUE,
            property_type TEXT NOT NULL DEFAULT \'departamento\',
            operation TEXT NOT NULL DEFAULT \'arriendo\',
            status TEXT NOT NULL DEFAULT \'borrador\',
            featured INTEGER NOT NULL DEFAULT 0,
            seasonal_rent INTEGER NOT NULL DEFAULT 0,
            rent_amount REAL,
            rent_currency TEXT NOT NULL DEFAULT \'CLP\',
            common_expenses REAL,
            region TEXT NOT NULL DEFAULT \'Metropolitana\',
            commune TEXT NOT NULL DEFAULT \'\',
            sector TEXT,
            street TEXT,
            street_number TEXT,
            unit TEXT,
            referential_address TEXT NOT NULL DEFAULT \'\',
            lat REAL,
            lng REAL,
            show_map INTEGER NOT NULL DEFAULT 0,
            bedrooms INTEGER,
            bathrooms INTEGER,
            suites INTEGER,
            living_rooms INTEGER,
            useful_area REAL,
            total_area REAL,
            land_area REAL,
            built_area REAL,
            terrace_area REAL,
            floor_number TEXT,
            year_built INTEGER,
            floors INTEGER,
            elevators INTEGER,
            parking_covered INTEGER,
            parking_open INTEGER,
            storage INTEGER NOT NULL DEFAULT 0,
            storage_price_uf REAL,
            parking_price_uf REAL,
            parking_note TEXT,
            furnished INTEGER NOT NULL DEFAULT 0,
            pets_allowed INTEGER NOT NULL DEFAULT 0,
            pool INTEGER NOT NULL DEFAULT 0,
            condominium INTEGER NOT NULL DEFAULT 0,
            feat_termopanel INTEGER NOT NULL DEFAULT 0,
            feat_cocina_equipada INTEGER NOT NULL DEFAULT 0,
            feat_gres_porcelanato INTEGER NOT NULL DEFAULT 0,
            feat_revestimiento_eifs INTEGER NOT NULL DEFAULT 0,
            feat_piso_fotolaminado INTEGER NOT NULL DEFAULT 0,
            feat_cocina_integrada INTEGER NOT NULL DEFAULT 0,
            amenity_salon_gourmet INTEGER NOT NULL DEFAULT 0,
            amenity_conserjeria INTEGER NOT NULL DEFAULT 0,
            amenity_ecommerce INTEGER NOT NULL DEFAULT 0,
            amenity_bicicletero INTEGER NOT NULL DEFAULT 0,
            amenity_gimnasio INTEGER NOT NULL DEFAULT 0,
            amenity_cowork INTEGER NOT NULL DEFAULT 0,
            orientation TEXT,
            heating_type TEXT,
            title TEXT,
            description TEXT,
            visit_instructions TEXT,
            internal_notes TEXT,
            owner_doc_type TEXT DEFAULT \'RUT\',
            owner_doc_number TEXT,
            owner_first_name TEXT,
            owner_last_name TEXT,
            owner_maternal_last_name TEXT,
            owner_email TEXT,
            owner_phone TEXT,
            owner_mobile TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            published_at TEXT
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS property_photos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_cover INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS property_nearby_points (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id INTEGER NOT NULL,
            category TEXT NOT NULL DEFAULT \'transporte\',
            subtype TEXT,
            name TEXT NOT NULL,
            distance_m INTEGER,
            walk_min INTEGER,
            drive_min INTEGER,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
        )'
    );

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_properties_status ON properties(status)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_properties_type ON properties(property_type)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_properties_commune ON properties(commune)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_photos_property ON property_photos(property_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_nearby_property ON property_nearby_points(property_id)');

    rentti_ensure_columns($pdo, 'properties', [
        'storage_price_uf' => 'REAL',
        'parking_price_uf' => 'REAL',
        'parking_note' => 'TEXT',
        'feat_termopanel' => 'INTEGER NOT NULL DEFAULT 0',
        'feat_cocina_equipada' => 'INTEGER NOT NULL DEFAULT 0',
        'feat_gres_porcelanato' => 'INTEGER NOT NULL DEFAULT 0',
        'feat_revestimiento_eifs' => 'INTEGER NOT NULL DEFAULT 0',
        'feat_piso_fotolaminado' => 'INTEGER NOT NULL DEFAULT 0',
        'feat_cocina_integrada' => 'INTEGER NOT NULL DEFAULT 0',
        'amenity_salon_gourmet' => 'INTEGER NOT NULL DEFAULT 0',
        'amenity_conserjeria' => 'INTEGER NOT NULL DEFAULT 0',
        'amenity_ecommerce' => 'INTEGER NOT NULL DEFAULT 0',
        'amenity_bicicletero' => 'INTEGER NOT NULL DEFAULT 0',
        'amenity_gimnasio' => 'INTEGER NOT NULL DEFAULT 0',
        'amenity_cowork' => 'INTEGER NOT NULL DEFAULT 0',
        'delivery_label' => 'TEXT',
        'project_name' => 'TEXT',
        'price_from_uf' => 'REAL',
        'promo_badge' => 'TEXT',
        'promo_line_1' => 'TEXT',
        'promo_line_2' => 'TEXT',
        'summary_dorms_min' => 'INTEGER',
        'summary_dorms_max' => 'INTEGER',
        'summary_baths_min' => 'INTEGER',
        'summary_baths_max' => 'INTEGER',
        'summary_m2_min' => 'REAL',
        'summary_m2_max' => 'REAL',
        'tipologias_json' => 'TEXT',
    ]);
}

function rentti_ensure_columns(PDO $pdo, string $table, array $columns): void
{
    $existing = [];
    foreach ($pdo->query("PRAGMA table_info($table)") as $row) {
        $existing[(string) $row['name']] = true;
    }
    foreach ($columns as $name => $definition) {
        if (isset($existing[$name])) {
            continue;
        }
        $pdo->exec("ALTER TABLE $table ADD COLUMN $name $definition");
    }
}
