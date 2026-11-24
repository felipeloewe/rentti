<?php
/**
 * Router for PHP built-in server: php -S 127.0.0.1:8080 router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false; // serve static as-is
}

if (is_dir($file)) {
    foreach (['index.php', 'index.html'] as $index) {
        $candidate = rtrim($file, '/') . '/' . $index;
        if (is_file($candidate)) {
            if (str_ends_with($candidate, '.php')) {
                require $candidate;
                return true;
            }
            return false;
        }
    }
}

if (is_file($file) && str_ends_with($file, '.php')) {
    require $file;
    return true;
}

http_response_code(404);
echo '404';
return true;
