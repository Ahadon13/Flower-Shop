<?php
include 'header.php';
include '../includes/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

// Fetch product details
$product_query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();

if ($product_result->num_rows === 0) {
    header('Location: products.php');
    exit();
}

$product = $product_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $phone_number = trim($_POST['phone_number']);
    $event_type = $_POST['event_type'];
    $event_time = $_POST['event_time'];
    $event_venue = trim($_POST['event_venue']);
    $mode_of_payment = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    
    $gcash_reference_number = null;
    $gcash_number = null;
    $gcash_account_name = null;
    $proof_of_payment = null;
    
    $errors = [];
    
    // Validation
    if (empty($phone_number)) {
        $errors[] = "Phone number is required";
    }
    if (empty($event_type)) {
        $errors[] = "Event type is required";
    }
    if (empty($event_time)) {
        $errors[] = "Event time is required";
    }
    if (empty($event_venue)) {
        $errors[] = "Event venue is required";
    }
    if (empty($mode_of_payment)) {
        $errors[] = "Payment method is required";
    }
    
    // GCash validation and file upload
    if ($mode_of_payment === 'gcash') {
        $gcash_reference_number = trim($_POST['gcash_reference_number']);
        $gcash_number = trim($_POST['gcash_number']);
        $gcash_account_name = trim($_POST['gcash_account_name']);
        
        if (empty($gcash_reference_number)) {
            $errors[] = "GCash reference number is required";
        }
        if (empty($gcash_number)) {
            $errors[] = "GCash number is required";
        }
        if (empty($gcash_account_name)) {
            $errors[] = "GCash account name is required";
        }
        
        // Handle file upload for payment proof
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
            $upload_dir = '../uploads/payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'proof_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_path)) {
                    $proof_of_payment = $new_filename;
                } else {
                    $errors[] = "Failed to upload payment proof";
                }
            } else {
                $errors[] = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed";
            }
        } else {
            $errors[] = "Payment proof is required for GCash payment";
        }
    }
    
    if (empty($errors)) {
        // Insert booking into database
        $insert_query = "INSERT INTO bookings (user_id, product_id, phone_number, event_type, event_time, event_venue, mode_of_payment, gcash_reference_number, gcash_number, gcash_account_name, proof_of_payment, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iissssssssss", $user_id, $product_id, $phone_number, $event_type, $event_time, $event_venue, $mode_of_payment, $gcash_reference_number, $gcash_number, $gcash_account_name, $proof_of_payment, $notes);
        
        if ($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Successful!',
                        text: 'Your booking has been submitted successfully.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#27ae60'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'occasions.php';
                        }
                    });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to submit booking. Please try again.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            </script>";
        }
    } else {
        $error_message = implode(', ', $errors);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error!',
                    text: '$error_message',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000
                });
            });
        </script>";
    }
}
?>
<style>
.checkout-container {
    padding: 20px;
    max-width: 900px;
    margin: 0 auto;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
}

.checkout-container h2,
.checkout-container h3 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.checkout-product-item {
    display: flex;
    align-items: center;
    padding: 15px;
    margin-bottom: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.checkout-product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 15px;
}

.checkout-product-info {
    flex-grow: 1;
}

.checkout-product-name {
    font-weight: bold;
    margin-bottom: 5px;
}

.checkout-product-price {
    color: #27ae60;
    font-size: 16px;
}

