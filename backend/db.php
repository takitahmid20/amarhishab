<?php
$config = require __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset={$config['db_charset']}",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}

function sendJson(array $data, int $status = 200): void
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function generateToken(): string
{
    return bin2hex(random_bytes(32));
}

function getBearerToken(): ?string
{
    if (!function_exists('getallheaders')) {
        return null;
    }

    $headers = getallheaders();
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if ($authorization && preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
        return $matches[1];
    }

    return null;
}
