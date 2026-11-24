<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/bootstrap.php';

if (rentti_request_is_post() && rentti_verify_csrf($_POST['csrf'] ?? null)) {
    rentti_logout();
}
rentti_redirect('/admin/login.php');
