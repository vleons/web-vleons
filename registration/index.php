<?php
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/modules/registration/registration_logics.php';

$registrationLogics = new RegistrationLogics();
$registrationLogics->executeAndShow();

require $_SERVER['DOCUMENT_ROOT'] . '/footer.php';