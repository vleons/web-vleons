<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/users_table.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database/gender_table.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/usersession.php';

class RegistrationLogics
{

	private $usersTable;
	private $genderTable;
	private $errorMessages = [];
	private $uriThisPage;


	public function __construct()
	{
		$this->usersTable = new UsersTable();
		$this->genderTable = new GenderTable();
	}

	public function executeAndShow()
	{
		$this->uriThisPage = explode('?', $_SERVER['REQUEST_URI'])[0];

		if (isset($_GET['logout']) && $_GET['logout'] == 'Y')
		{
			UserSession::logOut();
			header('Location: ' . $this->uriThisPage);
		}

		if (isset($_POST['action']) && $_POST['action'] == 'registration')
			$this->registerUser();

		$data = [
			'URI_THIS_PAGE' => $this->uriThisPage,
			'GENDERS' => $this->genderTable->getList(),
			'ERROR_MESSAGES' => $this->errorMessages,
			'USER_SESSION' => [
				'IS_AUTHORIZED' => UserSession::isAuthorized(),
				'DATA' => UserSession::getData()
			],
		];

		include 'registration_front.php';
	}

	private function registerUser()
	{
		if ($_POST['password'] != $_POST['password_repeat']) {
			$this->errorMessages[] = 'Пароли не совпадают';
		}

		if (count($this->usersTable->getByEmail($_POST['email']))) {
			$this->errorMessages[] = 'Пользователь с таким email уже существует';
		}

		if (strlen($_POST['password']) < 6) {
			$this->errorMessages[] = 'Пароль меньше 6 символов';
		}

		if (!preg_match('/^(?=.*[\d])(?=.*[[:punct:]])(?=.*[a-z])(?=.*[A-Z])(?=.*[^А-Яа-я])(?=.*[\s])[a-zA-Z[:punct:]\s\d]{6,}$/', $_POST['password'])) {
			$this->errorMessages[] = 'Пароль должен быть длиннее 6 символов, обязательно содержит большие латинские буквы,' .
				'маленькие латинские буквы, спецсимволы (знаки препинания, арифметические действия и тп), пробел, дефис, ' .
				' подчеркивание и цифры. Русские буквы запрещены.';
		}

		if (count($this->errorMessages))
			return;

		// соль не требуется, т.к. password_hash - генерирут разные хеши для одного пароля
		$passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

		$userid = $this->usersTable->insert([
			'fio' => $_POST['fio'],
			'date_of_birth' => $_POST['date_of_birth'],
			'address' => $_POST['address'],
			'gender_id' => $_POST['gender_id'],
			'interests' => $_POST['interests'],
			'link_vk' => $_POST['link_vk'],
			'blood_type' => $_POST['blood_type'],
			'rh_factor' => $_POST['rh_factor'],
			'email' => $_POST['email'],
			'password_hash' => $passwordHash,
		]);

		if (!$userid) {
			$this->errorMessages[] = 'Что-то пошло не так. Пользователь не был добавлен в базу.';
		} else {
			UserSession::logIn($userid);
			header('Location: ' . $this->uriThisPage);
		}
	}

}

