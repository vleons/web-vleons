<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crud/products_table.php';

$tableModule = new ProductTableModule();
$message = '';
$errors = [];
$oldInput = [];
$baseUrl = 'main.php';

// Получаем все доступные свойства
$properties = $tableModule->findAllProperties();

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $oldInput = $data;
    
    // Обработка изображения
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $data['img'] = $tableModule->handleUpload($_FILES['img']);
        if (!$data['img']) {
            $errors['img'] = 'Ошибка загрузки изображения';
        }
    } elseif (isset($_POST['delete_img']) && $_POST['delete_img'] == 'on') {
        $data['img'] = '';
    } else {
        $data['img'] = $_POST['existing_img'] ?? '';
    }
    
    if (empty($errors)) {
        $result = $tableModule->save($data);
        if ($result['success']) {
            $message = 'Данные успешно сохранены!';
            $oldInput = [];
            if (isset($data['id'])) {
                header("Location: $baseUrl");
                exit;
            }
        } else {
            $errors = $result['errors'];
        }
    }
}

// Обработка GET-запросов
$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($action === 'delete' && $id) {
    if ($tableModule->delete($id)) {
        $message = 'Продукт удален!';
    }
} elseif ($action === 'copy' && $id) {
    if ($tableModule->copy($id)) {
        $message = 'Продукт скопирован!';
    } else {
        $message = 'Ошибка при копировании!';
    }
}

$products = $tableModule->findAll();
$editingProduct = ($action === 'edit' && $id) ? $tableModule->findById($id) : null;
$sales = $tableModule->findAllSales();

?>
<div class="container mt-4">
    <h1 class="mb-4">Управление продуктами</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?= strpos($message, 'успешно') !== false ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <?= $editingProduct ? 'Редактирование' : 'Добавление' ?> продукта
                    </h3>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <?php if ($editingProduct && isset($editingProduct['id'])): ?>
                            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$editingProduct['id']) ?>">
                            <input type="hidden" name="existing_img" value="<?= isset($editingProduct['img']) ? htmlspecialchars((string)$editingProduct['img']) : '' ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Название *</label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   id="name" name="name" 
                                   value="<?= htmlspecialchars((string)($oldInput['name'] ?? $editingProduct['name'] ?? '')) ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sale_id" class="form-label">Акция</label>
                            <?php
                            // Получаем список скидок
                            $sales = $tableModule->findAllSales();
                            
                            // Проверяем, есть ли скидки в базе
                            if (empty($sales)) {
                                echo '<div class="alert alert-warning">Нет доступных акций. Сначала создайте акции в разделе управления скидками.</div>';
                            } else {
                            ?>
                            <select class="form-select" id="sale_id" name="sale_id" required>
                                <option value="">-- Выберите акцию --</option>
                                <?php foreach ($sales as $sale): ?>
                                    <?php if (isset($sale['id'], $sale['name'], $sale['value'])): ?>
                                        <option value="<?= htmlspecialchars($sale['id']) ?>"
                                            <?= (isset($editingProduct['sale_id']) && $editingProduct['sale_id'] == $sale['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sale['name']) ?> (<?= $sale['value'] ?>%)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <?php } ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="properties_id" class="form-label">Свойство продукта</label>
                            <select class="form-select form-select-lg" id="properties_id" name="properties_id" style="font-size: 1rem; padding: 0.5rem 1rem; height: auto;">
                                <option value="">-- Выберите свойство --</option>
                                <?php if (!empty($properties)): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <?php if (isset($property['id'], $property['description'])): ?>
                                            <option value="<?= htmlspecialchars((string)$property['id']) ?>" 
                                                <?= (isset($oldInput['properties_id']) && $oldInput['properties_id'] == $property['id']) || 
                                                    (isset($editingProduct['properties_id']) && $editingProduct['properties_id'] == $property['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($property['description']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?= htmlspecialchars((string)($oldInput['description'] ?? $editingProduct['description'] ?? '')) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Цена *</label>
                            <input type="number" step="0.01" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                   id="price" name="price" 
                                   value="<?= htmlspecialchars((string)($oldInput['price'] ?? $editingProduct['price'] ?? '')) ?>" required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="img" class="form-label">Изображение</label>
                            <input type="file" class="form-control <?= isset($errors['img']) ? 'is-invalid' : '' ?>" 
                                   id="img" name="img" accept="image/jpeg,image/png,image/gif">
                            <?php if (isset($errors['img'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['img']) ?></div>
                            <?php endif; ?>
                            
                            <?php if ($editingProduct && !empty($editingProduct['img'])): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars((string)$editingProduct['img']) ?>" class="img-thumbnail" style="max-height: 100px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="delete_img" id="delete_img">
                                        <label class="form-check-label" for="delete_img">Удалить изображение</label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?= $editingProduct ? 'Обновить' : 'Добавить' ?>
                        </button>
                        
                        <?php if ($editingProduct): ?>
                            <a href="<?= $baseUrl ?>" class="btn btn-secondary">Отмена</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список продуктов</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Изображение</th>
                                    <th>Название</th>
                                    <th>Цена</th>
                                    <th>Акция</th>
                                    <th>Свойство</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Нет продуктов</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['id'] ?? '') ?></td>
                                            <td>
                                                <?php if (!empty($product['img'])): ?>
                                                    <img src="<?= htmlspecialchars($product['img']) ?>" class="img-thumbnail" style="max-height: 50px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($product['name'] ?? '') ?></td>
                                            <td><?= number_format((float)($product['price'] ?? 0), 2, '.', '') ?> ₽</td>
                                            <td>
                                                <?php if (!empty($product['sale_id'])): ?>
                                                    <span class="badge bg-info">
                                                        <?= htmlspecialchars($product['sale_name'] ?? '') ?> 
                                                        (<?= htmlspecialchars($product['sale_value'] ?? 0) ?>%)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Нет акции</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= !empty($product['property_description']) ? htmlspecialchars($product['property_description']) : 'Не указано' ?>
                                            </td>
                                            <td>
                                                <?php if (isset($product['id'])): ?>
                                                    <div class="btn-group">
                                                        <a href="main.php?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="main.php?action=delete&id=<?= $product['id'] ?>" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Удалить продукт?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript валидация формы (остается без изменений)
document.querySelector('form').addEventListener('submit', function(e) {
    let valid = true;
    const name = this.elements.name;
    const price = this.elements.price;
    
    if (!name.value.trim()) {
        if (!name.nextElementSibling) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            name.parentNode.appendChild(errorDiv);
        }
        name.classList.add('is-invalid');
        name.nextElementSibling.textContent = 'Название обязательно';
        valid = false;
    }
    
    if (!price.value || isNaN(price.value) || parseFloat(price.value) <= 0) {
        if (!price.nextElementSibling) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            price.parentNode.appendChild(errorDiv);
        }
        price.classList.add('is-invalid');
        price.nextElementSibling.textContent = 'Цена должна быть положительным числом';
        valid = false;
    }
    
    if (!valid) {
        e.preventDefault();
        const firstError = document.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        if (this.nextElementSibling && this.nextElementSibling.classList.contains('invalid-feedback')) {
            this.nextElementSibling.textContent = '';
        }
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/footer.php'; ?>