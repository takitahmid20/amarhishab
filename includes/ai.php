<?php
/**
 * HisabAI — builds a finance context for the logged-in user and asks Gemini.
 * Only the current user's data is ever sent, and the model is instructed to
 * answer strictly about their AmarHishab finances.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/cashbooks.php';
require_once __DIR__ . '/transactions.php';
require_once __DIR__ . '/budget.php';
require_once __DIR__ . '/borrow_lend.php';
require_once __DIR__ . '/reminders.php';

/** Resolve the Gemini API key (env wins, then config). */
function ai_key(): string
{
	$k = getenv('GEMINI_API_KEY');
	return $k !== false && $k !== '' ? $k : (app_config()['ai']['key'] ?? '');
}

/** Resolve the model name. */
function ai_model(): string
{
	$m = getenv('GEMINI_MODEL');
	return $m !== false && $m !== '' ? $m : (app_config()['ai']['model'] ?? 'gemini-2.5-flash');
}

/** Build a compact, readable finance snapshot for one user. */
function build_finance_context(int $userId, string $userName): string
{
	$books = cashbooks_for_user($userId);
	$totalBalance = 0.0;
	foreach ($books as $b) {
		$totalBalance += (float) $b['balance'];
	}

	$monthStart = date('Y-m-01');
	$today      = date('Y-m-d');
	$monthTx    = transactions_for_user($userId, ['from' => $monthStart, 'to' => $today]);
	$income = 0.0;
	$expense = 0.0;
	foreach ($monthTx as $t) {
		if ($t['direction'] === 'in') $income += (float) $t['amount'];
		else                         $expense += (float) $t['amount'];
	}

	$recent = array_slice(transactions_for_user($userId), 0, 20);
	$cats   = budget_categories_with_spent($userId);
	$blTot  = borrow_lend_totals($userId);
	$bl     = borrow_lend_records($userId, 'pending');
	$rem    = reminders_for_user($userId, 'all');

	$lines = [];
	$lines[] = "User: {$userName}. All amounts are in Bangladeshi Taka (Tk). Today is {$today}.";
	$lines[] = "";
	$lines[] = "TOTAL BALANCE (all cashbooks): Tk " . number_format($totalBalance);
	$lines[] = "THIS MONTH: income Tk " . number_format($income) . ", expense Tk " . number_format($expense) . ", net Tk " . number_format($income - $expense);

	$lines[] = "";
	$lines[] = "CASHBOOKS:";
	foreach ($books as $b) {
		$lines[] = "- {$b['name']}: balance Tk " . number_format($b['balance'])
			. " (in Tk " . number_format($b['cash_in']) . ", out Tk " . number_format($b['cash_out']) . "), status {$b['status']}";
	}

	$lines[] = "";
	$lines[] = "BUDGET CATEGORIES (spent / limit this period):";
	foreach ($cats as $c) {
		$pct = $c['limit_amount'] > 0 ? round($c['spent'] / $c['limit_amount'] * 100) : 0;
		$lines[] = "- {$c['name']}: Tk " . number_format($c['spent']) . " / Tk " . number_format($c['limit_amount']) . " ({$pct}%)";
	}

	$lines[] = "";
	$lines[] = "BORROW/LEND: you owe (borrowed, unsettled) Tk " . number_format($blTot['borrowed'])
		. "; owed to you (lent, unsettled) Tk " . number_format($blTot['lent']) . ".";
	foreach ($bl as $r) {
		$lines[] = "- " . ($r['type'] === 'borrow' ? 'You owe ' : 'Owed to you by ') . $r['person']
			. ": Tk " . number_format($r['amount']) . ($r['due_date'] ? " due {$r['due_date']}" : "");
	}

	$lines[] = "";
	$lines[] = "REMINDERS:";
	foreach ($rem as $r) {
		$status = $r['is_done'] ? 'paid' : ($r['due_date'] < $today ? 'overdue' : 'pending');
		$lines[] = "- {$r['title']}" . ($r['amount'] !== null ? " Tk " . number_format($r['amount']) : "")
			. " due {$r['due_date']} ({$status})";
	}

	$lines[] = "";
	$lines[] = "RECENT TRANSACTIONS (newest first):";
	foreach ($recent as $t) {
		$dir = $t['direction'] === 'in' ? '+' : '-';
		$label = $t['details'] ?: ($t['bill'] ?: ($t['direction'] === 'in' ? 'income' : 'expense'));
		$lines[] = "- " . date('j M', strtotime($t['occurred_at'])) . " {$dir}Tk " . number_format($t['amount'])
			. " | {$label} | " . ($t['category_name'] ?: 'uncategorized') . " | {$t['cashbook_name']}";
	}

	return implode("\n", $lines);
}

