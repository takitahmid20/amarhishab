<?php
/**
 * Shared view + request helpers.
 */

/** Escape a value for safe HTML output. */
function e(?string $value): string
{
	return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format an amount as Bangladeshi Taka, e.g. 4530 -> "৳ 4,530". */
function taka($amount): string
{
	$value = is_numeric($amount) ? (float) $amount : 0.0;
	return '৳ ' . number_format($value);
}

/** Redirect to a path and stop execution. */
function redirect(string $path): void
{
	header('Location: ' . $path);
	exit;
}

/** Read a trimmed POST field. */
function post(string $key, string $default = ''): string
{
	return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}
