<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/classes/usersession.php';
?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Аптека</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
			crossorigin="anonymous"></script>

</head>
<body>
<header >
    <div class="d-flex justify-content-start align-items-center m-3">
        <img src="/img/apteka.ru.png" style="width: 10%; height: 10%;">

        <div class="d-flex justify-content-start align-items-center" style="width: 10%; height: auto">
            <img src="/img/pngwing.com.png" style="width: 14px;height: auto;">
            <h6 style="color: #1c257b; margin-left: 4px; margin-bottom: 4px">Москва</h6>
        </div>

        <form class="d-flex justify-content-start m-1" style="width: 60%; height: 44px">
            <input style="border-radius: 20px 0 0 20px; width: 90%; border: 1px solid gray" placeholder="Введите название товара, заболевания или симптома">
            <button style=" width: 10%; background-color: #4665d7; border-radius: 0 20px 20px 0; border: 1px solid gray; color: aliceblue" type="submit">Искать</button>
        </form>

        <div class="ml-2 d-flex align-items-center" style="width: 20%">
			<?php if (UserSession::isAuthorized()) : ?>
                <div>
                    Вы вошли как <strong><?=UserSession::getData()['EMAIL']?></strong>.
                    <a href="/auth/?logout=Y">Выйти</a>
                </div>
			<?php else : ?>
                <div>
                    Вы не авторизованы <br>
                    <a href="/auth/">Войти</a> или <a href="/registration/">зарегистрироваться</a>.
                </div>
			<?php endif; ?>
        </div>
    </div>
    <div class="d-flex justify-content-center align-items-center mb-2 ml-5 mr-5">
        <a class="ml-2 mr-2" href="/">
            <h6 style="margin-bottom: 0">Главвная страница</h6>
        </a>
        |
        <a class="ml-2 mr-2" href="/products/">
            <h6 style="margin-bottom: 0">Страница с товарами(секретная страница)</h6>
        </a>
        |
        <a class="ml-2 mr-2" href="/text.php">
            <h6 style="margin-bottom: 0">Лабораторная работа №4</h6>
        </a>
        |
        <a class="ml-2 mr-2" href="/lab5/lab5.php">
            <h6 style="margin-bottom: 0">Лабораторная работа №5</h6>
        </a>
    </div>

</header>
<main>
	<div class="container">