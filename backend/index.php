<?php
require __DIR__ . '/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$apiPrefix = '/api';
$path = $uri;
$prefixPos = strpos($uri, $apiPrefix);
if ($prefixPos !== false) {
    $path = substr($uri, $prefixPos + strlen($apiPrefix));
}
$path = rtrim($path, '/');

switch ($path) {
    case '/signup':
        handleSignup($pdo);
        break;
    case '/login':
        handleLogin($pdo);
        break;
    case '/logout':
        handleLogout($pdo);
        break;
    case '':
    case '/':
        sendJson(['success' => false, 'message' => 'API root. Use /api/signup, /api/login, or /api/logout'], 400);
        break;
    default:
        sendJson(['success' => false, 'message' => 'Not Found'], 404);
}

function findUserByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function findUserByToken(PDO $pdo, string $token): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE api_token = :token LIMIT 1');
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function getUserResponse(array $user): array
{
    return [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'created_at' => $user['created_at'],
    ];
}

function handleSignup(PDO $pdo): void
{
    $data = getJsonInput();
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['password_confirmation'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        sendJson(['success' => false, 'message' => 'All fields are required'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJson(['success' => false, 'message' => 'Please provide a valid email address'], 400);
    }

    if ($password !== $confirmPassword) {
        sendJson(['success' => false, 'message' => 'Passwords do not match'], 400);
    }

    if (findUserByEmail($pdo, $email) !== null) {
        sendJson(['success' => false, 'message' => 'Email is already registered'], 400);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $token = generateToken();

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, api_token) VALUES (:name, :email, :password_hash, :api_token)');
    try {
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'api_token' => $token,
        ]);
    } catch (PDOException $e) {
        sendJson(['success' => false, 'message' => 'Could not create account'], 500);
    }

    $user = findUserByEmail($pdo, $email);
    sendJson([
        'success' => true,
        'token' => $token,
        'user' => getUserResponse($user),
    ]);
}

function handleLogin(PDO $pdo): void
{
    $data = getJsonInput();
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if ($email === '' || $password === '') {
        sendJson(['success' => false, 'message' => 'Email and password are required'], 400);
    }

    $user = findUserByEmail($pdo, $email);
    if ($user === null || !password_verify($password, $user['password_hash'])) {
        sendJson(['success' => false, 'message' => 'Invalid email or password'], 401);
    }

    $token = generateToken();
    $stmt = $pdo->prepare('UPDATE users SET api_token = :token WHERE id = :id');
    $stmt->execute(['token' => $token, 'id' => $user['id']]);

    $user['api_token'] = $token;
    sendJson([
        'success' => true,
        'token' => $token,
        'user' => getUserResponse($user),
    ]);
}

function handleLogout(PDO $pdo): void
{
    $token = getBearerToken();
    if ($token === null) {
        sendJson(['success' => false, 'message' => 'Missing auth token'], 401);
    }

    $user = findUserByToken($pdo, $token);
    if ($user === null) {
        sendJson(['success' => false, 'message' => 'Invalid auth token'], 401);
    }

    $stmt = $pdo->prepare('UPDATE users SET api_token = NULL WHERE id = :id');
    $stmt->execute(['id' => $user['id']]);
    sendJson(['success' => true, 'message' => 'Logged out successfully']);
}
