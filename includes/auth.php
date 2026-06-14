<?php
/**
 * Session-based auth helpers. Wired up fully in the auth phase.
 */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

/** Currently logged-in user array, or null. */
function current_user(): ?array
{
	return $_SESSION['user'] ?? null;
}

/** True if a user session exists. */
function is_logged_in(): bool
{
	return current_user() !== null;
}

/** Guard a page: redirect guests to login. */
function require_login(string $loginPath = './login.php'): void
{
	if (!is_logged_in()) {
		redirect($loginPath);
	}
}

/** Log a user in: store a minimal, safe subset in the session. */
function login_user(array $user): void
{
	session_regenerate_id(true);
	$_SESSION['user'] = [
		'id'    => (int) $user['id'],
		'name'  => $user['name'],
		'email' => $user['email'],
	];
}

/** Log the current user out and clear the session. */
function logout_user(): void
{
	$_SESSION = [];
	session_destroy();
}
