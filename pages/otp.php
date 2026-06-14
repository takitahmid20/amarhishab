<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (is_logged_in()) {
	redirect('./dashboard.php');
}
if (empty($_SESSION['pwreset'])) {
	redirect('./forgot-password.php');
}
$error   = flash_get('error');
$success = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>OTP Verification | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
</head>
<body class="auth-page">
	<main class="auth-shell" aria-labelledby="otp-title">
		<section class="auth-card auth-card--otp">
			<header class="auth-header">
				<h1 id="otp-title" class="auth-title">Verify Your Account</h1>
				<p class="auth-subtitle">Enter the 4-digit code we sent to your email</p>
			</header>

			<?php if ($success !== ''): ?>
				<p class="auth-success" role="status"><?= e($success) ?></p>
			<?php endif; ?>
			<?php if ($error !== ''): ?>
				<p class="auth-error" role="alert"><?= e($error) ?></p>
			<?php endif; ?>

			<form class="auth-form auth-form--tight" action="../actions/verify-otp.php" method="post">
				<?= csrf_field() ?>
				<div class="field">
					<span class="label">Enter Verification Code</span>
					<div class="auth-otp-grid">
						<input class="auth-otp-input" name="d1" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 1" required />
						<input class="auth-otp-input" name="d2" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 2" required />
						<input class="auth-otp-input" name="d3" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 3" required />
						<input class="auth-otp-input" name="d4" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 4" required />
					</div>
					<p class="auth-note auth-note--center">We sent a verification code to your email address</p>
				</div>

				<button type="submit" class="btn btn-primary btn-block auth-submit">
					<span>Verify Code</span>
				</button>
			</form>

			<div class="auth-footer auth-footer--stack">
				<p>
					Didn't receive a code?
					<a class="auth-link auth-link-inline" href="./forgot-password.php"><i class="auth-link-icon" data-lucide="refresh-cw" aria-hidden="true"></i><span>Resend</span></a>
				</p>
				<p><a class="auth-link auth-link-inline" href="./login.php"><i class="auth-link-icon" data-lucide="arrow-left" aria-hidden="true"></i><span>Back to Sign In</span></a></p>
			</div>
		</section>
	</main>
	<script src="https://unpkg.com/lucide@latest"></script>
	<script>
		lucide.createIcons();
		// Auto-advance focus between the 4 OTP boxes.
		document.querySelectorAll('.auth-otp-input').forEach(function (input, i, all) {
			input.addEventListener('input', function () {
				if (input.value.length === 1 && i < all.length - 1) all[i + 1].focus();
			});
			input.addEventListener('keydown', function (e) {
				if (e.key === 'Backspace' && input.value === '' && i > 0) all[i - 1].focus();
			});
		});
	</script>
</body>
</html>
