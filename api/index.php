<?php
/**
 * ShopVerse — Vercel Serverless Function entry point.
 *
 * Vercel only discovers PHP serverless functions inside /api, so this thin
 * wrapper forwards every routed request to the app's front controller
 * (public/index.php). All routing logic stays in one place.
 */

declare(strict_types=1);

require __DIR__ . '/../public/index.php';
