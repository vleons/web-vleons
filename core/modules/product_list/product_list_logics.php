<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/products_table.php'; // импорт зависимостей
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/sale_table.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/usersession.php';

class ProductListLogics
{

	private $productTable;
	private $saleTable;

	public function __construct() //инициализация подключения к таблицам (продукта и скидок)
	{
		$this->productTable = new ProductsTable();
		$this->saleTable = new SaleTable();
	}

	public function executeAndShow()
	{
		if (!UserSession::isAuthorized()) //проверяется авторизация пользователя. 
			header('Location: /auth/');

		if (isset($_GET['clearFilter'])){// очистка фильтра
			$products = $this->productTable->getListByFilter([]);
			$_GET = [];
		} else {
			$products = $this->productTable->getListByFilter($_GET);
		}

		$data = [//формирование массива данных
			'PRODUCTS' => $products,
			'SALES' => $this->saleTable->getList(),
		];

		include 'product_list_front.php';//отображение данных
	}
}