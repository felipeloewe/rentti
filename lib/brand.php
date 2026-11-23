<?php
declare(strict_types=1);

/** Favicon + apple-touch link tags for public/admin pages. */
function rentti_brand_head_tags(): void
{
    ?>
  <link rel="icon" href="/assets/brand/favicon.ico" sizes="any" />
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/brand/favicon-32.png" />
  <link rel="icon" type="image/svg+xml" href="/assets/brand/logo-mark.svg" />
  <link rel="apple-touch-icon" href="/assets/brand/apple-touch-icon.png" />
    <?php
}

/**
 * Logo markup.
 * $variant: 'coral' (light UI), 'on-dark' (hero/dark), 'navy' (formal stacked mark word)
 * $layout: 'horizontal' | 'stacked' | 'mark'
 */
function rentti_brand_logo(string $variant = 'coral', string $layout = 'horizontal', string $class = 'brand-logo'): string
{
    if ($layout === 'mark') {
        $src = '/assets/brand/logo-mark.svg';
    } elseif ($layout === 'stacked') {
        $src = $variant === 'navy'
            ? '/assets/brand/logo-stacked-navy.svg'
            : '/assets/brand/logo-stacked.svg';
    } else {
        $src = $variant === 'on-dark'
            ? '/assets/brand/logo-horizontal-on-dark.svg'
            : '/assets/brand/logo-horizontal.svg';
    }
    return '<img class="' . h($class) . '" src="' . h($src) . '" alt="RENTTI" width="140" height="32" decoding="async" />';
}