/**
 * Ask Gemini a question grounded in the user's finance context.
 * $history = prior turns [['role'=>'user'|'ai','content'=>...], ...] for follow-ups.
 * Returns ['ok' => bool, 'answer' => string].
 */
function hisab_ai_ask(string $question, string $context, array $history = []): array
{
	$key = ai_key();
	if ($key === '') {
		return ['ok' => false, 'answer' => 'AI is not configured yet.'];
	}

	$system = "You are HisabAI, the finance assistant inside the AmarHishab money-tracking app. "
		. "Answer ONLY using the user's finance data provided below. Amounts are in Bangladeshi Taka — write them as 'Tk 1,200'. "
		. "Be concise, friendly and specific, and use the actual numbers. "
		. "Reply in plain text only — do NOT use Markdown or any formatting symbols (no **, *, #, backticks, or bullet dashes). Write normal sentences; if you list items, put each on its own line. "
		. "If the question is not about this user's personal finances, politely reply that you can only help with their AmarHishab finances. "
		. "Never invent data that is not in the context.\n\n=== USER FINANCE DATA ===\n" . $context;

	// Build multi-turn contents: prior history (last 10 turns) + new question.
	$contents = [];
	foreach (array_slice($history, -10) as $turn) {
		$contents[] = [
			'role'  => $turn['role'] === 'ai' ? 'model' : 'user',
			'parts' => [['text' => (string) $turn['content']]],
		];
	}
	$contents[] = ['role' => 'user', 'parts' => [['text' => $question]]];

	$payload = json_encode([
		'systemInstruction' => ['parts' => [['text' => $system]]],
		'contents' => $contents,
		'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 800],
	]);

	// Try the configured model, then other free models — each free model has
	// its own quota, so a fallback often succeeds when one is rate-limited.
	$candidates = array_values(array_unique([
		ai_model(),
		'gemini-2.5-flash',
		'gemini-2.5-flash-lite',
		'gemini-2.0-flash',
		'gemini-2.0-flash-lite',
	]));

	$rateLimited = false;
	$lastErr = '';
	foreach ($candidates as $model) {
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model)
			. ':generateContent?key=' . urlencode($key);

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_TIMEOUT        => 30,
		]);
		$resp = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err  = curl_error($ch);
		curl_close($ch);

		if ($resp === false) {
			$lastErr = $err;
			continue;
		}

		$data = json_decode($resp, true);

		if ($code === 200) {
			$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
			if ($text !== '') {
				return ['ok' => true, 'answer' => trim($text)];
			}
			continue;
		}

		if ($code === 429) {
			$rateLimited = true; // try next free model
			continue;
		}

		// Other errors (404 model, etc.) — try the next candidate.
		$lastErr = $data['error']['message'] ?? ('HTTP ' . $code);
	}

	if ($rateLimited) {
		return ['ok' => false, 'answer' => "HisabAI is busy right now (free usage limit reached). Please try again in a minute."];
	}

	return ['ok' => false, 'answer' => "I couldn't reach HisabAI just now. Please try again shortly."];
}
