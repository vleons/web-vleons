<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/products_table.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/sale_table.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/usersession.php';

class ProductListLogics
{

	private $productTable;
	private $saleTable;

	public function __construct()
	{
		$this->productTable = new ProductsTable();
		$this->saleTable = new SaleTable();
	}

	public function executeAndShow()
	{
		if (!UserSession::isAuthorized())
			header('Location: /auth/');

		if (isset($_GET['clearFilter'])){
			$products = $this->productTable->getListByFilter([]);
			$_GET = [];
		} else {
			$products = $this->productTable->getListByFilter($_GET);
		}

		$data = [
			'PRODUCTS' => $products,
			'SALES' => $this->saleTable->getList(),
		];

		include 'product_list_front.php';
	}
}