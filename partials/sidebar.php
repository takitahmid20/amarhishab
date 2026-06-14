<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar app-sidebar dashboard-sidebar">
	<div class="sidebar-section">
		<div class="nav-group-title">Main</div>
		<nav class="nav">
			<a href="./dashboard.php"<?= $current === 'dashboard.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="home" aria-hidden="true"></i><span>Dashboard</span></a>
			<a href="./transactions.php"<?= $current === 'transactions.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="arrow-left-right" aria-hidden="true"></i><span>Transactions</span></a>
			<a href="./budget.php"<?= $current === 'budget.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="wallet" aria-hidden="true"></i><span>Budget</span></a>
			<a href="./reports.php"<?= $current === 'reports.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="bar-chart-3" aria-hidden="true"></i><span>Reports</span></a>
		</nav>
	</div>

	<div class="sidebar-section">
		<div class="nav-group-title">Manage</div>
		<nav class="nav">
			<a href="./cashbooks.php"<?= $current === 'cashbooks.php' || $current === 'cashbook-details.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="book-open" aria-hidden="true"></i><span>Cashbooks</span></a>
			<a href="./borrow-lend.php"<?= $current === 'borrow-lend.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="handshake" aria-hidden="true"></i><span>Borrow / Lend</span></a>
			<a href="./reminders.php"<?= $current === 'reminders.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="bell" aria-hidden="true"></i><span>Reminders</span></a>
			<a href="./settings.php"<?= $current === 'settings.php' ? ' class="active"' : '' ?>><i class="nav-icon" data-lucide="settings" aria-hidden="true"></i><span>Settings</span></a>
		</nav>
	</div>
</aside>
