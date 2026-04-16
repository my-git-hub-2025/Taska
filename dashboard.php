<?php
/**
 * Taska – Generic dashboard redirect
 */
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
redirect_dashboard($user);
