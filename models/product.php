<?php
class Product {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getTotal() {
        $result = $this->conn->query("SELECT COUNT(*) as total FROM products");
        return $result->fetch_assoc()['total'];
    }

    public function getPaginated($limit, $offset) {
        $stmt = $this->conn->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function add($data, $file) {
        $errors = [];
        $name = trim($data['product_name']);
        $price = (float)$data['product_price'];
        $quantity = (int)$data['product_quantity'];
        $occasion = trim($data['product_occasion']);
        $description = trim($data['product_description']);

        if ($name === '' || $price <= 0 || $quantity <= 0 || $occasion === '' || $description === '') {
            $errors[] = "Please fill out all fields correctly.";
            return $errors;
        }

        if (empty($file['name'])) {
            $errors[] = "Please choose an image.";
            return $errors;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $allowed)) {
            $errors[] = "Unsupported image type.";
            return $errors;
        }

        $newFilename = time() . "_" . basename($file['name']);
        $destination = __DIR__ . "/../uploads/" . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = "Failed to upload image.";
            return $errors;
        }

        $stmt = $this->conn->prepare("INSERT INTO products (name, price, quantity, occasion, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdisss", $name, $price, $quantity, $occasion, $description, $newFilename);
        $stmt->execute();

        return $errors; // empty if success
    }

    public function update($id, $data, $file) {
        $name = trim($data['product_name']);
        $price = (float)$data['product_price'];
        $quantity = (int)$data['product_quantity'];
        $occasion = trim($data['product_occasion']);
        $description = trim($data['product_description']);

        $imageQuery = "";
        $params = [$name, $price, $quantity, $occasion, $description];
        $types = "sdiss";

        if (!empty($file['name'])) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                return ["Unsupported image type."];
            }
            $newFilename = time() . "_" . basename($file['name']);
            $destination = __DIR__ . "/../uploads/" . $newFilename;
            move_uploaded_file($file['tmp_name'], $destination);

            $imageQuery = ", image=?";
            $params[] = $newFilename;
            $types .= "s";
        }

        $params[] = $id;
        $types .= "i";

        $sql = "UPDATE products SET name=?, price=?, quantity=?, occasion=?, description=? {$imageQuery} WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return [];
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}