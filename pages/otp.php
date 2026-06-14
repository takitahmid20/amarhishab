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

			<form class="auth-form auth-form--tight" action="#" method="post">
				<div class="field">
					<span class="label">Enter Verification Code</span>
					<div class="auth-otp-grid">
						<input class="auth-otp-input" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 1" />
						<input class="auth-otp-input" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 2" />
						<input class="auth-otp-input" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 3" />
						<input class="auth-otp-input" type="text" inputmode="numeric" maxlength="1" aria-label="OTP digit 4" />
					</div>
					<p class="auth-note auth-note--center">We sent a verification code to your email address</p>
				</div>

				<button type="submit" class="btn btn-primary btn-block auth-submit" disabled>
					<span>Verify Code</span>
				</button>
			</form>

			<div class="auth-footer auth-footer--stack">
				<p>
					Didn't receive a code?
					<a class="auth-link auth-link-inline" href="#"><i class="auth-link-icon" data-lucide="refresh-cw" aria-hidden="true"></i><span>Resend</span></a>
				</p>
				<p><a class="auth-link auth-link-inline" href="./login.php"><i class="auth-link-icon" data-lucide="arrow-left" aria-hidden="true"></i><span>Back to Sign In</span></a></p>
			</div>
		</section>
	</main>
	<script src="https://unpkg.com/lucide@latest"></script>
	<script>
		lucide.createIcons();
	</script>
</body>
</html>
