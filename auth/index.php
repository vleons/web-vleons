<?php
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/modules/auth/auth_logics.php';

$authLogics = new AuthLogics();
$authLogics->executeAndShow();

require $_SERVER['DOCUMENT_ROOT'] . '/footer.php';