<?php
/**
 * App bootstrap — load on every PHP entry point (pages + action handlers).
 * Loads config, helpers and session-based auth in the right order.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
