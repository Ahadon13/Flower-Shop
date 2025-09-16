<?php
include '../includes/db.php';
include 'header.php';
include '../models/Order.php';
include '../models/cart_item.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

// Initialize Order model
$orderModel = new Order($conn);
$cartItem = new CartItem($conn);
 
// Get products from URL parameters
$products = [];
$cart_item_ids = [];

if (isset($_GET['products']) && isset($_GET['quantities'])) {
    $product_ids = explode(',', $_GET['products']);
    $quantities = explode(',', $_GET['quantities']);
    
    if (count($product_ids) === count($quantities)) {
        for ($i = 0; $i < count($product_ids); $i++) {
            $products[(int)$product_ids[$i]] = (int)$quantities[$i];
        }
    }

    // Get cart item IDs for deletion
    if (isset($_GET['cart_ids'])) {
        $cart_item_ids = array_map('intval', explode(',', $_GET['cart_ids']));
    }
}

// Redirect if no products
if (empty($products)) {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'No products selected!',
            icon: 'error',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'home.php';
        });
    </script>";
    exit;
}

// Get product details and calculate total
$productDetails = $orderModel->getProductsWithDetails($products);
$totalAmount = $orderModel->calculateTotal($products);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $payment_method = $_POST['payment_method'];
        $fulfillment_method = $_POST['fulfillment_method'];
        
        // Prepare payment data
        $payment_data = [];
        if ($payment_method === 'gcash') {
            $payment_data['gcash_reference_number'] = $_POST['gcash_reference_number'];
            $payment_data['gcash_number'] = $_POST['gcash_number'];
            $payment_data['gcash_account_name'] = $_POST['gcash_account_name'];
            
            // Upload payment proof
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $uploaded_file = $orderModel->uploadPaymentProof($_FILES['payment_proof']);
                if ($uploaded_file) {
                    $payment_data['payment_proof_image'] = $uploaded_file;
                } else {
                    throw new Exception('Failed to upload payment proof');
                }
            } else {
                throw new Exception('Payment proof is required for GCash payment');
            }
        }
        
        // Prepare customer data
        $customer_data = [];
        if ($fulfillment_method === 'delivery') {
            $customer_data['delivery_address'] = $_POST['delivery_address'];
            $customer_data['contact_number'] = $_POST['contact_number'];
        }
        
        // Create order
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $order_id = $orderModel->createOrder($user_id, $totalAmount, $payment_method, $fulfillment_method, $customer_data, $payment_data);
        
        if ($order_id) {
            // Create order items
            $orderModel->createOrderItems($order_id, $products);

            // Delete cart items from database
            if (!empty($cart_item_ids)) {
                $cart_ids_string = implode(',', $cart_item_ids);
                $conn->query("DELETE FROM cart_items WHERE user_id = $user_id AND id IN ($cart_ids_string)");
                $cartItem->syncSessionCartItem(null, 'clear');
            }
            
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Your order has been placed successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'home.php';
                });
            </script>";
        } else {
            throw new Exception('Failed to create order');
        }
        
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: '" . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}
?>
<div class="checkout-container">
    <h2>Checkout</h2>

    <!-- Order Summary Section -->
    <div class="section">
        <h3>Order Summary</h3>
        <?php foreach ($productDetails as $product): ?>
        <div class="checkout-product-item">
            <img src="../uploads/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>" class="checkout-product-image">
            <div class="checkout-product-info">
                <div class="checkout-product-name"><?= htmlspecialchars($product['name']) ?></div>
                <div class="checkout-product-price">
                    ₱<?= number_format($product['price'], 2) ?> x <?= $product['quantity'] ?> =
                    ₱<?= number_format($product['subtotal'], 2) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="total-amount">
            Total: ₱<?= number_format($totalAmount, 2) ?>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" id="checkoutForm">
        <!-- Customer Information Section -->
        <div class="section">
            <h3>Customer Information</h3>
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"
                    value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" required>
            </div>
        </div>

        <!-- Payment Method Section -->
        <div class="section">
            <h3>Payment Method</h3>
            <div class="payment-options">
                <div class="radio-option" data-payment="cash">
                    <input type="radio" id="cash" name="payment_method" value="cash" required>
                    <label for="cash">Cash</label>
                </div>
                <div class="radio-option" data-payment="gcash">
                    <input type="radio" id="gcash" name="payment_method" value="gcash" required>
                    <label for="gcash">GCash</label>
                </div>
            </div>

            <div class="gcash-fields" id="gcashFields">

                <?php
                // Fetch GCash details from admins table
                $gcash_query = "SELECT gcash_number, gcash_name, gcash_qr_code FROM admins WHERE gcash_number IS NOT NULL LIMIT 1";
                $gcash_result = $conn->query($gcash_query);
                if ($gcash_result && $gcash_result->num_rows > 0) {
                    $gcash_info = $gcash_result->fetch_assoc();
                ?>
                <div>
                    <h4>GCash Payment Details</h4>
                    <p><strong>GCash Number:</strong> <?= htmlspecialchars($gcash_info['gcash_number']) ?></p>
                    <p><strong>Account Name:</strong> <?= htmlspecialchars($gcash_info['gcash_name']) ?></p>
                    <?php if (!empty($gcash_info['gcash_qr_code'])): ?>
                    <div class="gcash-qr">
                        <p><strong>QR Code:</strong></p>
                        <img src="../uploads/gcash/<?= htmlspecialchars($gcash_info['gcash_qr_code']) ?>"
                            alt="GCash QR Code" style="display: block; margin: 0 auto; width: 500px; height: 750px;">
                    </div>
                    <?php endif; ?>
                </div>
                <?php } ?>

                <div class="form-group">
                    <label for="gcash_reference_number">GCash Reference Number:</label>
                    <input type="text" id="gcash_reference_number" name="gcash_reference_number">
                </div>

                <div class="form-group">
                    <label for="gcash_number">GCash Number:</label>
                    <input type="text" id="gcash_number" name="gcash_number">
                </div>

                <div class="form-group">
                    <label for="gcash_account_name">GCash Account Name:</label>
                    <input type="text" id="gcash_account_name" name="gcash_account_name">
                </div>

                <div class="form-group">
                    <label for="payment_proof">Payment Proof (Image):</label>
                    <input type="file" id="payment_proof" name="payment_proof" accept="image/*">
                </div>
            </div>
        </div>

        <!-- Fulfillment Method Section -->
        <div class="section">
            <h3>How would you like to receive your order?</h3>
            <div class="fulfillment-options">
                <div class="radio-option" data-fulfillment="pickup">
                    <input type="radio" id="pickup" name="fulfillment_method" value="pickup" required>
                    <label for="pickup">Pickup</label>
                </div>
                <div class="radio-option" data-fulfillment="delivery">
                    <input type="radio" id="delivery" name="fulfillment_method" value="delivery" required>
                    <label for="delivery">Delivery</label>
                </div>
            </div>

            <div class="delivery-fields" id="deliveryFields">
                <div class="form-group">
                    <label for="delivery_address">Delivery Address:</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number:</label>
                    <input type="tel" id="contact_number" name="contact_number">
                </div>
            </div>
        </div>

        <button type="submit" class="checkout-btn">Place Order</button>
        <a href="flower_gallery.php" class="checkout-btn"
            style="background-color: #dc3545; text-decoration: none; display: inline-block; text-align: center; margin-top: 10px;">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment method selection
    const paymentOptions = document.querySelectorAll('.radio-option[data-payment]');
    const gcashFields = document.getElementById('gcashFields');

    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            const paymentType = this.dataset.payment;
            const radio = this.querySelector('input[type="radio"]');

            // Remove selected class from all options
            paymentOptions.forEach(opt => opt.classList.remove('selected'));

            // Add selected class to clicked option
            this.classList.add('selected');
            radio.checked = true;

            // Show/hide GCash fields
            if (paymentType === 'gcash') {
                gcashFields.style.display = 'block';
                // Make GCash fields required
                gcashFields.querySelectorAll('input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                gcashFields.style.display = 'none';
                // Remove required attribute from GCash fields
                gcashFields.querySelectorAll('input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    });

    // Handle fulfillment method selection
    const fulfillmentOptions = document.querySelectorAll('.radio-option[data-fulfillment]');
    const deliveryFields = document.getElementById('deliveryFields');

    fulfillmentOptions.forEach(option => {
        option.addEventListener('click', function() {
            const fulfillmentType = this.dataset.fulfillment;
            const radio = this.querySelector('input[type="radio"]');

            // Remove selected class from all options
            fulfillmentOptions.forEach(opt => opt.classList.remove('selected'));

            // Add selected class to clicked option
            this.classList.add('selected');
            radio.checked = true;

            // Show/hide delivery fields
            if (fulfillmentType === 'delivery') {
                deliveryFields.style.display = 'block';
                // Make delivery fields required
                deliveryFields.querySelectorAll('input, textarea').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                deliveryFields.style.display = 'none';
                // Remove required attribute from delivery fields
                deliveryFields.querySelectorAll('input, textarea').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    });

    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        const fulfillmentMethod = document.querySelector('input[name="fulfillment_method"]:checked');

        if (!paymentMethod) {
            e.preventDefault();
            Swal.fire({
                title: 'Error!',
                text: 'Please select a payment method.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!fulfillmentMethod) {
            e.preventDefault();
            Swal.fire({
                title: 'Error!',
                text: 'Please select how you want to receive your order.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
    });
});
</script>
<?php include 'footer.php'; ?>