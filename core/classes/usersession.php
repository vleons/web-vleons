<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/users_table.php';

class UserSession
{
	
	private static $isLoadedUserData = false;
	private static $data = [];

	static public function isAuthorized() : bool
	{
		return isset($_SESSION['USER_ID']) || false;
	}

	static public function logOut()
	{
		session_destroy();
		static::clear();
	}

	static public function logIn($userId)
	{
		$_SESSION['USER_ID'] = $userId;
	}

	static public function getId()
	{
		return $_SESSION['USER_ID'];
	}

	static public function getData()
	{
		if (static::$isLoadedUserData || static::loadUser())
			return static::$data;
		else
			return false;
	}
	
	static private function loadUser() : bool
	{
		if (static::isAuthorized())
		{
			$userTable = new UsersTable();
			$user = $userTable->getById(static::getId());
			
			if ($user)
			{
				static::$data = $user;
				static::$isLoadedUserData = true;
				return true;
			}
		}
		return false;
	}

	static private function clear()
	{
		static::$isLoadedUserData = false;
		static::$data = [];
	}

}