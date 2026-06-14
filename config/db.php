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
	$dsn = sprintf(
		'mysql:host=%s;port=%s;dbname=%s;charset=%s',
		$cfg['host'],
		$cfg['port'],
		$cfg['name'],
		$cfg['charset']
	);

	$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	]);

	return $pdo;
}
