<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/ai_chat.php';
require_login();
$user = current_user();
$firstName = explode(' ', trim($user['name']))[0] ?? 'there';

$chats   = ai_chats_for_user((int) $user['id']);
$chatId  = (int) ($_GET['chat'] ?? 0);
$active  = $chatId > 0 ? ai_find_chat($chatId, (int) $user['id']) : null;
$messages = $active ? ai_chat_messages((int) $active['id']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>HisabAI | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/hisab-ai.css" />
</head>
<body data-page-title="HisabAI">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main hisabai-main">
				<div class="hisabai-layout">

					<!-- Conversation list -->
					<aside class="hisabai-conv">
						<a href="./hisab-ai.php" class="hisabai-newchat">
							<i data-lucide="plus" aria-hidden="true"></i><span>New chat</span>
						</a>
						<div class="hisabai-conv-list">
							<?php if (empty($chats)): ?>
								<p class="hisabai-conv-empty">No conversations yet.</p>
							<?php else: ?>
								<?php foreach ($chats as $c): ?>
									<div class="hisabai-conv-item<?= $active && (int) $active['id'] === (int) $c['id'] ? ' active' : '' ?>">
										<a class="hisabai-conv-link" href="./hisab-ai.php?chat=<?= e($c['id']) ?>">
											<i data-lucide="message-square" aria-hidden="true"></i>
											<span><?= e($c['title']) ?></span>
										</a>
										<form action="../actions/hisab-ai-delete.php" method="post" onsubmit="return confirm('Delete this conversation?');">
											<?= csrf_field() ?>
											<input type="hidden" name="id" value="<?= e($c['id']) ?>">
											<button type="submit" class="hisabai-conv-del" aria-label="Delete conversation"><i data-lucide="trash-2" aria-hidden="true"></i></button>
										</form>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</aside>

					<!-- Chat -->
					<section class="hisabai-shell">
						<header class="hisabai-header">
							<div class="hisabai-header-left">
								<span class="hisabai-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>
								<div class="hisabai-header-copy">
									<h1>HisabAI <span class="hisabai-beta">Beta</span></h1>
									<p>Your personal finance assistant — ask anything about your money.</p>
								</div>
							</div>
							<span class="hisabai-status"><span class="hisabai-status-dot"></span>Online</span>
						</header>

						<div class="hisabai-chat" data-hisabai-chat>
							<?php if (empty($messages)): ?>
								<div class="hisabai-msg hisabai-msg--ai">
									<span class="hisabai-msg-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>
									<div class="hisabai-bubble">
										<p>Hi <?= e($firstName) ?> 👋 I'm <strong>HisabAI</strong>. Ask me about your cashbooks, spending, budgets, dues or reminders.</p>
										<p class="hisabai-muted">Try one of these:</p>
									</div>
								</div>
								<div class="hisabai-suggestions" data-hisabai-suggestions>
									<button class="hisabai-chip" type="button">How much did I spend this month?</button>
									<button class="hisabai-chip" type="button">What's my biggest expense category?</button>
									<button class="hisabai-chip" type="button">How much do people owe me?</button>
									<button class="hisabai-chip" type="button">Am I over budget anywhere?</button>
									<button class="hisabai-chip" type="button">What's my total balance right now?</button>
								</div>
							<?php else: ?>
								<?php foreach ($messages as $m): ?>
									<?php if ($m['role'] === 'ai'): ?>
										<div class="hisabai-msg hisabai-msg--ai">
											<span class="hisabai-msg-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>
											<div class="hisabai-bubble"><p data-md><?= e($m['content']) ?></p></div>
										</div>
									<?php else: ?>
										<div class="hisabai-msg hisabai-msg--user">
											<div class="hisabai-bubble"><?= nl2br(e($m['content'])) ?></div>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>

						<form class="hisabai-composer" data-hisabai-form data-csrf="<?= e(csrf_token()) ?>" data-chat-id="<?= $active ? e($active['id']) : '' ?>">
							<div class="hisabai-input-wrap">
								<i class="hisabai-input-icon" data-lucide="message-circle" aria-hidden="true"></i>
								<textarea class="hisabai-input" rows="1" placeholder="Ask HisabAI about your finances..." data-hisabai-input></textarea>
								<button class="hisabai-send" type="submit" aria-label="Send message"><i data-lucide="arrow-up" aria-hidden="true"></i></button>
							</div>
							<p class="hisabai-disclaimer">HisabAI answers from your AmarHishab data. It can make mistakes — double-check important numbers.</p>
						</form>
					</section>

				</div>
			</main>
		</div>
	</div>

	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
	<script>
		(function () {
			var chat  = document.querySelector('[data-hisabai-chat]');
			var form  = document.querySelector('[data-hisabai-form]');
			var input = document.querySelector('[data-hisabai-input]');

			function escapeHtml(s) {
				return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
			}
			function formatAi(raw) {
				var s = escapeHtml(raw);
				s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
				s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');
				s = s.replace(/`(.+?)`/g, '$1');
				s = s.replace(/^\s*[\-\*]\s+/gm, '• ');
				s = s.replace(/^\s*#{1,6}\s*/gm, '');
				s = s.replace(/\n/g, '<br>');
				return s;
			}

			// Render any server-loaded AI messages through the same formatter.
			document.querySelectorAll('[data-md]').forEach(function (el) {
				el.innerHTML = formatAi(el.textContent);
			});

			function addBubble(html, who) {
				var msg = document.createElement('div');
				msg.className = 'hisabai-msg hisabai-msg--' + who;
				msg.innerHTML = (who === 'ai'
					? '<span class="hisabai-msg-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>'
					+ '<div class="hisabai-bubble"><p>' + html + '</p></div>'
					: '<div class="hisabai-bubble">' + html + '</div>');
				chat.appendChild(msg);
				chat.scrollTop = chat.scrollHeight;
				if (window.lucide) lucide.createIcons();
				return msg;
			}
			function addTyping() {
				var msg = document.createElement('div');
				msg.className = 'hisabai-msg hisabai-msg--ai';
				msg.innerHTML = '<span class="hisabai-msg-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>'
					+ '<div class="hisabai-bubble"><span class="hisabai-typing"><span></span><span></span><span></span></span></div>';
				chat.appendChild(msg);
				chat.scrollTop = chat.scrollHeight;
				if (window.lucide) lucide.createIcons();
				return msg;
			}

			document.querySelectorAll('[data-hisabai-suggestions] .hisabai-chip').forEach(function (chip) {
				chip.addEventListener('click', function () { input.value = chip.textContent; input.focus(); });
			});

			var busy = false;
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (busy) return;
				var text = input.value.trim();
				if (!text) return;
				var sugg = document.querySelector('[data-hisabai-suggestions]');
				if (sugg) sugg.remove();
				addBubble(escapeHtml(text).replace(/\n/g, '<br>'), 'user');
				input.value = ''; input.style.height = 'auto';
				busy = true;
				var typing = addTyping();

				var body = new URLSearchParams();
				body.set('question', text);
				body.set('_csrf', form.getAttribute('data-csrf'));
				body.set('chat_id', form.getAttribute('data-chat-id') || '');

				fetch('../actions/hisab-ai-ask.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: body.toString()
				})
				.then(function (r) { return r.json(); })
				.then(function (data) {
					typing.remove();
					addBubble(formatAi(data.answer || 'No answer.'), 'ai');
					// First message of a brand-new chat: reload so it appears in the list.
					if (data.is_new && data.chat_id) {
						form.setAttribute('data-chat-id', data.chat_id);
						window.location.href = './hisab-ai.php?chat=' + data.chat_id;
					}
				})
				.catch(function () {
					typing.remove();
					addBubble('Something went wrong reaching HisabAI. Please try again.', 'ai');
				})
				.finally(function () { busy = false; });
			});

			input.addEventListener('input', function () {
				input.style.height = 'auto';
				input.style.height = Math.min(input.scrollHeight, 140) + 'px';
			});
		})();
	</script>
</body>
</html>
