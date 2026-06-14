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
	<title>Sign Up | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/signup.css" />
</head>

<body class="auth-page">
	<main class="auth-shell" aria-labelledby="signup-title">
		<section class="auth-card auth-card--signup">
			<img class="auth-logo" src="../assets/logos/amarhishab-logo.png" alt="AmarHishab" />

			<header class="auth-header">
				<h1 id="signup-title" class="auth-title">Create Account</h1>
				<p class="auth-subtitle">Sign up to get started</p>
			</header>

			<?php if ($error !== ''): ?>
				<p class="auth-error" role="alert"><?= e($error) ?></p>
			<?php endif; ?>

			<form class="auth-form" action="../actions/signup.php" method="post">
				<?= csrf_field() ?>

				<label class="field" for="signup-name">
					<span class="label">Full Name</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="user" aria-hidden="true"></i>
						<input id="signup-name" name="name" class="input" type="text" placeholder="Enter your name" value="<?= e(old('name')) ?>" required />
					</div>
				</label>

				<label class="field" for="signup-email">
					<span class="label">Email Address</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="mail" aria-hidden="true"></i>
						<input id="signup-email" name="email" class="input" type="email" placeholder="Enter your email" autocomplete="email" value="<?= e(old('email')) ?>" required />
					</div>
				</label>

				<label class="field" for="signup-password">
					<span class="label">Password</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="lock" aria-hidden="true"></i>
						<input id="signup-password" name="password" class="input" type="password" placeholder="At least 6 characters" autocomplete="new-password" required />
					</div>
				</label>

				<label class="field" for="signup-confirm-password">
					<span class="label">Confirm Password</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="shield-check" aria-hidden="true"></i>
						<input id="signup-confirm-password" name="confirm_password" class="input" type="password" placeholder="Re-enter your password" autocomplete="new-password" required />
					</div>
				</label>

				<button type="submit" class="btn btn-primary btn-block auth-submit">
					<span>Sign Up</span>
				</button>
			</form>

			<p class="auth-footer">
				Already have an account?
				<a href="./login.php">Sign In</a>
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
