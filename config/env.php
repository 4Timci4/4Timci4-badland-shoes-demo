<?php

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(sprintf('%s=%s', trim($key), trim($value)));
        }
    }
}

define('DB_TYPE', getenv('DB_TYPE') ?: 'supabase');

define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'SUPABASE_URL');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'SUPABASE_KEY');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'bandland_shoes');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

define('APP_ENV', getenv('APP_ENV') ?: 'development');

define('MAIL_HOST', getenv('MAIL_HOST') ?: 'mail.badlandshoes.com.tr');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: 'mail@badlandshoes.com.tr');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: 'Parola28!');
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'mail@badlandshoes.com.tr');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Application');

function debug_connection_info()
{
    if (APP_ENV !== 'development') {
        return;
    }

    $url_parts = parse_url(SUPABASE_URL);
    $masked_key = substr(SUPABASE_KEY, 0, 5) . '...' . substr(SUPABASE_KEY, -5);

}
