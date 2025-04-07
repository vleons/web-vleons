<?php

class ProductTableModule {
    private $pdo;
    private $uploadDir = '/uploads/products/';

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnect();
        $this->initUploadDir();
    }

    private function initUploadDir() {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $this->uploadDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }

    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT 
                                 id, 
                                 COALESCE(name, '') as name, 
                                 COALESCE(sale_id, 0) as sale_id, 
                                 COALESCE(description, '') as description, 
                                 COALESCE(price, 0) as price, 
                                 COALESCE(img, '') as img 
                               FROM products ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById($id): ?array {
        $stmt = $this->pdo->prepare("SELECT 
                                    id, 
                                    COALESCE(name, '') as name, 
                                    COALESCE(sale_id, 0) as sale_id, 
                                    COALESCE(description, '') as description, 
                                    COALESCE(price, 0) as price, 
                                    COALESCE(img, '') as img 
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
            if (empty($cleanData['id'])) {
                return $this->insert($cleanData);
            } else {
                return $this->update($cleanData);
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Ошибка базы данных']];
        }
    }

    private function insert(array $data): array {
        $stmt = $this->pdo->prepare("INSERT INTO products (name, sale_id, description, price, img) 
                                    VALUES (?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['name'],
            !empty($data['sale_id']) ? $data['sale_id'] : null,
            $data['description'],
            $data['price'],
            $data['img'] ?? ''
        ]);
        
        return ['success' => $success, 'id' => $this->pdo->lastInsertId()];
    }

    private function update(array $data): array {
        $old = $this->findById($data['id']);
        if (!$old) {
            return ['success' => false, 'errors' => ['general' => 'Продукт не найден']];
        }
        
        if ((isset($data['img']) || (isset($data['delete_img']) && $data['delete_img'])) && !empty($old['img'])) {
            $this->deleteFile($old['img']);
        }
        
        $img = isset($data['img']) ? $data['img'] : (empty($data['delete_img']) ? $old['img'] : '');
        
        $stmt = $this->pdo->prepare("UPDATE products SET 
                                   name = ?, 
                                   sale_id = ?, 
                                   description = ?, 
                                   price = ?, 
                                   img = ? 
                                   WHERE id = ?");
        $success = $stmt->execute([
            $data['name'],
            !empty($data['sale_id']) ? $data['sale_id'] : null,
            $data['description'],
            $data['price'],
            $img,
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

    public function handleUpload($file): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
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

    private function deleteFile($path) {
        if (!empty($path)) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    private function sanitize(array $data): array {
        return [
            'id' => isset($data['id']) ? (int)$data['id'] : null,
            'name' => isset($data['name']) ? trim(htmlspecialchars($data['name'])) : '',
            'sale_id' => isset($data['sale_id']) ? (int)$data['sale_id'] : null,
            'description' => isset($data['description']) ? trim(htmlspecialchars($data['description'])) : '',
            'price' => isset($data['price']) ? (float)$data['price'] : 0,
            'img' => $data['img'] ?? null,
            'delete_img' => $data['delete_img'] ?? false
        ];
    }
}