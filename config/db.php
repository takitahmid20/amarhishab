<?php
/**
 * PDO database connection.
 *
 * Loads config from config.local.php if present, otherwise falls back to
 * config.sample.php defaults (XAMPP: root / no password). Returns a single
 * shared PDO instance per request.
 */

function app_config(): array
{
	static $config = null;
	if ($config !== null) {
		return $config;
	}

	$local  = __DIR__ . '/config.local.php';
	$sample = __DIR__ . '/config.sample.php';
	$config = file_exists($local) ? require $local : require $sample;

	return $config;
}

function db(): PDO
{
	static $pdo = null;
	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$cfg = app_config()['db'];

	// Environment variables win when set (used by Docker); otherwise fall back
	// to the config file. pass uses !== false so an intentional empty password works.
	$host    = getenv('DB_HOST')    ?: $cfg['host'];
	$port    = getenv('DB_PORT')    ?: $cfg['port'];
	$name    = getenv('DB_NAME')    ?: $cfg['name'];
	$charset = getenv('DB_CHARSET') ?: $cfg['charset'];
	$user    = getenv('DB_USER')    ?: $cfg['user'];
	$pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : $cfg['pass'];

	$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

	$pdo = new PDO($dsn, $user, $pass, [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	]);

	return $pdo;
}
