<header class="navbar app-topbar dashboard-topbar">
	<div class="navbar-left">
		<a class="navbar-brand-link" href="./dashboard.php" aria-label="AmarHishab dashboard">
			<img
				class="navbar-logo"
				src="../assets/logos/amarhishab-logo.png"
				alt="AmarHishab Logo"
			/>
		</a>
	</div>

	<div class="navbar-right">

		<div class="navbar-search search-wrap">
			<span class="search-icon" aria-hidden="true">🔍</span>
			<input
				class="input"
				type="search"
				placeholder="Search"
				aria-label="Search"
			/>
		</div>

		<div class="navbar-notification-wrap">
			<button class="btn btn-outline btn-icon navbar-notification" type="button" aria-label="Notifications" aria-expanded="false" data-notification-toggle>
				<i data-lucide="bell" aria-hidden="true"></i>
			</button>
			<div class="navbar-notification-panel" data-notification-panel hidden aria-label="Notifications">
				<div class="notification-panel-head">
					<strong>Notifications</strong>
					<span class="notification-pill">3 New</span>
				</div>
				<div class="notification-list">
					<div class="notification-item">
						<div class="notification-dot"></div>
						<div>
							<p class="notification-title">Budget alert</p>
							<p class="notification-meta">Food & Dining is at 80% this month.</p>
						</div>
					</div>
					<div class="notification-item">
						<div class="notification-dot"></div>
						<div>
							<p class="notification-title">New income added</p>
							<p class="notification-meta">Salary deposit of ৳ 5,000 received.</p>
						</div>
					</div>
					<div class="notification-item">
						<div class="notification-dot"></div>
						<div>
							<p class="notification-title">Weekly report ready</p>
							<p class="notification-meta">View your last 7 days summary.</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php $navUser = function_exists('current_user') ? current_user() : null; ?>
		<button
			class="navbar-user"
			type="button"
			aria-label="Open profile menu"
			aria-expanded="false"
			onclick="window.location.href='./settings.php'"
		>
			<span class="navbar-user-avatar" data-user-avatar>
				<img src="https://api.dicebear.com/9.x/adventurer-neutral/svg?seed=<?= e(urlencode($navUser['name'] ?? 'User')) ?>" alt="User avatar" />
			</span>
			<span class="navbar-user-meta">
				<span class="navbar-user-name" data-user-name><?= e($navUser['name'] ?? '') ?></span>
			</span>
		</button>

		<a class="btn btn-outline btn-icon navbar-logout" href="../actions/logout.php" aria-label="Log out" title="Log out">
			<i data-lucide="log-out" aria-hidden="true"></i>
		</a>

	</div>
</header>