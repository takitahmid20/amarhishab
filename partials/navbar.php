<?php
/**
 * Build notifications from real data: overdue/due-soon reminders and
 * budget categories near or over their limit.
 */
$navNotifications = [];
if (function_exists('current_user') && current_user()) {
	require_once __DIR__ . '/../includes/reminders.php';
	require_once __DIR__ . '/../includes/budget.php';
	$navUid = current_user()['id'];

	foreach (reminders_for_user($navUid, 'overdue') as $r) {
		$navNotifications[] = [
			'title' => 'Overdue: ' . $r['title'],
			'meta'  => 'Was due ' . date('j M', strtotime($r['due_date'])),
		];
	}
	$soonCutoff = date('Y-m-d', strtotime('+7 days'));
	foreach (reminders_for_user($navUid, 'pending') as $r) {
		if ($r['due_date'] <= $soonCutoff) {
			$navNotifications[] = [
				'title' => 'Due soon: ' . $r['title'],
				'meta'  => 'Due ' . date('j M', strtotime($r['due_date'])),
			];
		}
	}
	foreach (budget_categories_with_spent($navUid) as $c) {
		if ($c['limit_amount'] > 0 && ($c['spent'] / $c['limit_amount']) >= 0.9) {
			$pct = (int) round($c['spent'] / $c['limit_amount'] * 100);
			$navNotifications[] = [
				'title' => 'Budget alert: ' . $c['name'],
				'meta'  => $pct . '% of limit used',
			];
		}
	}
	$navNotifications = array_slice($navNotifications, 0, 6);
}
?>
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
					<?php if (!empty($navNotifications)): ?>
						<span class="notification-pill"><?= count($navNotifications) ?> New</span>
					<?php endif; ?>
				</div>
				<div class="notification-list">
					<?php if (empty($navNotifications)): ?>
						<div class="notification-item">
							<div>
								<p class="notification-meta">You're all caught up.</p>
							</div>
						</div>
					<?php else: ?>
						<?php foreach ($navNotifications as $n): ?>
							<div class="notification-item">
								<div class="notification-dot"></div>
								<div>
									<p class="notification-title"><?= e($n['title']) ?></p>
									<p class="notification-meta"><?= e($n['meta']) ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
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