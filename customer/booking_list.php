<?php
include 'header.php';
include '../includes/db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get search parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$event_type = isset($_GET['event_type']) ? $_GET['event_type'] : '';

// Build the query with search conditions
$whereConditions = ['b.user_id = ?'];
$params = [$user_id];
$paramTypes = 'i';

// Search query (booking ID or product name)
if (!empty($search_query)) {
    $whereConditions[] = "(b.id LIKE ? OR p.name LIKE ?)";
    $searchParam = '%' . $search_query . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $paramTypes .= 'ss';
}

// Date range filter
if (!empty($date_from)) {
    $whereConditions[] = "DATE(b.created_at) >= ?";
    $params[] = $date_from;
    $paramTypes .= 's';
}

if (!empty($date_to)) {
    $whereConditions[] = "DATE(b.created_at) <= ?";
    $params[] = $date_to;
    $paramTypes .= 's';
}

// Payment method filter
if (!empty($payment_method)) {
    $whereConditions[] = "b.mode_of_payment = ?";
    $params[] = $payment_method;
    $paramTypes .= 's';
}

// Event type filter
if (!empty($event_type)) {
    $whereConditions[] = "b.event_type = ?";
    $params[] = $event_type;
    $paramTypes .= 's';
}

// Build the final query
$bookingsQuery = "SELECT b.*, p.name as product_name, p.image as product_image, p.price as product_price, p.description as product_description, u.full_name as user_name, u.email as user_email 
                  FROM bookings b 
                  JOIN products p ON b.product_id = p.id 
                  JOIN users u ON b.user_id = u.id 
                  WHERE " . implode(' AND ', $whereConditions) . " 
                  ORDER BY b.created_at DESC";

$stmt = $conn->prepare($bookingsQuery);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$bookingsResult = $stmt->get_result();
$bookings = $bookingsResult->fetch_all(MYSQLI_ASSOC);

// Get available payment and event types for filter dropdowns
$paymentMethods = ['cash', 'gcash'];
$eventTypes = ['funeral', 'birthday', 'valentine', 'wedding'];
?>

