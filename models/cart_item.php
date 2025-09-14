<?php
class CartItem {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add product to cart
    public function addToCart($user_id, $product_id, $quantity = 1) {
        $check = $this->conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $new_qty = $row['quantity'] + $quantity;

            $update = $this->conn->prepare("UPDATE cart_items SET quantity=? WHERE id=?");
            $update->bind_param("ii", $new_qty, $row['id']);
            return $update->execute();
        } else {
            $stmt = $this->conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            return $stmt->execute();
        }
    }

    // Remove a product from cart
    public function removeFromCart($user_id, $product_id) {
        $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id=? AND product_id=?");
        $stmt->bind_param("ii", $user_id, $product_id);
        return $stmt->execute();
    }

    // Update quantity
    public function updateQuantity($user_id, $product_id, $quantity) {
        $stmt = $this->conn->prepare("UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?");
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        return $stmt->execute();
    }

    // Get all cart items of user
    public function getCartItems($user_id) {
        $stmt = $this->conn->prepare("
            SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
            FROM cart_items c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Check if product exists in cart
    public function existsInCart($user_id, $product_id) {
        $stmt = $this->conn->prepare("SELECT id FROM cart_items WHERE user_id=? AND product_id=?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    // Add these helper functions at the top of your file after includes
    function syncSessionCartItem($product_id, $action, $quantity = 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
            return;
        }
        
        switch ($action) {
            case 'remove':
                if (isset($_SESSION['cart'][$product_id])) {
                    unset($_SESSION['cart'][$product_id]);
                }
                break;
                
            case 'update':
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                break;
                
            case 'clear':
                unset($_SESSION['cart']);
                break;
        }
    }

    function getSessionCartCount() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return 0;
        }
        
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}