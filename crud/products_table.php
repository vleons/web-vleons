<?php

abstract class AbstractCRUD {
    protected $pdo;
    protected $uploadDir = null;

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnect();
        $this->initUploadDir();
    }

    protected function initUploadDir() {
        if ($this->uploadDir) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadDir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        }
    }

    // Абстрактные методы, которые должны быть реализованы в дочерних классах
    abstract public function findAll(): array;
    abstract public function findById($id): ?array;
    abstract public function validate(array $data): array;
    abstract protected function sanitize(array $data): array;
    abstract public function save(array $data): array;
    abstract protected function insert(array $data): array;
    abstract protected function update(array $data): array;
    abstract public function delete($id): bool;
    abstract public function copy($id): bool;

    // Общие методы с реализацией
    public function handleUpload($file): ?string {
        if (!$this->uploadDir || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $_SERVER['DOCUMENT_ROOT'] . $this->uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->uploadDir . $filename;
        }
        
        return null;
    }

    protected function deleteFile($path) {
        if (!empty($path)) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}

class ProductTableModule extends AbstractCRUD {
    private $tableName = 'products';
    protected $uploadDir = '/uploads/products/';

    public function findAllProperties(): array {
        $stmt = $this->pdo->query("SELECT * FROM properties ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT 
                                 p.id, 
                                 p.img, 
                                 p.name, 
                                 p.sale_id,
                                 s.name as sale_name,
                                 s.value as sale_value,
                                 p.properties_id,
                                 pr.description as property_description,
                                 p.description, 
                                 p.price
                               FROM products p
                               LEFT JOIN sale s ON p.sale_id = s.id
                               LEFT JOIN properties pr ON p.properties_id = pr.id
                               ORDER BY p.id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findAllSales(): array {
        try {
            // Явно указываем поля для выборки
            $stmt = $this->pdo->query("SELECT id, name, value FROM sale ORDER BY value DESC");
            
            if (!$stmt) {
                throw new PDOException("Ошибка выполнения запроса: " . implode(" ", $this->pdo->errorInfo()));
            }
            
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Форсируем правильную структуру
            $result = [];
            foreach ($sales as $sale) {
                $result[] = [
                    'id' => $sale['id'] ?? null,
                    'name' => $sale['name'] ?? '',
                    'value' => $sale['value'] ?? 0
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Sales Error: " . $e->getMessage());
            return [
                ['id' => 1, 'name' => '33%', 'value' => 33],
                ['id' => 2, 'name' => '50%', 'value' => 50],
                ['id' => 3, 'name' => '10%', 'value' => 10],
                ['id' => 4, 'name' => 'Без скидки', 'value' => 0]
            ];
        }
    }

    public function findPropertyById($id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findById($id): ?array {
        $stmt = $this->pdo->prepare("SELECT 
                                    id, 
                                    COALESCE(img, '') as img, 
                                    COALESCE(name, '') as name, 
                                    COALESCE(sale_id, 0) as sale_id, 
                                    COALESCE(properties_id, 0) as properties_id,
                                    COALESCE(description, '') as description, 
                                    COALESCE(price, 0) as price
                                  FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function validate(array $data): array {
        $errors = [];
        
        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'Название обязательно';
        }
        
        if (!isset($data['price']) || $data['price'] === '') {
            $errors['price'] = 'Цена обязательна';
        } elseif (!is_numeric($data['price'])) {
            $errors['price'] = 'Цена должна быть числом';
        } elseif ($data['price'] <= 0) {
            $errors['price'] = 'Цена должна быть положительной';
        }
        
        return $errors;
    }

    public function save(array $data): array {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $cleanData = $this->sanitize($data);
        
        try {
            $this->pdo->beginTransaction();
            
            if (empty($cleanData['id'])) {
                $result = $this->insert($cleanData);
                $productId = $result['id'];
            } else {
                $result = $this->update($cleanData);
                $productId = $cleanData['id'];
            }
            
            if ($result['success'] && isset($cleanData['property_ids'])) {
                // Удаляем старые связи
                $stmt = $this->pdo->prepare("DELETE FROM properties WHERE product_id = ?");
                $stmt->execute([$productId]);
                
                // Добавляем новые связи
                if (!empty($cleanData['property_ids'])) {
                    $stmt = $this->pdo->prepare("INSERT INTO properties (product_id, property_id) VALUES (?, ?)");
                    foreach ($cleanData['property_ids'] as $propertyId) {
                        $stmt->execute([$productId, $propertyId]);
                    }
                }
            }
            
            $this->pdo->commit();
            return $result;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Ошибка базы данных']];
        }
    }

    protected function insert(array $data): array {
        $stmt = $this->pdo->prepare("INSERT INTO products 
                                    (img, name, sale_id, properties_id, description, price) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['img'],
            $data['name'],
            !empty($data['sale_id']) ? $data['sale_id'] : null,
            !empty($data['properties_id']) ? $data['properties_id'] : null,
            $data['description'],
            $data['price']
        ]);
        
        return ['success' => $success, 'id' => $this->pdo->lastInsertId()];
    }

    protected function update(array $data): array {
        $old = $this->findById($data['id']);
        if (!$old) {
            return ['success' => false, 'errors' => ['general' => 'Продукт не найден']];
        }
        
        if ((isset($data['img']) || (isset($data['delete_img']) && $data['delete_img'])) && !empty($old['img'])) {
            $this->deleteFile($old['img']);
        }
        
        $img = isset($data['img']) ? $data['img'] : (empty($data['delete_img']) ? $old['img'] : '');
        
        $stmt = $this->pdo->prepare("UPDATE products SET 
                                   img = ?,
                                   name = ?, 
                                   sale_id = ?, 
                                   properties_id = ?,
                                   description = ?, 
                                   price = ? 
                                   WHERE id = ?");
        $success = $stmt->execute([
            $img,
            $data['name'],
            !empty($data['sale_id']) ? $data['sale_id'] : null,
            !empty($data['properties_id']) ? $data['properties_id'] : null,
            $data['description'],
            $data['price'],
            $data['id']
        ]);
        
        return ['success' => $success];
    }

    public function delete($id): bool {
        $product = $this->findById($id);
        if (!$product) return false;
        
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $success = $stmt->execute([$id]);
        
        if ($success && !empty($product['img'])) {
            $this->deleteFile($product['img']);
        }
        
        return $success;
    }

    public function copy($id): bool {
        $product = $this->findById($id);
        if (!$product) return false;
        
        $copyData = [
            'name' => $product['name'] . ' (копия)',
            'sale_id' => $product['sale_id'],
            'description' => $product['description'],
            'price' => $product['price'],
            'img' => $product['img']
        ];
        
        return $this->insert($copyData)['success'];
    }

    protected function sanitize(array $data): array {
        return [
            'id' => isset($data['id']) ? (int)$data['id'] : null,
            'img' => $data['img'] ?? null,
            'name' => isset($data['name']) ? trim(htmlspecialchars($data['name'])) : '',
            'sale_id' => isset($data['sale_id']) ? (int)$data['sale_id'] : null,
            'properties_id' => isset($data['properties_id']) ? (int)$data['properties_id'] : null,
            'description' => isset($data['description']) ? trim(htmlspecialchars($data['description'])) : '',
            'price' => isset($data['price']) ? (float)$data['price'] : 0,
            'delete_img' => $data['delete_img'] ?? false
        ];
    }
}