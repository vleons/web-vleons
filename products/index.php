<?php
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/modules/product_list/product_list_logics.php';

$productListLogics = new ProductListLogics();
$productListLogics->executeAndShow();

require $_SERVER['DOCUMENT_ROOT'] . '/footer.php';