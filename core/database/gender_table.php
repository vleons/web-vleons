<?php


class GenderTable
{
	public function getList() : array
	{
		$querySql = 'SELECT * FROM gender';
		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute();

		return $query->fetchAll();
	}
}