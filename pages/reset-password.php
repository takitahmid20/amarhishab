<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_logged_in()) {
	redirect('./dashboard.php');
}
if (empty($_SESSION['pwreset']['verified'])) {
	redirect('./forgot-password.php');
}
$error = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Set New Password | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
</head>
<body class="auth-page">
	<main class="auth-shell" aria-labelledby="reset-title">
		<section class="auth-card auth-card--forgot">
			<header class="auth-header">
				<h1 id="reset-title" class="auth-title">Set New Password</h1>
				<p class="auth-subtitle">Choose a new password for your account</p>
			</header>

			<?php if ($error !== ''): ?>
				<p class="auth-error" role="alert"><?= e($error) ?></p>
			<?php endif; ?>

			<form class="auth-form auth-form--tight" action="../actions/reset-password.php" method="post">
				<?= csrf_field() ?>
				<label class="field" for="reset-password">
					<span class="label">New Password</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="lock" aria-hidden="true"></i>
						<input id="reset-password" name="password" class="input" type="password" placeholder="At least 6 characters" autocomplete="new-password" required />
					</div>
				</label>

				<label class="field" for="reset-confirm">
					<span class="label">Confirm Password</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="shield-check" aria-hidden="true"></i>
						<input id="reset-confirm" name="confirm_password" class="input" type="password" placeholder="Re-enter your password" autocomplete="new-password" required />
					</div>
				</label>

				<button type="submit" class="btn btn-primary btn-block auth-submit">
					<span>Update Password</span>
				</button>
			</form>

			<p class="auth-footer">
				<a class="auth-link auth-link-inline" href="./login.php"><i class="auth-link-icon" data-lucide="arrow-left" aria-hidden="true"></i><span>Back to Sign In</span></a>
			</p>
		</section>
	</main>
	<script src="https://unpkg.com/lucide@latest"></script>
	<script>
		lucide.createIcons();
	</script>
</body>
</html>
