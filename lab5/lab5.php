<?php
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

// Определяем активную вкладку
$action = $_GET['action'] ?? 'export';
?>

<div class="container mt-4">
    <h1 class="mb-4">Лабораторная работа №5</h1>
    
    <!-- Навигационные вкладки -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?= $action === 'export' ? 'active' : '' ?>" 
               href="?action=export">Экспорт данных</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $action === 'import' ? 'active' : '' ?>" 
               href="?action=import">Импорт данных</a>
        </li>
    </ul>
    
    <!-- Контент вкладок -->
    <div class="tab-content p-3 border border-top-0 rounded-bottom">
        <?php
        if ($action === 'export') {
            require_once 'export.php';
        } elseif ($action === 'import') {
            require_once 'import.php';
        } else {
            echo '<div class="alert alert-danger">Неверное действие</div>';
        }
        ?>
    </div>
</div>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/footer.php';