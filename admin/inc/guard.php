<?php
/** One-line include for every protected admin page: boots + enforces login. */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth.php';
require_admin();
