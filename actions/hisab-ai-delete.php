<?php
/**
 * Delete a HisabAI conversation owned by the user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/ai_chat.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/hisab-ai.php');
}

csrf_check();

$chatId = (int) post('id');
if ($chatId > 0) {
	ai_delete_chat($chatId, current_user()['id']);
}

redirect('../pages/hisab-ai.php');
