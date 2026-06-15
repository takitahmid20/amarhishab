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

	$options = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];

	// Enable TLS when DB_SSL is truthy or config ['db']['ssl'] is set — required
	// for managed cloud databases like Aiven (ssl-mode=REQUIRED).
	$ssl = getenv('DB_SSL') !== false ? getenv('DB_SSL') : ($cfg['ssl'] ?? false);
	if (filter_var($ssl, FILTER_VALIDATE_BOOLEAN)) {
		$options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
		$options[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA') ?: ($cfg['ssl_ca'] ?? null);
		if (empty($options[PDO::MYSQL_ATTR_SSL_CA])) {
			unset($options[PDO::MYSQL_ATTR_SSL_CA]);
		}
	}

	$pdo = new PDO($dsn, $user, $pass, $options);

	return $pdo;
}
