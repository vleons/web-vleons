<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/usersession.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/users_table.php';

class AuthLogics
{
	private $usersTable;
	private $errorMessages = [];
	private $uriThisPage;

	public function __construct()
	{
		$this->usersTable = new UsersTable();
	}

	public function executeAndShow()
	{

		$this->uriThisPage = explode('?', $_SERVER['REQUEST_URI'])[0];

		if (isset($_GET['logout']) && $_GET['logout'] == 'Y')
		{
			UserSession::logOut();
			header('Location: ' . $this->uriThisPage);
		}

		if (isset($_POST['action']) == 'auth')
			$this->logIn();

		$data = [
			'URI_THIS_PAGE' => $this->uriThisPage,
			'ERROR_MESSAGES' => $this->errorMessages,
			'USER_SESSION' => [
				'IS_AUTHORIZED' => UserSession::isAuthorized(),
				'DATA' => UserSession::getData()
			],
		];

		include 'auth_front.php';
	}


	private function logIn()
	{
		$user = $this->usersTable->getByEmail($_POST['email']);

		if ($user) {

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