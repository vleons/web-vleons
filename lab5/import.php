<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = Database::getInstance();
$pdo = $db->getConnect();

$tableName = 'users'; // Главная таблица из ЛР2
$message = '';

// Обработка загрузки файла
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['import_file'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($fileType !== 'json') {
            $message = "Ошибка: поддерживается только JSON формат";
        } else {
            $content = file_get_contents($file['tmp_name']);
            
            try {
                $pdo->beginTransaction();
                
                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Ошибка парсинга JSON: " . json_last_error_msg());
                }
                
                foreach ($data as $row) {
                    $columns = implode(', ', array_keys($row));
                    $values = ':' . implode(', :', array_keys($row));
                    
                    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($row);
                }
                
                $pdo->commit();
                $message = "Данные из JSON файла успешно импортированы в таблицу $tableName";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Ошибка импорта: " . $e->getMessage();
            }
        }
    } else {
        $message = "Ошибка загрузки файла";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Импорт JSON (Вариант 14)</title>
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
        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success {
            background: #e6ffed;
            border-left: 4px solid #2ea44f;
            color: #2c974b;
        }
        .error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #d32f2f;
        }
        .import-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .import-btn:hover {
            background-color: #45a049;
        }
        .file-input {
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Импорт данных</h1>
        
        <div class="variant-info">
            <h3>Параметры варианта №14:</h3>
            <p><strong>Главная таблица:</strong> <?= $tableName ?></p>
            <p><strong>Поддерживаемый формат:</strong> JSON</p>
            <p><strong>Максимальный размер файла:</strong> 10MB</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Ошибка') === false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="file-input">
                <input type="file" name="import_file" accept=".json" required>
            </div>
            <button type="submit" name="import" class="import-btn">Импортировать JSON</button>
        </form>
    </div>
</body>
</html>