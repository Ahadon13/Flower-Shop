<?php
class Order {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getProduct($product_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function createOrder($user_id, $total, $payment_method, $fulfillment_method, $customer_data = [], $payment_data = []) {
        $stmt = $this->conn->prepare("
            INSERT INTO orders (
                user_id, total, payment_method, gcash_reference_number, 
                gcash_number, gcash_account_name, payment_proof_image, 
                fulfillment_method, delivery_address, contact_number, 
                order_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $gcash_ref = $payment_method === 'gcash' ? $payment_data['gcash_reference_number'] : null;
        $gcash_number = $payment_method === 'gcash' ? $payment_data['gcash_number'] : null;
        $gcash_name = $payment_method === 'gcash' ? $payment_data['gcash_account_name'] : null;
        $proof_image = $payment_method === 'gcash' ? $payment_data['payment_proof_image'] : null;
        $delivery_address = $fulfillment_method === 'delivery' ? $customer_data['delivery_address'] : null;
        $contact_number = $fulfillment_method === 'delivery' ? $customer_data['contact_number'] : null;
        
        $stmt->bind_param(
            "idssssssss", 
            $user_id, $total, $payment_method, $gcash_ref, 
            $gcash_number, $gcash_name, $proof_image, 
            $fulfillment_method, $delivery_address, $contact_number
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function deductProductQuantity($product_id, $quantity) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET quantity = quantity - ? 
            WHERE id = ? AND quantity >= ?
        ");
        $stmt->bind_param("iii", $quantity, $product_id, $quantity);
        $stmt->execute();
        
        // Check if the update was successful (affected rows > 0)
        return $stmt->affected_rows > 0;
    }
    
    public function createOrderItems($order_id, $products) {
        foreach ($products as $product_id => $quantity) {
            $product = $this->getProduct($product_id);
            if ($product) {
                // Deduct quantity from product stock
                $this->deductProductQuantity($product_id, $quantity);
                // Create individual order items for each quantity
                for ($i = 0; $i < $quantity; $i++) {
                    $stmt = $this->conn->prepare("
                        INSERT INTO order_items (order_id, product_id, price) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->bind_param("iid", $order_id, $product_id, $product['price']);
                    $stmt->execute();
                }
            }
        }
        return true;
    }
    
    public function uploadPaymentProof($file) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }
        
        $upload_dir = '../uploads/payment_proofs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            return false;
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }
    
    public function calculateTotal($products) {
        $total = 0;
        foreach ($products as $product_id => $quantity) {
            $product = $this->getProduct($product_id);
            if ($product) {
                $total += $product['price'] * $quantity;
            }
        }
        return $total;
    }
    
    public function getProductsWithDetails($products) {
        $product_details = [];
        foreach ($products as $product_id => $quantity) {
            $product = $this->getProduct($product_id);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $product_details[] = $product;
            }
        }
        return $product_details;
    }
}