<?php
/**
 * HisabAI ask endpoint. POST (CSRF + login). Returns JSON { ok, answer }.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/ai.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'answer' => 'Method not allowed.']);
	exit;
}

csrf_check();

$question = trim((string) ($_POST['question'] ?? ''));
if ($question === '') {
	echo json_encode(['ok' => false, 'answer' => 'Please type a question.']);
	exit;
}
if (mb_strlen($question) > 500) {
	$question = mb_substr($question, 0, 500);
}

$user    = current_user();
$context = build_finance_context((int) $user['id'], $user['name']);
$result  = hisab_ai_ask($question, $context);

echo json_encode($result);
