<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rentti_now(): string
{
    return (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
}

function rentti_redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function rentti_flash(string $type, string $message): void
{
    rentti_session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function rentti_take_flash(): ?array
{
    rentti_session_start();
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function rentti_csrf_token(): string
{
    rentti_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function rentti_verify_csrf(?string $token): bool
{
    rentti_session_start();
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals((string) $_SESSION['csrf'], $token);
}

function rentti_format_price(?float $amount, string $currency): string
{
    if ($amount === null) {
        return '—';
    }
    if ($currency === 'UF') {
        return 'UF ' . number_format($amount, 2, ',', '.');
    }
    return '$' . number_format($amount, 0, ',', '.');
}

function rentti_price_both(?float $amount, string $currency): string
{
    if ($amount === null) {
        return '—';
    }
    $cfg = rentti_config();
    $uf = (float) $cfg['uf_clp'];
    if ($currency === 'UF') {
        $clp = $amount * $uf;
        return rentti_format_price($amount, 'UF') . ' · ' . rentti_format_price($clp, 'CLP');
    }
    if ($uf > 0) {
        $asUf = $amount / $uf;
        return rentti_format_price($amount, 'CLP') . ' · ' . rentti_format_price($asUf, 'UF');
    }
    return rentti_format_price($amount, $currency);
}

function rentti_type_label(string $type): string
{
    return rentti_property_types()[$type] ?? $type;
}

function rentti_status_label(string $status): string
{
    return rentti_statuses()[$status] ?? $status;
}

function rentti_photo_url(string $filename): string
{
    return '/uploads/properties/' . rawurlencode($filename);
}

function rentti_request_is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
