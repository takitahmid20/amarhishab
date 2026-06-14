<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_logged_in()) {
	redirect('./dashboard.php');
}
$error   = flash_get('error');
$success = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Login | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
</head>

<body class="auth-page">
	<main class="auth-shell" aria-labelledby="signin-title">
		<section class="auth-card auth-card--signin">
			<img class="auth-logo" src="../assets/logos/amarhishab-logo.png" alt="AmarHishab" />

			<header class="auth-header">
				<h1 id="signin-title" class="auth-title">Welcome Back</h1>
				<p class="auth-subtitle">Sign in to access your personal finance dashboard</p>
			</header>

			<?php if ($success !== ''): ?>
				<p class="auth-success" role="status"><?= e($success) ?></p>
			<?php endif; ?>
			<?php if ($error !== ''): ?>
				<p class="auth-error" role="alert"><?= e($error) ?></p>
			<?php endif; ?>

			<form class="auth-form auth-form--tight" action="../actions/login.php" method="post">
				<?= csrf_field() ?>

				<label class="field" for="login-email">
					<span class="label">Email Address</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="mail" aria-hidden="true"></i>
						<input id="login-email" name="email" class="input" type="email" placeholder="Enter your email" autocomplete="email" value="<?= e(old('email')) ?>" required />
					</div>
				</label>

				<label class="field" for="login-password">
					<span class="auth-label-row">
						<span class="label">Password</span>
						<a class="auth-link-small" href="./forgot-password.php">Forgot?</a>
					</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="lock" aria-hidden="true"></i>
						<input id="login-password" name="password" class="input" type="password" placeholder="Enter your password" autocomplete="current-password" required />
					</div>
				</label>

				<button type="submit" class="btn btn-primary btn-block auth-submit">
					<span>Sign In</span>
				</button>
			</form>

			<p class="auth-footer">
				Don't have an account?
				<a class="auth-link" href="./signup.php">Sign Up</a>
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
