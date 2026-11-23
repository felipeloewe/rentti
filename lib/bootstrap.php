<?php
declare(strict_types=1);

const RENTTI_ROOT = __DIR__ . '/..';

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chile.php';
require_once __DIR__ . '/amenities.php';
require_once __DIR__ . '/zone.php';
require_once __DIR__ . '/project.php';
require_once __DIR__ . '/brand.php';
require_once __DIR__ . '/properties.php';
require_once __DIR__ . '/helpers.php';

rentti_ensure_storage();
rentti_db(); // migrate on first hit
