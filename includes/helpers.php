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

/** Human-friendly relative time, e.g. "about 2 hours ago". */
function time_ago(string $datetime): string
{
	$ts   = strtotime($datetime);
	$diff = time() - $ts;

	if ($diff < 60)        return 'just now';
	if ($diff < 3600)      return 'about ' . (int) round($diff / 60) . ' minutes ago';
	if ($diff < 86400)     return 'about ' . (int) round($diff / 3600) . ' hours ago';
	if ($diff < 2592000)   return (int) round($diff / 86400) . ' days ago';
	return date('M j, Y', $ts);
}

/** Read a trimmed POST field. */
function post(string $key, string $default = ''): string
{
	return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

/* ---- Flash messages (one-shot, survive a redirect) -------------------- */

/** Store a flash message under a key (e.g. 'error', 'success'). */
function flash_set(string $key, string $message): void
{
	$_SESSION['_flash'][$key] = $message;
}

/** Read and clear a flash message. Returns '' when none. */
function flash_get(string $key): string
{
	$message = $_SESSION['_flash'][$key] ?? '';
	unset($_SESSION['_flash'][$key]);
	return $message;
}

/* ---- Old form input (repopulate fields after a failed submit) --------- */

/** Stash submitted values so the form can be re-rendered with them. */
function old_set(array $values): void
{
	$_SESSION['_old'] = $values;
}

/** Read a previously stashed value (does not clear; cleared on next page load). */
function old(string $key, string $default = ''): string
{
	return $_SESSION['_old'][$key] ?? $default;
}

/** Clear stashed input — call once at the top of a form page. */
function old_clear(): void
{
	unset($_SESSION['_old']);
}

/* ---- CSRF -------------------------------------------------------------- */

/** Current CSRF token, generated once per session. */
function csrf_token(): string
{
	if (empty($_SESSION['_csrf'])) {
		$_SESSION['_csrf'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['_csrf'];
}

/** Hidden input markup for forms. */
function csrf_field(): string
{
	return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Validate the posted token; abort on mismatch. */
function csrf_check(): void
{
	$token = $_POST['_csrf'] ?? '';
	if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
		http_response_code(403);
		exit('Invalid or expired form token. Go back and try again.');
	}
}
