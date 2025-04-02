<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$exportType = 'json'; // Формат файла
$exportDestination = 'download'; // Способ выгрузки (скачивание)
$tableName = 'users'; // Главная таблица из ЛР2
$exportFileName = $tableName . '_exported.' . $exportType;

// Обработка экспорта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    // Отключаем вывод любых данных кроме JSON
ob_clean();

try {
    $db = Database::getInstance();
    $pdo = $db->getConnect();
    
    // Получаем данные из таблицы users
    $stmt = $pdo->query("SELECT * FROM users");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Устанавливаем заголовки для скачивания JSON
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="users_exported.json"');
    
    // Выводим только JSON данные
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Завершаем выполнение скрипта
    exit;

} catch (PDOException $e) {
    // В случае ошибки выводим чистый JSON с ошибкой
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Экспорт данных (Вариант 14)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .variant-info {
            background: #f0f7ff;
            padding: 15px;
            border-left: 4px solid #4a90e2;
            margin-bottom: 20px;
        }
        .export-btn {
            display: block;
            width: 200px;
            margin: 0 auto;
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .export-btn:hover {
            background-color: #45a049;
        }
        .instructions {
            margin-top: 30px;
            padding: 15px;
            background: #fff8e1;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Экспорт данных</h1>
        
        <div class="variant-info">
            <h3>Параметры варианта №14:</h3>
            <p><strong>Главная таблица:</strong> <?= $tableName ?></p>
            <p><strong>Формат файла:</strong> JSON</p>
            <p><strong>Способ выгрузки:</strong> Скачивание файла</p>
        </div>
        
        <form method="post">
            <button type="submit" name="export" class="export-btn">Экспорт в JSON</button>
        </form>
        
        <div class="instructions">
            <h3>Инструкция:</h3>
            <ol>
                <li>Нажмите кнопку "Экспорт в JSON"</li>
                <li>Файл <?= $exportFileName ?> будет автоматически скачан</li>
                <li>Для проверки откройте скачанный файл в текстовом редакторе</li>
            </ol>
        </div>
    </div>
</body>
</html>