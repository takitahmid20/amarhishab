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
				<p class="auth-subtitle">Enter your email to receive a password reset link</p>
			</header>

			<form class="auth-form auth-form--tight" action="#" method="post">
				<label class="field" for="forgot-email">
					<span class="label">Email Address</span>
					<div class="input-wrap">
						<i class="input-icon" data-lucide="mail" aria-hidden="true"></i>
						<input id="forgot-email" class="input" type="email" placeholder="Enter your email" autocomplete="email" />
					</div>
				</label>

				<p class="auth-help">Enter the email address associated with your account and we'll send you a link to reset your password.</p>

				<button type="submit" class="btn btn-primary btn-block auth-submit">
					<span>Send Reset Link</span>
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