.total-amount {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    color: #27ae60;
    margin: 20px 0;
    padding: 15px;
    background: white;
    border-radius: 8px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.payment-options,
.fulfillment-options {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.radio-option {
    flex: 1;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.radio-option:hover {
    border-color: #27ae60;
}

.radio-option input[type="radio"] {
    margin-right: 10px;
}

.radio-option.selected {
    border-color: #27ae60;
    background-color: #e8f5e8;
}

.gcash-fields,
.delivery-fields {
    display: none;
    margin-top: 15px;
    padding: 15px;
    background: #f0f8f0;
    border-radius: 8px;
}

.checkout-btn {
    width: 100%;
    padding: 15px;
    background-color: #27ae60;
    color: white;
    border: none;
    font-size: 18px;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s;
}

.checkout-btn:hover {
    background-color: #2ecc71;
}

.section {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-info {
    background: #e8f4f8;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>

<div class="checkout-container">
    <h2>Book Product</h2>

    <!-- Product Details -->
    <div class="section">
        <h3>Product Details</h3>
        <div class="checkout-product-item">
            <img src="../uploads/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>" class="checkout-product-image">
            <div class="checkout-product-info">
                <div class="checkout-product-name"><?= htmlspecialchars($product['name']) ?></div>
                <div class="checkout-product-price">₱<?= number_format($product['price'], 2) ?></div>
                <p><?= htmlspecialchars($product['description']) ?></p>
            </div>
        </div>
        <div class="total-amount">
            Total Amount: ₱<?= number_format($product['price'], 2) ?>
        </div>
    </div>

    <!-- User Information -->
    <div class="section">
        <h3>Customer Information</h3>
        <div class="user-info">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($_SESSION['user_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user_email']) ?></p>
        </div>
    </div>

    <!-- Booking Form -->
    <form method="POST" enctype="multipart/form-data">
        <!-- Event Details Section -->
        <div class="section">
            <h3>Event Details</h3>

            <div class="form-group">
                <label for="phone_number">Phone Number *</label>
                <input type="tel" id="phone_number" name="phone_number" required>
            </div>

            <div class="form-group">
                <label for="event_type">Event Type *</label>
                <select id="event_type" name="event_type" required>
                    <option value="">Select Event Type</option>
                    <option value="funeral">Funeral</option>
                    <option value="birthday">Birthday</option>
                    <option value="valentine">Valentine</option>
                    <option value="wedding">Wedding</option>
                </select>
            </div>

            <div class="form-group">
                <label for="event_time">Event Date & Time *</label>
                <input type="datetime-local" id="event_time" name="event_time" required>
            </div>

            <div class="form-group">
                <label for="event_venue">Event Venue *</label>
                <input type="text" id="event_venue" name="event_venue" placeholder="Enter the venue address" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes/Special Requests</label>
                <textarea id="notes" name="notes"
                    placeholder="Any special requests or additional information..."></textarea>
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
                            alt="GCash QR Code" style="display: block; margin: 10px auto; width: 500px; height: 750px;">
                    </div>
                    <?php endif; ?>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label for="gcash_reference_number">GCash Reference Number *</label>
                    <input type="text" id="gcash_reference_number" name="gcash_reference_number">
                </div>
                <div class="form-group">
                    <label for="gcash_number">Your GCash Number *</label>
                    <input type="text" id="gcash_number" name="gcash_number">
                </div>
                <div class="form-group">
                    <label for="gcash_account_name">Your GCash Account Name *</label>
                    <input type="text" id="gcash_account_name" name="gcash_account_name">
                </div>
                <div class="form-group">
                    <label for="payment_proof">Payment Proof (Screenshot) *</label>
                    <input type="file" id="payment_proof" name="payment_proof" accept="image/*">
                </div>
            </div>
        </div>

        <button type="submit" class="checkout-btn">Submit Booking</button>
        <a href="occasions.php" class="checkout-btn"
            style="background-color: #dc3545; text-decoration: none; display: inline-block; text-align: center; margin-top: 10px;">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.radio-option[data-payment]');
    const gcashFields = document.getElementById('gcashFields');

    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            paymentOptions.forEach(opt => opt.classList.remove('selected'));

            // Add selected class to clicked option
            this.classList.add('selected');

            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            // Show/hide GCash fields
            if (this.dataset.payment === 'gcash') {
                gcashFields.style.display = 'block';
                // Make GCash fields required
                document.getElementById('gcash_reference_number').required = true;
                document.getElementById('gcash_number').required = true;
                document.getElementById('gcash_account_name').required = true;
                document.getElementById('payment_proof').required = true;
            } else {
                gcashFields.style.display = 'none';
                // Make GCash fields not required
                document.getElementById('gcash_reference_number').required = false;
                document.getElementById('gcash_number').required = false;
                document.getElementById('gcash_account_name').required = false;
                document.getElementById('payment_proof').required = false;
            }
        });
    });

    // Set minimum datetime to current datetime
    const eventTimeInput = document.getElementById('event_time');
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    eventTimeInput.min = now.toISOString().slice(0, 16);
});
</script>
<!-- Footer -->
<?php include 'footer.php'; ?>