<style>
/* Orders page */
.customer-orders-details-orders-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.customer-orders-details-orders-container h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.customer-orders-details-order-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.customer-orders-details-order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.customer-orders-details-order-header {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.customer-orders-details-order-info {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.customer-orders-details-order-detail {
    display: flex;
    flex-direction: column;
}

.customer-orders-details-order-detail-label {
    font-size: 12px;
    opacity: 0.9;
    margin-bottom: 2px;
}

.customer-orders-details-order-detail-value {
    font-size: 16px;
    font-weight: bold;
}

.customer-orders-details-order-total {
    text-align: right;
}

.customer-orders-details-order-total .amount {
    font-size: 24px;
    font-weight: bold;
}

.customer-orders-details-payment-method {
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.payment-gcash {
    background: #007bff;
    color: white;
}

.payment-cash {
    background: #28a745;
    color: white;
}

.event-type-badge {
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.event-funeral {
    background: #6c757d;
    color: white;
}

.event-birthday {
    background: #ffc107;
    color: #212529;
}

.event-valentine {
    background: #e91e63;
    color: white;
}

.event-wedding {
    background: #17a2b8;
    color: white;
}

.customer-orders-details-order-content {
    padding: 0;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

.customer-orders-details-order-content.expanded {
    max-height: 1000px;
    padding: 20px;
}

.customer-orders-details-order-items {
    margin-bottom: 20px;
}

.customer-orders-details-item-row {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.customer-orders-details-item-row:last-child {
    border-bottom: none;
}

.customer-orders-details-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 15px;
}

.customer-orders-details-item-info {
    flex-grow: 1;
}

.customer-orders-details-item-name {
    font-weight: bold;
    margin-bottom: 5px;
}

.customer-orders-details-item-details {
    color: #666;
    font-size: 14px;
}

.customer-orders-details-item-price {
    text-align: right;
    font-weight: bold;
    color: #27ae60;
    font-size: 20px;
}

.customer-orders-details-order-details-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.customer-orders-details-detail-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.customer-orders-details-detail-box h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 16px;
}

.customer-orders-details-detail-box p {
    margin: 5px 0;
    color: #666;
}

.customer-orders-details-payment-proof {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.customer-orders-details-payment-proof:hover {
    transform: scale(1.05);
}

.customer-orders-details-expand-icon {
    transition: transform 0.3s;
    transform-origin: center center;
    display: inline-block;
}

.customer-orders-details-expand-icon.rotated {
    transform: rotate(180deg);
}

.customer-orders-details-no-orders {
    text-align: center;
    padding: 50px;
    color: #666;
}

.customer-orders-search-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.customer-orders-search-form {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
}

.customer-orders-search-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.customer-orders-search-field {
    display: flex;
    flex-direction: column;
}

.customer-orders-search-field label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 14px;
}

.customer-orders-search-field input,
.customer-orders-search-field select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.customer-orders-search-field input:focus,
.customer-orders-search-field select:focus {
    outline: none;
    border-color: #27ae60;
    box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.1);
}

.customer-orders-search-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
    flex-wrap: wrap;
}

.customer-orders-search-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.customer-orders-search-btn-primary {
    background: #27ae60;
    color: white;
}

.customer-orders-search-btn-primary:hover {
    background: #219a52;
    transform: translateY(-1px);
}

.customer-orders-search-btn-secondary {
    background: #6c757d;
    color: white;
}

.customer-orders-search-btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.customer-orders-search-results-info {
    background: #e8f5e8;
    border: 1px solid #27ae60;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.customer-orders-search-results-text {
    color: #2d5016;
    font-weight: 600;
}

.customer-orders-search-clear-btn {
    background: #dc3545;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    transition: background 0.3s ease;
}

.customer-orders-search-clear-btn:hover {
    background: #c82333;
}

@media (max-width: 768px) {
    .customer-orders-search-row {
        grid-template-columns: 1fr;
    }

    .customer-orders-search-buttons {
        justify-content: center;
    }

    .customer-orders-details-order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .customer-orders-details-order-info {
        width: 100%;
    }

    .customer-orders-details-order-total {
        text-align: left;
        width: 100%;
    }

    .customer-orders-details-order-details-section {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="customer-orders-details-orders-container">
    <h2>My Bookings</h2>

    <!-- Search Form -->
    <div class="customer-orders-search-container">
        <form method="GET" class="customer-orders-search-form">
            <div class="customer-orders-search-row">
                <div class="customer-orders-search-field">
                    <label for="search">Search Bookings</label>
                    <input type="text" id="search" name="search" placeholder="Booking ID or Product Name"
                        value="<?= htmlspecialchars($search_query) ?>">
                </div>

                <div class="customer-orders-search-field">
                    <label for="date_from">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>

                <div class="customer-orders-search-field">
                    <label for="date_to">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>

                <div class="customer-orders-search-field">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method">
                        <option value="">All Payment Methods</option>
                        <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?= $method ?>" <?= $payment_method === $method ? 'selected' : '' ?>>
                            <?= ucfirst($method) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="customer-orders-search-field">
                    <label for="event_type">Event Type</label>
                    <select id="event_type" name="event_type">
                        <option value="">All Event Types</option>
                        <?php foreach ($eventTypes as $type): ?>
                        <option value="<?= $type ?>" <?= $event_type === $type ? 'selected' : '' ?>>
                            <?= ucfirst($type) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="customer-orders-search-buttons">
                <button type="submit" class="customer-orders-search-btn customer-orders-search-btn-primary">
                    🔍 Search Bookings
                </button>
                <a href="?" class="customer-orders-search-btn customer-orders-search-btn-secondary">
                    🔄 Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Search Results Info -->
    <?php if (!empty($search_query) || !empty($date_from) || !empty($date_to) || !empty($payment_method) || !empty($event_type)): ?>
    <div class="customer-orders-search-results-info">
        <div class="customer-orders-search-results-text">
            Found <?= count($bookings) ?> booking<?= count($bookings) != 1 ? 's' : '' ?>
            <?php
                    $filters = [];
                    if (!empty($search_query)) $filters[] = "matching '{$search_query}'";
                    if (!empty($date_from) && !empty($date_to)) $filters[] = "from {$date_from} to {$date_to}";
                    elseif (!empty($date_from)) $filters[] = "from {$date_from}";
                    elseif (!empty($date_to)) $filters[] = "until {$date_to}";
                    if (!empty($payment_method)) $filters[] = "paid with " . ucfirst($payment_method);
                    if (!empty($event_type)) $filters[] = "for " . ucfirst($event_type) . " events";
                    
                    if (!empty($filters)) {
                        echo implode(', ', $filters);
                    }
                    ?>
        </div>
        <a href="?" class="customer-orders-search-clear-btn">Clear All Filters</a>
    </div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
    <div class="customer-orders-details-no-orders">
        <h3><?= !empty($search_query) || !empty($date_from) || !empty($date_to) || !empty($payment_method) || !empty($event_type) ? 'No Bookings Found' : 'No Bookings Found' ?>
        </h3>
        <p><?= !empty($search_query) || !empty($date_from) || !empty($date_to) || !empty($payment_method) || !empty($event_type) ? 'No bookings match your search criteria. Try adjusting your filters.' : 'You haven\'t made any bookings yet.' ?>
        </p>
        <?php if (empty($search_query) && empty($date_from) && empty($date_to) && empty($payment_method) && empty($event_type)): ?>
        <a href="products.php" style="color: #27ae60; text-decoration: none; font-weight: bold;">Browse Products</a>
        <?php else: ?>
        <a href="?" style="color: #27ae60; text-decoration: none; font-weight: bold;">View All Bookings</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <?php foreach ($bookings as $booking): ?>
    <div class="customer-orders-details-order-card">
        <div class="customer-orders-details-order-header" onclick="toggleBooking(<?= $booking['id'] ?>)">
            <div class="customer-orders-details-order-info">
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Booking ID</div>
                    <div class="customer-orders-details-order-detail-value">#<?= $booking['id'] ?></div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Date</div>
                    <div class="customer-orders-details-order-detail-value">
                        <?= date('M d, Y', strtotime($booking['created_at'])) ?></div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Event</div>
                    <div class="customer-orders-details-order-detail-value">
                        <span class="event-type-badge event-<?= $booking['event_type'] ?>">
                            <?= ucfirst($booking['event_type']) ?>
                        </span>
                    </div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Payment</div>
                    <div class="customer-orders-details-order-detail-value">
                        <span class="customer-orders-details-payment-method payment-<?= $booking['mode_of_payment'] ?>">
                            <?= ucfirst($booking['mode_of_payment']) ?>
                        </span>
                    </div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Event Date</div>
                    <div class="customer-orders-details-order-detail-value">
                        <?= date('M d, Y', strtotime($booking['event_time'])) ?>
                    </div>
                </div>
            </div>
            <div class="customer-orders-details-order-total">
                <div class="amount">₱<?= number_format($booking['product_price'], 2) ?></div>
                <div class="customer-orders-details-expand-icon" id="icon-<?= $booking['id'] ?>">▼</div>
            </div>
        </div>

        <div class="customer-orders-details-order-content" id="content-<?= $booking['id'] ?>">
            <div class="customer-orders-details-order-items">
                <h4>Product Details</h4>
                <div class="customer-orders-details-item-row">
                    <img src="../uploads/<?= htmlspecialchars($booking['product_image']) ?>"
                        alt="<?= htmlspecialchars($booking['product_name']) ?>"
                        class="customer-orders-details-item-image">
                    <div class="customer-orders-details-item-info">
                        <div class="customer-orders-details-item-name">
                            <?= htmlspecialchars($booking['product_name']) ?></div>
                        <div class="customer-orders-details-item-details">
                            <?= htmlspecialchars($booking['product_description']) ?>
                        </div>
                    </div>
                    <div class="customer-orders-details-item-price">
                        ₱<?= number_format($booking['product_price'], 2) ?>
                    </div>
                </div>
            </div>

            <div class="customer-orders-details-order-details-section">
                <div class="customer-orders-details-detail-box">
                    <h4>Event Details</h4>
                    <p><strong>Event Type:</strong> <?= ucfirst($booking['event_type']) ?></p>
                    <p><strong>Event Date & Time:</strong>
                        <?= date('F d, Y g:i A', strtotime($booking['event_time'])) ?></p>
                    <p><strong>Venue:</strong> <?= htmlspecialchars($booking['event_venue']) ?></p>
                    <p><strong>Phone Number:</strong> <?= htmlspecialchars($booking['phone_number']) ?></p>
                    <?php if ($booking['notes']): ?>
                    <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($booking['notes'])) ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($booking['mode_of_payment'] === 'gcash'): ?>
                <div class="customer-orders-details-detail-box">
                    <h4>GCash Payment Details</h4>
                    <?php if ($booking['gcash_reference_number']): ?>
                    <p><strong>Reference Number:</strong>
                        <?= htmlspecialchars($booking['gcash_reference_number']) ?></p>
                    <?php endif; ?>
                    <?php if ($booking['gcash_number']): ?>
                    <p><strong>Your GCash Number:</strong> <?= htmlspecialchars($booking['gcash_number']) ?></p>
                    <?php endif; ?>
                    <?php if ($booking['gcash_account_name']): ?>
                    <p><strong>Your Account Name:</strong> <?= htmlspecialchars($booking['gcash_account_name']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($booking['proof_of_payment']): ?>
                    <p><strong>Payment Proof:</strong></p>
                    <img src="../uploads/payment_proofs/<?= htmlspecialchars($booking['proof_of_payment']) ?>"
                        alt="Payment Proof" class="customer-orders-details-payment-proof"
                        onclick="showPaymentProof('../uploads/payment_proofs/<?= htmlspecialchars($booking['proof_of_payment']) ?>')">
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="customer-orders-details-detail-box">
                    <h4>Booking Summary</h4>
                    <p><strong>Booking Date:</strong> <?= date('F d, Y g:i A', strtotime($booking['created_at'])) ?>
                    </p>
                    <p><strong>Product:</strong> <?= htmlspecialchars($booking['product_name']) ?></p>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($booking['product_price'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleBooking(bookingId) {
    const content = document.getElementById(`content-${bookingId}`);
    const icon = document.getElementById(`icon-${bookingId}`);

    if (content.classList.contains('expanded')) {
        content.classList.remove('expanded');
        icon.classList.remove('rotated');
    } else {
        // Close all other expanded bookings
        document.querySelectorAll('.customer-orders-details-order-content.expanded').forEach(el => {
            el.classList.remove('expanded');
        });
        document.querySelectorAll('.customer-orders-details-expand-icon.rotated').forEach(el => {
            el.classList.remove('rotated');
        });

        // Open this booking
        content.classList.add('expanded');
        icon.classList.add('rotated');
    }
}

function showPaymentProof(imageSrc) {
    Swal.fire({
        title: 'Payment Proof',
        imageUrl: imageSrc,
        imageWidth: 600,
        imageHeight: 600,
        imageAlt: 'Payment Proof',
        showCloseButton: true,
        showConfirmButton: false,
        width: 750,
        background: '#fff',
        backdrop: 'rgba(0,0,0,0.8)'
    });
}

// Auto-expand first booking if there's only one
document.addEventListener('DOMContentLoaded', function() {
    const bookingCards = document.querySelectorAll('.customer-orders-details-order-card');
    if (bookingCards.length === 1) {
        const firstBookingId = bookingCards[0].querySelector('.customer-orders-details-order-header')
            .getAttribute(
                'onclick').match(/\d+/)[0];
        toggleBooking(parseInt(firstBookingId));
    }
});

// Enhanced search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.customer-orders-search-form');
    const searchInput = document.getElementById('search');

    // Auto-submit on enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchForm.submit();
        }
    });
});
</script>
<!-- Footer -->
<?php include 'footer.php'; ?>