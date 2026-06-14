<?php
/**
 * Logout handler. Clears the session and returns to login.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

logout_user();
redirect('../pages/login.php');
