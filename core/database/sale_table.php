<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/database.php';

class SaleTable
{
	public function getList() : array
	{
		$querySql = 'SELECT * FROM sale';
		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute();

		return $query->fetchAll();
	}
}