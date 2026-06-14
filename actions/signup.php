<?php
/**
 * Signup handler. POST only. Creates a user, logs them in, goes to dashboard.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/signup.php');
}

csrf_check();

$name     = post('name');
$email    = post('email');
$password = post('password');
$confirm  = post('confirm_password');

old_set(['name' => $name, 'email' => $email]);

// Validate
if ($name === '' || $email === '' || $password === '') {
	flash_set('error', 'All fields are required.');
	redirect('../pages/signup.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	flash_set('error', 'Please enter a valid email address.');
	redirect('../pages/signup.php');
}
if (strlen($password) < 6) {
	flash_set('error', 'Password must be at least 6 characters.');
	redirect('../pages/signup.php');
}
if ($password !== $confirm) {
	flash_set('error', 'Passwords do not match.');
	redirect('../pages/signup.php');
}

// Email already registered?
$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
	flash_set('error', 'An account with this email already exists.');
	redirect('../pages/signup.php');
}

// Create user
$hash = password_hash($password, PASSWORD_DEFAULT);
$insert = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$insert->execute([$name, $email, $hash]);

login_user([
	'id'    => db()->lastInsertId(),
	'name'  => $name,
	'email' => $email,
]);

old_clear();
redirect('../pages/dashboard.php');
