<?php
/**
 * One-time database bootstrap for the Turso backend.
 * Usage: TURSO_DB_URL=libsql://... TURSO_AUTH_TOKEN=... php scripts/init-db.php
 * (The SQLite dev fallback auto-creates its schema on first request.)
 */
declare(strict_types=1);

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (!defined('STORAGE_DIR')) define('STORAGE_DIR', __DIR__ . '/../storage');

if (getenv('TURSO_DB_URL') === false) {
    fwrite(STDERR, "TURSO_DB_URL not set — nothing to do (SQLite fallback self-migrates).\n");
    exit(1);
}
db_migrate();
echo "Turso schema created.\n";
