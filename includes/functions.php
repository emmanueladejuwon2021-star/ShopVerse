<?php
/**
 * Shared helpers: escaping, money formatting, partial rendering, flash messages.
 */

declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function money(float|int|string $n): string
{
    return '$' . number_format((float) $n, 2);
}

/** URL builder that preserves current query params (used by Products filters). */
function url_with(array $overrides = []): string
{
    $query = array_merge($_GET, $overrides);
    $query = array_filter($query, fn($v) => $v !== '' && $v !== null);
    $base = strtok($_SERVER['REQUEST_URI'] ?? '/products', '?');
    return $base . ($query ? '?' . http_build_query($query) : '');
}

/** Render a view/partial file. */
function render(string $viewFile, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../views/' . $viewFile . '.php';
}

function render_page(string $page, array $params = []): void
{
    $titles = [
        'home' => 'ShopVerse — Premium Online Store',
        'products' => 'Shop — ShopVerse',
        'product_detail' => 'Product — ShopVerse',
        'cart' => 'Your Cart — ShopVerse',
        'checkout' => 'Checkout — ShopVerse',
        'login' => 'Sign In — ShopVerse',
        'orders' => 'My Orders — ShopVerse',
        'account' => 'Account Settings — ShopVerse',
    ];

    render('layout', [
        'page' => $page,
        'params' => $params,
        'title' => $titles[$page] ?? 'ShopVerse',
    ]);
}

function flash(): ?string
{
    $msg = $_SESSION['flash'] ?? null;
    $err = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash'], $_SESSION['flash_error']);
    return $msg ?? $err;
}

/**
 * Open-redirect protection — only same-site absolute paths are allowed
 * as targets for user-controlled ?redirect= parameters.
 */
function validate_redirect(string $url): string
{
    if ($url === '' || !str_starts_with($url, '/') || str_starts_with($url, '//')) {
        return '/';
    }
    return $url;
}

/** Build the /login URL preserving redirect target + tab mode. */
function login_url(string $redirectTo = '/', string $mode = 'login'): string
{
    $q = ['mode' => $mode];
    if ($redirectTo !== '/') {
        $q['redirect'] = $redirectTo;
    }
    return '/login?' . http_build_query($q);
}

function stars_html(float $rating): string
{
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $out .= '<svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        } elseif ($rating >= $i - 0.5) {
            $out .= '<svg class="w-4 h-4 text-yellow-400" viewBox="0 0 20 20"><defs><linearGradient id="half' . $i . '"><stop offset="50%" stop-color="#facc15"/><stop offset="50%" stop-color="#d1d5db"/></linearGradient></defs><path fill="url(#half' . $i . ')" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        } else {
            $out .= '<svg class="w-4 h-4 text-gray-300 dark:text-gray-600" viewBox="0 0 20 20"><path fill="currentColor" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
    }
    return $out;
}
