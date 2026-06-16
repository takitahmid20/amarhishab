<?php
/**
 * HisabAI ask endpoint. POST (CSRF + login). Saves the conversation.
 * Returns JSON { ok, answer, chat_id, title }.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/ai.php';
require_once __DIR__ . '/../includes/ai_chat.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'answer' => 'Method not allowed.']);
	exit;
}

csrf_check();

$userId   = (int) current_user()['id'];
$question = trim((string) ($_POST['question'] ?? ''));
$chatId   = (int) ($_POST['chat_id'] ?? 0);

if ($question === '') {
	echo json_encode(['ok' => false, 'answer' => 'Please type a question.']);
	exit;
}
if (mb_strlen($question) > 500) {
	$question = mb_substr($question, 0, 500);
}

// Resolve or create the chat.
$chat = $chatId > 0 ? ai_find_chat($chatId, $userId) : null;
$isNew = false;
if (!$chat) {
	$chatId = ai_create_chat($userId, $question);
	$chat   = ai_find_chat($chatId, $userId);
	$isNew  = true;
}

// History BEFORE saving the new question.
$history = array_map(
	fn($m) => ['role' => $m['role'], 'content' => $m['content']],
	ai_chat_messages($chatId)
);

ai_add_message($chatId, 'user', $question);

$user    = current_user();
$context = build_finance_context($userId, $user['name']);
$result  = hisab_ai_ask($question, $context, $history);

if ($result['ok']) {
	ai_add_message($chatId, 'ai', $result['answer']);
}

echo json_encode([
	'ok'      => $result['ok'],
	'answer'  => $result['answer'],
	'chat_id' => $chatId,
	'title'   => $chat['title'],
	'is_new'  => $isNew,
]);
