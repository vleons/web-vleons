<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/usersession.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/users_table.php';

class AuthLogics
{
	private $usersTable;
	private $errorMessages = [];
	private $uriThisPage;

	public function __construct()//Инициализирует подключение к таблице пользователей
	{
		$this->usersTable = new UsersTable();
	}

	public function executeAndShow()
	{

		$this->uriThisPage = explode('?', $_SERVER['REQUEST_URI'])[0];

		if (isset($_GET['logout']) && $_GET['logout'] == 'Y')// Обработка выхода
		{
			UserSession::logOut();
			header('Location: ' . $this->uriThisPage);
		}

		if (isset($_POST['action']) == 'auth')// Обработка авторизации
			$this->logIn();
// Подготовка данных для шаблона
		$data = [
			'URI_THIS_PAGE' => $this->uriThisPage,
			'ERROR_MESSAGES' => $this->errorMessages,
			'USER_SESSION' => [
				'IS_AUTHORIZED' => UserSession::isAuthorized(),
				'DATA' => UserSession::getData()
			],
		];

		include 'auth_front.php';//Отображает фронтенд-часть
	}


	private function logIn()//Получает пользователя по email из POST-данных.
	{
		$user = $this->usersTable->getByEmail($_POST['email']);//Пытается найти пользователя по email 
//Проверка существования пользователя
		if ($user) {
//Проверка пароля
			if (password_verify($_POST['password'], $user['PASSWORD_HASH'])) {
				UserSession::logIn($user['ID']);
				header('Location: /');
			} else {
				$this->errorMessages[] = 'Неверный пароль';
			}

		} else {
			$this->errorMessages[] = 'Нет пользователя с таким email';
		}
	}

}