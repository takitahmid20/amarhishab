<?php
/**
 * Database + app config template.
 *
 * Copy this file to `config.local.php` and adjust for your machine.
 * `config.local.php` is gitignored so local credentials never get committed.
 */

return [
	'db' => [
		'host'    => '127.0.0.1',
		'port'    => '3306',
		'name'    => 'amarhishab',
		'user'    => 'root',
		'pass'    => '',
		'charset' => 'utf8mb4',
	],
	'app' => [
		'name'     => 'AmarHishab',
		'base_url' => 'http://localhost/amarhishab',
	],
	// HisabAI — Google Gemini API key (https://aistudio.google.com/app/apikey).
	// Can also be supplied via the GEMINI_API_KEY environment variable.
	'ai' => [
		'key'   => '',
		'model' => 'gemini-2.5-flash',
	],
];
