<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/crud/products_table.php';

$tableModule = new ProductTableModule();
$message = '';
$errors = [];
$oldInput = [];
$baseUrl = 'main.php';

// Получаем все доступные свойства и скидки
$properties = $tableModule->findAllProperties();
$sales = $tableModule->findAllSales();

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
                            <select class="form-select" id="sale_id" name="sale_id">
                                <option value="">-- Без акции --</option>
                                <?php foreach ($sales as $sale): ?>
                                    <?php if (isset($sale['id'], $sale['name'], $sale['value'])): ?>
                                        <option value="<?= htmlspecialchars($sale['id']) ?>"
                                            <?= (isset($editingProduct['sale_id'])) && $editingProduct['sale_id'] == $sale['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sale['name']) ?> (<?= $sale['value'] ?>%)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="properties_id" class="form-label">Свойство продукта</label>
                            <select class="form-select" id="properties_id" name="properties_id">
                                <option value="">-- Выберите свойство --</option>
                                <?php foreach ($properties as $property): ?>
                                    <?php if (isset($property['id'], $property['description'])): ?>
                                        <option value="<?= htmlspecialchars($property['id']) ?>" 
                                            <?= (isset($editingProduct['properties_id'])) && $editingProduct['properties_id'] == $property['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($property['description']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
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
            <div class="card mb-4">
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
                                                    <?php 
                                                    $sale = array_filter($sales, function($s) use ($product) { 
                                                        return isset($s['id']) && $s['id'] == $product['sale_id']; 
                                                    });
                                                    $sale = reset($sale);
                                                    ?>
                                                    <?php if ($sale && isset($sale['name'], $sale['value'])): ?>
                                                        <span class="badge bg-info">
                                                            <?= htmlspecialchars($sale['name']) ?> (<?= $sale['value'] ?>%)
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Нет</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($product['properties_id'])): ?>
                                                    <?php 
                                                    $property = array_filter($properties, function($p) use ($product) { 
                                                        return isset($p['id']) && $p['id'] == $product['properties_id']; 
                                                    });
                                                    $property = reset($property);
                                                    ?>
                                                    <?= $property && isset($property['description']) ? htmlspecialchars($property['description']) : 'Не указано' ?>
                                                <?php else: ?>
                                                    Не указано
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($product['id'])): ?>
                                                    <div class="btn-group" role="group" style="white-space: nowrap;">
                                                        <a href="<?= $baseUrl ?>?action=edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-warning px-3">
                                                            <i class="fas fa-edit me-1"></i>Изменить
                                                        </a>
                                                        <a href="<?= $baseUrl ?>?action=delete&id=<?= $product['id'] ?>" 
                                                           class="btn btn-sm btn-danger px-3"
                                                           onclick="return confirm('Вы уверены, что хотите удалить этот продукт?')">
                                                            <i class="fas fa-trash me-1"></i>Удалить
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

            <!-- Таблица скидок -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Доступные скидки</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Размер скидки</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Нет доступных скидок</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sales as $sale): ?>
                                        <?php if (isset($sale['id'], $sale['name'], $sale['value'])): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($sale['id']) ?></td>
                                                <td><?= htmlspecialchars($sale['name']) ?></td>
                                                <td><?= htmlspecialchars($sale['value']) ?>%</td>
                                            </tr>
                                        <?php endif; ?>
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

<style>
/* Улучшаем отображение кнопок */
.btn-group .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Делаем кнопки более заметными */
.btn-warning {
    color: #000;
}

/* Улучшаем таблицу скидок */
.table-sm th, 
.table-sm td {
    padding: 0.3rem;
}

.form-select {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .form-select:hover {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .form-select-lg {
        min-height: 46px;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    
    .sale-badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
        background-color: #6c757d;
        color: white;
    }

    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
        border-radius: 0.25rem;
    }
    
    .bg-info {
        background-color: #0dcaf0!important;
    }
    
    .bg-secondary {
        background-color: #6c757d!important;
    }
</style>

<script>
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