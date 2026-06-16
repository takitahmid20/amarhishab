<?php
/**
 * HisabAI conversation storage — multiple saved chats per user, each with
 * an ordered list of messages. All queries scoped to the owning user.
 */

require_once __DIR__ . '/../config/db.php';

/** All chats for a user, most recently updated first. */
function ai_chats_for_user(int $userId): array
{
	$stmt = db()->prepare('SELECT id, title, updated_at FROM ai_chats WHERE user_id = ? ORDER BY updated_at DESC, id DESC');
	$stmt->execute([$userId]);
	return $stmt->fetchAll();
}

/** A chat owned by the user, or null. */
function ai_find_chat(int $chatId, int $userId): ?array
{
	$stmt = db()->prepare('SELECT * FROM ai_chats WHERE id = ? AND user_id = ?');
	$stmt->execute([$chatId, $userId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** Create a chat, returns its id. Title trimmed from the first question. */
function ai_create_chat(int $userId, string $title): int
{
	$title = trim($title);
	if ($title === '') {
		$title = 'New chat';
	}
	if (mb_strlen($title) > 60) {
		$title = mb_substr($title, 0, 57) . '…';
	}
	$stmt = db()->prepare('INSERT INTO ai_chats (user_id, title) VALUES (?, ?)');
	$stmt->execute([$userId, $title]);
	return (int) db()->lastInsertId();
}

/** Messages of a chat, oldest first. */
function ai_chat_messages(int $chatId): array
{
	$stmt = db()->prepare('SELECT role, content, created_at FROM ai_messages WHERE chat_id = ? ORDER BY id ASC');
	$stmt->execute([$chatId]);
	return $stmt->fetchAll();
}

/** Append a message to a chat and bump the chat's updated_at. */
function ai_add_message(int $chatId, string $role, string $content): void
{
	$stmt = db()->prepare('INSERT INTO ai_messages (chat_id, role, content) VALUES (?, ?, ?)');
	$stmt->execute([$chatId, $role === 'ai' ? 'ai' : 'user', $content]);
	db()->prepare('UPDATE ai_chats SET updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$chatId]);
}

/** Delete a chat owned by the user (messages cascade). */
function ai_delete_chat(int $chatId, int $userId): int
{
	$stmt = db()->prepare('DELETE FROM ai_chats WHERE id = ? AND user_id = ?');
	$stmt->execute([$chatId, $userId]);
	return $stmt->rowCount();
}
