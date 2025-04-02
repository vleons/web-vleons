<?php

class Database
{

	protected static $instance = null;
	protected $connect;
	protected $hostName;
	protected $userName;
	protected $password;
	protected $database;

	protected function __construct()
	{
		$this->parseFileDataForDb();
		$this->connect = new \PDO('mysql:host=' . $this->hostName . ';dbname=' . $this->database, $this->userName, $this->password);
		if (mysqli_connect_errno())
		{
			echo 'Error database:' . mysqli_connect_errno();
			exit();
		}
	}

	protected function parseFileDataForDb()
	{
		$dataDb = parse_ini_file('data-db.ini', false, INI_SCANNER_TYPED);
		$this->hostName = $dataDb['host-name'];
		$this->userName = $dataDb['user-name'];
		$this->password = $dataDb['password'];
		$this->database = $dataDb['database'];
	}

	public static function getInstance()
	{
		if (!static::$instance)
			static::$instance = new Database();
		return static::$instance;
	}

	public function getConnect()
	{
		return $this->connect;
	}

}