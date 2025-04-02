<?php
// Папка для хранения загруженных файлов
$uploadDir = 'files/';

// Обработка загрузки файла
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $targetPath = $uploadDir . basename($filename);
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        echo "Файл $filename успешно загружен.";
    } else {
        echo "Ошибка при загрузке файла.";
    }
    exit;
}

// Обработка скачивания файла
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filePath = $uploadDir . $filename;
    
    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filePath);
        exit;
    } else {
        die("Файл не найден.");
    }
}
?>