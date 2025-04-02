<?php

require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/modules/text/text_logics.php';

$textLogic = new TextLogic();
$_GET['preset'] = 4;
$textLogic->executeAndShow();

require $_SERVER['DOCUMENT_ROOT'] . '/footer.php';


