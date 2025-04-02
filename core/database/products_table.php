<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/sale_table.php';

class ProductsTable
{

	public function getList() : array
	{
		$querySql = 'SELECT * FROM products';
		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute();

		return $query->fetchAll();
	}

	public function getListWithSale() : array
	{
		$querySql = "SELECT products.*, sale.NAME as SALE_NAME FROM products LEFT JOIN sale ON products.SALE_ID=sale.ID";
		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute();

		return $query->fetchAll();
	}

	public function getListByFilter(array $filter) : array
	{

		$querySql = "SELECT products.*, sale.NAME as SALE_NAME FROM products LEFT JOIN sale ON products.SALE_ID=sale.ID";

		$whereSql = [];
		$whereValue = [];
		if (isset($filter['product_id']) && intval($filter['product_id'])) {
			$whereSql[] =  "products.ID = :product_id";
			$whereValue['product_id'] = intval($filter['product_id']);
		}
		if (isset($filter['description']) && strlen(trim($filter['description']))) {
			$whereSql[] =  "products.DESCRIPTION LIKE CONCAT('%', :description, '%')";
			$whereValue['description'] = $filter['description'];
		}
		if (isset($filter['name']) && strlen(trim($filter['name']))) {
			$whereSql[] =  "products.name LIKE CONCAT('%', :name, '%')";
			$whereValue['name'] = $filter['name'];
		}
		if (isset($filter['price_from']) && $filter['price_from']) {
			$whereSql[] =  "products.PRICE > :price_from";
			$whereValue['price_from'] = intval($filter['price_from']);
		}
		if (isset($filter['price_to']) && $filter['price_to']) {
			$whereSql[] =  "products.PRICE < :price_to";
			$whereValue['price_to'] = intval($filter['price_to']);
		}
		if (isset($filter['saleId']) && $filter['saleId']) {
			$whereSql[] =  "products.SALE_ID = :saleId";
			$whereValue['saleId'] = intval($filter['saleId']);
		}

		if (count($whereSql)) {
			$querySql .=  ' WHERE ' . implode(' AND ', $whereSql);
			$query = Database::getInstance()->getConnect()->prepare($querySql);
			$query->execute($whereValue);
		} else {
			$query = Database::getInstance()->getConnect()->prepare($querySql);
			$query->execute();
		}

		return $query->fetchAll();
	}

}