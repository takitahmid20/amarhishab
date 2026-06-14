<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_logged_in()) {
	redirect('./dashboard.php');
}
$error = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Forgot Password | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
</head>
<body class="auth-page">
	<main class="auth-shell" aria-labelledby="forgot-title">
		<section class="auth-card auth-card--forgot">
			<header class="auth-header">
				<h1 id="forgot-title" class="auth-title">Reset Password</h1>
				<p class="auth-subtitle">Enter your email to receive a verification code</p>
			</header>

			<?php if ($error !== ''): ?>
				<p class="auth-error" role="alert"><?= e($error) ?></p>
			<?php endif; ?>

			<form class="auth-form auth-form--tight" action="../actions/forgot-password.php" method="post">
				<?= csrf_field() ?>
				<label class="field" for="forgot-email">
					<span class="label">Email Address</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="mail" aria-hidden="true"></i>
						<input id="forgot-email" name="email" class="input" type="email" placeholder="Enter your email" autocomplete="email" value="<?= e(old('email')) ?>" required />
					</div>
				</label>

				<p class="auth-help">Enter the email address associated with your account and we'll send you a code to reset your password.</p>

				<button type="submit" class="btn btn-primary btn-block auth-submit">
					<span>Send Code</span>
				</button>
			</form>

			<p class="auth-footer">
				<a class="auth-link auth-link-inline" href="./login.php"><i class="auth-link-icon" data-lucide="arrow-left" aria-hidden="true"></i><span>Back to Sign In</span></a>
			</p>
		</section>
	</main>
	<?php old_clear(); ?>
	<script src="https://unpkg.com/lucide@latest"></script>
	<script>
		lucide.createIcons();
	</script>
</body>
</html>
