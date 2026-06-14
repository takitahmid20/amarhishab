<?php
/**
 * Login handler. POST only. Verifies credentials and starts a session.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/login.php');
}

csrf_check();

$email    = post('email');
$password = post('password');

old_set(['email' => $email]);

if ($email === '' || $password === '') {
	flash_set('error', 'Email and password are required.');
	redirect('../pages/login.php');
}

$stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
	flash_set('error', 'Invalid email or password.');
	redirect('../pages/login.php');
}

login_user($user);
old_clear();
redirect('../pages/dashboard.php');
