<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$user = current_user();
$success = flash_get('success');
$error   = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Settings | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/settings.css" />
</head>

<body data-page-title="Settings">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main settings-main">
				<section class="container-wide settings-page">

					<!-- Hero -->
					<header class="settings-hero surface">
						<div class="settings-hero-copy">
							<h1>Settings</h1>
							<p>Manage your profile, preferences and account</p>
						</div>
					</header>

					<?php if ($success !== ''): ?>
						<p class="auth-success" role="status"><?= e($success) ?></p>
					<?php endif; ?>
					<?php if ($error !== ''): ?>
						<p class="auth-error" role="alert"><?= e($error) ?></p>
					<?php endif; ?>

					<div class="settings-grid">

						<!-- Profile Section -->
						<section class="settings-card surface">
							<div class="settings-card-head">
								<div class="settings-card-icon">
									<i data-lucide="user-round" aria-hidden="true"></i>
								</div>
								<div>
									<h2>Profile</h2>
									<p>Update your personal information</p>
								</div>
							</div>
							<form class="settings-form" action="../actions/profile-update.php" method="post">
								<?= csrf_field() ?>
								<div class="settings-avatar-wrap">
									<div class="settings-avatar">
										<i data-lucide="user" aria-hidden="true"></i>
									</div>
									<div>
										<p class="settings-avatar-name"><?= e($user['name']) ?></p>
										<p class="settings-avatar-email"><?= e($user['email']) ?></p>
									</div>
								</div>
								<label class="field">
									<span class="label">Full Name</span>
									<input class="input" type="text" name="name" placeholder="Your full name" value="<?= e($user['name']) ?>" />
								</label>
								<label class="field">
									<span class="label">Email Address</span>
									<input class="input" type="email" name="email" placeholder="your@email.com" value="<?= e($user['email']) ?>" />
								</label>
								<div class="settings-form-footer">
									<button class="btn btn-primary btn-sm" type="submit">
										<i data-lucide="save" aria-hidden="true"></i>
										Save Changes
									</button>
								</div>
							</form>
						</section>

						<!-- Password Section -->
						<section class="settings-card surface">
							<div class="settings-card-head">
								<div class="settings-card-icon settings-card-icon--warning">
									<i data-lucide="lock-keyhole" aria-hidden="true"></i>
								</div>
								<div>
									<h2>Change Password</h2>
									<p>Update your account password</p>
								</div>
							</div>
							<form class="settings-form" data-password-form>
								<label class="field">
									<span class="label">Current Password</span>
									<input class="input" type="password" name="current_password" placeholder="Enter current password" />
								</label>
								<label class="field">
									<span class="label">New Password</span>
									<input class="input" type="password" name="new_password" placeholder="Enter new password" />
								</label>
								<label class="field">
									<span class="label">Confirm New Password</span>
									<input class="input" type="password" name="confirm_password" placeholder="Confirm new password" />
								</label>
								<div class="settings-form-footer">
									<button class="btn btn-primary btn-sm" type="submit">
										<i data-lucide="lock" aria-hidden="true"></i>
										Update Password
									</button>
								</div>
							</form>
						</section>

						<!-- Preferences Section -->
						<section class="settings-card surface">
							<div class="settings-card-head">
								<div class="settings-card-icon settings-card-icon--info">
									<i data-lucide="sliders-horizontal" aria-hidden="true"></i>
								</div>
								<div>
									<h2>Preferences</h2>
									<p>Customize your app experience</p>
								</div>
							</div>
							<div class="settings-prefs">

								<div class="settings-pref-row">
									<div class="settings-pref-info">
										<strong>Dark Mode</strong>
										<p>Switch between light and dark theme</p>
									</div>
									<label class="settings-toggle" aria-label="Toggle dark mode">
										<input type="checkbox" name="dark_mode" />
										<span class="settings-toggle-track"></span>
									</label>
								</div>

								<div class="settings-pref-row">
									<div class="settings-pref-info">
										<strong>Currency</strong>
										<p>Select your preferred currency</p>
									</div>
									<select class="select settings-currency-select" name="currency">
										<option value="BDT" selected>৳ BDT</option>
										<option value="USD">$ USD</option>
										<option value="EUR">€ EUR</option>
										<option value="GBP">£ GBP</option>
										<option value="INR">₹ INR</option>
									</select>
								</div>

								<div class="settings-pref-row">
									<div class="settings-pref-info">
										<strong>Notifications</strong>
										<p>Enable reminder notifications</p>
									</div>
									<label class="settings-toggle" aria-label="Toggle notifications">
										<input type="checkbox" name="notifications" checked />
										<span class="settings-toggle-track"></span>
									</label>
								</div>

							</div>
						</section>

						<!-- Danger Zone -->
						<section class="settings-card settings-card--danger surface">
							<div class="settings-card-head">
								<div class="settings-card-icon settings-card-icon--danger">
									<i data-lucide="triangle-alert" aria-hidden="true"></i>
								</div>
								<div>
									<h2>Danger Zone</h2>
									<p>Irreversible actions — proceed with caution</p>
								</div>
							</div>
							<div class="settings-danger-list">

								<div class="settings-danger-row">
									<div class="settings-pref-info">
										<strong>Reset All Data</strong>
										<p>Delete all transactions, budgets and reminders</p>
									</div>
									<button class="btn btn-outline btn-sm settings-btn-danger" type="button" data-modal-target="#reset-data-modal">
										<i data-lucide="rotate-ccw" aria-hidden="true"></i>
										Reset Data
									</button>
								</div>

								<div class="settings-danger-row">
									<div class="settings-pref-info">
										<strong>Logout</strong>
										<p>Sign out from your account</p>
									</div>
									<button class="btn btn-outline btn-sm settings-btn-danger" type="button" data-modal-target="#logout-modal">
										<i data-lucide="log-out" aria-hidden="true"></i>
										Logout
									</button>
								</div>

							</div>
						</section>

					</div>

					<!-- Reset Data Modal -->
					<div class="overlay modal-overlay" id="reset-data-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="reset-data-title">
							<div class="modal-head">
								<h2 class="modal-title" id="reset-data-title">Reset All Data</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<div class="modal-body">
								<div class="settings-confirm-icon settings-confirm-icon--danger">
									<i data-lucide="triangle-alert" aria-hidden="true"></i>
								</div>
								<p class="settings-confirm-text">Are you sure you want to reset all data? This action <strong>cannot be undone</strong>.</p>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-sm settings-btn-danger-solid" type="button">Yes, Reset Everything</button>
								</div>
							</div>
						</div>
					</div>

					<!-- Logout Modal -->
					<div class="overlay modal-overlay" id="logout-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="logout-title">
							<div class="modal-head">
								<h2 class="modal-title" id="logout-title">Logout</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<div class="modal-body">
								<div class="settings-confirm-icon">
									<i data-lucide="log-out" aria-hidden="true"></i>
								</div>
								<p class="settings-confirm-text">Are you sure you want to logout from AmarHishab?</p>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<a class="btn btn-primary btn-sm" href="../actions/logout.php" id="confirm-logout-btn">Yes, Logout</a>
								</div>
							</div>
						</div>
					</div>

				</section>
			</main>
		</div>
	</div>
	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>