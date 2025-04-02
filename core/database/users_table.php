<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/database.php';

class UsersTable
{

	public function insert(array $data) : int
	{
		$querySql = "INSERT INTO users (`FIO`, `DATE_OF_BIRTH`, `ADDRESS`, `GENDER_ID`, `INTERESTS`, `LINK_VK`, `BLOOD_TYPE`, `RH_FACTOR`,`EMAIL`,`PASSWORD_HASH`)" .
			" VALUES (:FIO, :DATE_OF_BIRTH, :ADDRESS, :GENDER_ID, :INTERESTS, :LINK_VK, :BLOOD_TYPE, :RH_FACTOR, :EMAIL, :PASSWORD_HASH)";
		$insertData = [
			'FIO' => $data['fio'],
			'DATE_OF_BIRTH' => DateTime::createFromFormat('Y-m-d', $data['date_of_birth'])->format('Y-m-d'),
			'ADDRESS' => $data['address'],
			'GENDER_ID' => $data['gender_id'],
			'INTERESTS' => $data['interests'],
			'LINK_VK' => $data['link_vk'],
			'BLOOD_TYPE' => $data['blood_type'],
			'RH_FACTOR' => $data['rh_factor'],
			'EMAIL' => $data['email'],
			'PASSWORD_HASH' => $data['password_hash']
		];

		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute($insertData);

		return Database::getInstance()->getConnect()->lastInsertId() ?: 0;
	}

	public function getByEmail(string $email) : array
	{
		$querySql = "SELECT users.*, gender.NAME AS `GENDER_NAME` FROM users " .
			"LEFT JOIN gender ON users.GENDER_ID = gender.ID " .
			"WHERE users.EMAIL = :email";

		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute(['email' => $email]);

		return $query->fetch() ?: [];
	}

	public function getById(int $id) : array
	{
		$querySql = "SELECT users.*, gender.NAME AS `GENDER_NAME` FROM users " .
			"LEFT JOIN gender ON users.GENDER_ID = gender.ID " .
			"WHERE users.ID = :id";

		$query = Database::getInstance()->getConnect()->prepare($querySql);
		$query->execute(['id' => $id]);

		return $query->fetch() ?: [];
	}


}