<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$user = current_user();
$firstName = explode(' ', trim($user['name']))[0] ?? 'there';
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
				<section class="hisabai-shell">

					<!-- Header -->
					<header class="hisabai-header">
						<div class="hisabai-header-left">
							<span class="hisabai-avatar" aria-hidden="true">
								<i data-lucide="sparkles"></i>
							</span>
							<div class="hisabai-header-copy">
								<h1>HisabAI <span class="hisabai-beta">Beta</span></h1>
								<p>Your personal finance assistant — ask anything about your money.</p>
							</div>
						</div>
						<span class="hisabai-status"><span class="hisabai-status-dot"></span>Coming soon</span>
					</header>

					<!-- Conversation -->
					<div class="hisabai-chat" data-hisabai-chat>
						<div class="hisabai-msg hisabai-msg--ai">
							<span class="hisabai-msg-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>
							<div class="hisabai-bubble">
								<p>Hi <?= e($firstName) ?> 👋 I'm <strong>HisabAI</strong>. Soon I'll answer questions about your cashbooks, spending, budgets, and dues — in plain language.</p>
								<p class="hisabai-muted">Try one of these to see what I'll be able to do:</p>
							</div>
						</div>

						<!-- Suggestion chips -->
						<div class="hisabai-suggestions" data-hisabai-suggestions>
							<button class="hisabai-chip" type="button">How much did I spend this month?</button>
							<button class="hisabai-chip" type="button">What's my biggest expense category?</button>
							<button class="hisabai-chip" type="button">How much do people owe me?</button>
							<button class="hisabai-chip" type="button">Am I over budget anywhere?</button>
							<button class="hisabai-chip" type="button">What's my total balance right now?</button>
						</div>
					</div>

					<!-- Composer -->
					<form class="hisabai-composer" data-hisabai-form>
						<div class="hisabai-input-wrap">
							<i class="hisabai-input-icon" data-lucide="message-circle" aria-hidden="true"></i>
							<textarea class="hisabai-input" rows="1" placeholder="Ask HisabAI about your finances..." data-hisabai-input></textarea>
							<button class="hisabai-send" type="submit" aria-label="Send message">
								<i data-lucide="arrow-up" aria-hidden="true"></i>
							</button>
						</div>
						<p class="hisabai-disclaimer">HisabAI is in design preview — answers aren't connected yet.</p>
					</form>

				</section>
			</main>
		</div>
	</div>

	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
	<script>
		// Design-preview interactions only — no real AI yet.
		(function () {
			var chat  = document.querySelector('[data-hisabai-chat]');
			var form  = document.querySelector('[data-hisabai-form]');
			var input = document.querySelector('[data-hisabai-input]');

			function addBubble(text, who) {
				var msg = document.createElement('div');
				msg.className = 'hisabai-msg hisabai-msg--' + who;
				if (who === 'ai') {
					msg.innerHTML = '<span class="hisabai-msg-avatar" aria-hidden="true"><i data-lucide="sparkles"></i></span>'
						+ '<div class="hisabai-bubble"><p>' + text + '</p></div>';
				} else {
					msg.innerHTML = '<div class="hisabai-bubble">' + text + '</div>';
				}
				chat.appendChild(msg);
				chat.scrollTop = chat.scrollHeight;
				if (window.lucide) lucide.createIcons();
			}

			document.querySelectorAll('[data-hisabai-suggestions] .hisabai-chip').forEach(function (chip) {
				chip.addEventListener('click', function () {
					input.value = chip.textContent;
					input.focus();
				});
			});

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var text = input.value.trim();
				if (!text) return;
				addBubble(text.replace(/</g, '&lt;'), 'user');
				input.value = '';
				setTimeout(function () {
					addBubble("I'm not connected yet — this is a design preview. Soon I'll read your finance data and answer this.", 'ai');
				}, 400);
			});

			// auto-grow textarea
			input.addEventListener('input', function () {
				input.style.height = 'auto';
				input.style.height = Math.min(input.scrollHeight, 140) + 'px';
			});
		})();
	</script>
</body>
</html>
