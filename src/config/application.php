<?php

/**
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * A good default policy is to deviate from the production config as little as
 * possible. Try to define as much of your configuration in this file as you
 * can.
 */

use function Env\env;

use Roots\WPConfig\Config;

// USE_ENV_ARRAY + CONVERT_* + STRIP_QUOTES
Env\Env::$options = 31;

/**
 * Directory containing all the site's files
 */
$root_dir = dirname(__DIR__);

/**
 * Document Root
 */
$webroot_dir = $root_dir . '/web';
define('WEBROOT_DIR', $webroot_dir);

/**
 * Use Dotenv to set required environment variables and load .env file in root
 * .env.local will override .env if it exists
 */
if (file_exists($root_dir . '/.env')) {
    $env_files = file_exists($root_dir . '/.env.local')
        ? ['.env', '.env.local']
        : ['.env'];

    $repository = Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\EnvConstAdapter::class)
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->immutable()
        ->make();

    $dotenv = Dotenv\Dotenv::create($repository, $root_dir, $env_files, false);
    $dotenv->load();

    $dotenv->required(['WP_HOME', 'WP_SITEURL']);
    if (! env('DATABASE_URL')) {
        $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

/**
 * Infer WP_ENVIRONMENT_TYPE based on WP_ENV
 */
if (! env('WP_ENVIRONMENT_TYPE') && in_array(WP_ENV, ['production', 'staging', 'development', 'local'])) {
    Config::define('WP_ENVIRONMENT_TYPE', WP_ENV);
}

/**
 * URLs
 */
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 */
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

/**
 * DB settings
 */
if (env('DB_SSL')) {
    Config::define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);
}

Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD'));
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');
$table_prefix = env('DB_PREFIX') ?: 'wp_';

if (env('DATABASE_URL')) {
    $dsn = (object) parse_url(env('DATABASE_URL'));

    Config::define('DB_NAME', substr($dsn->path, 1));
    Config::define('DB_USER', $dsn->user);
    Config::define('DB_PASSWORD', $dsn->pass ?? null);
    Config::define('DB_HOST', isset($dsn->port) ? "{$dsn->host}:{$dsn->port}" : $dsn->host);
}

/**
 * Authentication Unique Keys and Salts
 */
Config::define('AUTH_KEY', env('AUTH_KEY'));
Config::define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
Config::define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
Config::define('NONCE_KEY', env('NONCE_KEY'));
Config::define('AUTH_SALT', env('AUTH_SALT'));
Config::define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
Config::define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
Config::define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */

/** SSL */
Config::define('FORCE_SSL_ADMIN', env('FORCE_SSL_ADMIN') ?? false);

// Disable the plugin and theme file editor in the admin and
// disable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_EDIT', true);
Config::define('DISALLOW_FILE_MODS', true);

Config::define('AUTOMATIC_UPDATER_DISABLED', env('AUTOMATIC_UPDATER_DISABLED') ?? false);
Config::define('WP_AUTO_UPDATE_CORE', env('WP_AUTO_UPDATE_CORE') ?? false);

Config::define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?? false);

Config::define('WP_POST_REVISIONS', env('WP_POST_REVISIONS') ?? true);
Config::define('EMPTY_TRASH_DAYS', env('EMPTY_TRASH_DAYS') ?? 30);
Config::define('AUTOSAVE_INTERVAL', env('AUTOSAVE_INTERVAL') ?? 180);

/** PHP Memory */
Config::define('WP_MEMORY_LIMIT', env('WP_MEMORY_LIMIT') ?? '512M');
Config::define('WP_MAX_MEMORY_LIMIT', env('WP_MAX_MEMORY_LIMIT') ?? '512M');

// Disable script concatenation
Config::define('CONCATENATE_SCRIPTS', false);

/** DISABLED_PLUGINS */
Config::define('DISABLED_PLUGINS', [
    //'wp-rocket/wp-rocket.php'
]);

/**
 * Debugging Settings
 */
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('SCRIPT_DEBUG', false);

ini_set('display_errors', '0');

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or a load balancer
 * See https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/** session.cookie_secure */
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    @ini_set('session.cookie_secure', '1');
}

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}

Config::apply();

/** FS_METHOD */
if (! defined('FS_METHOD')) {
    define('FS_METHOD', 'direct');
}

/**
 * Bootstrap WordPress
 */
if (! defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
