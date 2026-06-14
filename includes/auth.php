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
