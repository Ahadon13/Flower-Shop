<?php
include '../includes/db.php';
include 'header.php';
include '../models/Order.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

// Initialize Order model
$orderModel = new Order($conn);

// Get user orders
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get search parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : '';
$fulfillment_method = isset($_GET['fulfillment_method']) ? $_GET['fulfillment_method'] : '';

// Build the query with search conditions
$whereConditions = [];
$params = [];
$paramTypes = '';

// Base condition for user
if ($user_id) {
    $whereConditions[] = "o.user_id = ?";
    $params[] = $user_id;
    $paramTypes .= 'i';
}

// Search query (order ID or product name)
if (!empty($search_query)) {
    $whereConditions[] = "(o.id LIKE ? OR EXISTS (
        SELECT 1 FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = o.id AND p.name LIKE ?
    ))";
    $searchParam = '%' . $search_query . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $paramTypes .= 'ss';
}

// Date range filter
if (!empty($date_from)) {
    $whereConditions[] = "DATE(o.order_date) >= ?";
    $params[] = $date_from;
    $paramTypes .= 's';
}

if (!empty($date_to)) {
    $whereConditions[] = "DATE(o.order_date) <= ?";
    $params[] = $date_to;
    $paramTypes .= 's';
}

// Payment method filter
if (!empty($payment_method)) {
    $whereConditions[] = "o.payment_method = ?";
    $params[] = $payment_method;
    $paramTypes .= 's';
}

// Fulfillment method filter
if (!empty($fulfillment_method)) {
    $whereConditions[] = "o.fulfillment_method = ?";
    $params[] = $fulfillment_method;
    $paramTypes .= 's';
}

// Build the final query
$ordersQuery = "SELECT o.* FROM orders o";
if (!empty($whereConditions)) {
    $ordersQuery .= " WHERE " . implode(' AND ', $whereConditions);
}
$ordersQuery .= " ORDER BY o.order_date DESC";

// If no user session and no search, limit results
if (!$user_id && empty($search_query) && empty($date_from) && empty($date_to) && empty($payment_method) && empty($fulfillment_method)) {
    $ordersQuery .= " LIMIT 50";
}

$stmt = $conn->prepare($ordersQuery);
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$ordersResult = $stmt->get_result();
$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);

// Function to get order items for a specific order
function getOrderItems($conn, $order_id) {
    $query = "
        SELECT oi.*, p.name, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ? 
        ORDER BY p.name
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get order summary (grouped by product)
function getOrderSummary($conn, $order_id) {
    $query = "
        SELECT 
            p.name, 
            p.image,
            oi.price,
            COUNT(*) as quantity,
            SUM(oi.price) as subtotal
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ? 
        GROUP BY oi.product_id, p.name, p.image, oi.price
        ORDER BY p.name DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get available payment and fulfillment methods for filter dropdowns
$paymentMethods = ['cash', 'gcash'];
$fulfillmentMethods = ['pickup', 'delivery'];
?>

<div class="customer-orders-details-orders-container">
    <h2>My Orders</h2>

    <!-- Search Form -->
    <div class="customer-orders-search-container">
        <form method="GET" class="customer-orders-search-form">
            <div class="customer-orders-search-row">
                <div class="customer-orders-search-field">
                    <label for="search">Search Orders</label>
                    <input type="text" id="search" name="search" placeholder="Order ID or Product Name"
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
                    <label for="fulfillment_method">Fulfillment</label>
                    <select id="fulfillment_method" name="fulfillment_method">
                        <option value="">All Fulfillment Methods</option>
                        <?php foreach ($fulfillmentMethods as $method): ?>
                        <option value="<?= $method ?>" <?= $fulfillment_method === $method ? 'selected' : '' ?>>
                            <?= ucfirst($method) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="customer-orders-search-buttons">
                <button type="submit" class="customer-orders-search-btn customer-orders-search-btn-primary">
                    🔍 Search Orders
                </button>
                <a href="?" class="customer-orders-search-btn customer-orders-search-btn-secondary">
                    🔄 Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Search Results Info -->
    <?php if (!empty($search_query) || !empty($date_from) || !empty($date_to) || !empty($payment_method) || !empty($fulfillment_method)): ?>
    <div class="customer-orders-search-results-info">
        <div class="customer-orders-search-results-text">
            Found <?= count($orders) ?> order<?= count($orders) != 1 ? 's' : '' ?>
            <?php
                $filters = [];
                if (!empty($search_query)) $filters[] = "matching '{$search_query}'";
                if (!empty($date_from) && !empty($date_to)) $filters[] = "from {$date_from} to {$date_to}";
                elseif (!empty($date_from)) $filters[] = "from {$date_from}";
                elseif (!empty($date_to)) $filters[] = "until {$date_to}";
                if (!empty($payment_method)) $filters[] = "paid with " . ucfirst($payment_method);
                if (!empty($fulfillment_method)) $filters[] = "for " . ucfirst($fulfillment_method);
                
                if (!empty($filters)) {
                    echo implode(', ', $filters);
                }
                ?>
        </div>
        <a href="?" class="customer-orders-search-clear-btn">Clear All Filters</a>
    </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
    <div class="customer-orders-details-no-orders">
        <h3><?= !empty($search_query) || !empty($date_from) || !empty($date_to) || !empty($payment_method) || !empty($fulfillment_method) ? 'No Orders Found' : 'No Orders Found' ?>
        </h3>
        <p><?= !empty($search_query) || !empty($date_from) || !empty($date_to) || !empty($payment_method) || !empty($fulfillment_method) ? 'No orders match your search criteria. Try adjusting your filters.' : 'You havent placed any orders yet.' ?>
        </p>
        <?php if (empty($search_query) && empty($date_from) && empty($date_to) && empty($payment_method) && empty($fulfillment_method)): ?>
        <a href="index.php" style="color: #27ae60; text-decoration: none; font-weight: bold;">Start Shopping</a>
        <?php else: ?>
        <a href="?" style="color: #27ae60; text-decoration: none; font-weight: bold;">View All Orders</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <?php foreach ($orders as $order): ?>
    <?php 
                $orderItems = getOrderSummary($conn, $order['id']);
                $itemCount = array_sum(array_column($orderItems, 'quantity'));
            ?>
    <div class="customer-orders-details-order-card">
        <div class="customer-orders-details-order-header" onclick="toggleOrder(<?= $order['id'] ?>)">
            <div class="customer-orders-details-order-info">
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Order ID</div>
                    <div class="customer-orders-details-order-detail-value">#<?= $order['id'] ?></div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Date</div>
                    <div class="customer-orders-details-order-detail-value">
                        <?= date('M d, Y', strtotime($order['order_date'])) ?></div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Items</div>
                    <div class="customer-orders-details-order-detail-value"><?= $itemCount ?>
                        item<?= $itemCount != 1 ? 's' : '' ?></div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Payment</div>
                    <div class="customer-orders-details-order-detail-value">
                        <span class="customer-orders-details-payment-method payment-<?= $order['payment_method'] ?>">
                            <?= ucfirst($order['payment_method']) ?>
                        </span>
                    </div>
                </div>
                <div class="customer-orders-details-order-detail">
                    <div class="customer-orders-details-order-detail-label">Fulfillment</div>
                    <div class="customer-orders-details-order-detail-value">
                        <span
                            class="customer-orders-details-fulfillment-method fulfillment-<?= $order['fulfillment_method'] ?>">
                            <?= ucfirst($order['fulfillment_method']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="customer-orders-details-order-total">
                <div class="amount">₱<?= number_format($order['total'], 2) ?></div>
                <div class="customer-orders-details-expand-icon" id="icon-<?= $order['id'] ?>">▼</div>
            </div>
        </div>

        <div class="customer-orders-details-order-content" id="content-<?= $order['id'] ?>">
            <div class="customer-orders-details-order-items">
                <h4>Order Items</h4>
                <?php foreach ($orderItems as $item): ?>
                <div class="customer-orders-details-item-row">
                    <img src="../uploads/<?= htmlspecialchars($item['image']) ?>"
                        alt="<?= htmlspecialchars($item['name']) ?>" class="customer-orders-details-item-image">
                    <div class="customer-orders-details-item-info">
                        <div class="customer-orders-details-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="customer-orders-details-item-details">
                            Quantity: <?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?>
                        </div>
                    </div>
                    <div class="customer-orders-details-item-price">
                        ₱<?= number_format($item['subtotal'], 2) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="customer-orders-details-order-details-section">
                <?php if ($order['payment_method'] === 'gcash'): ?>
                <div class="customer-orders-details-detail-box">
                    <h4>GCash Payment Details</h4>
                    <?php if ($order['gcash_reference_number']): ?>
                    <p><strong>Reference Number:</strong> <?= htmlspecialchars($order['gcash_reference_number']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($order['gcash_number']): ?>
                    <p><strong>GCash Number:</strong> <?= htmlspecialchars($order['gcash_number']) ?></p>
                    <?php endif; ?>
                    <?php if ($order['gcash_account_name']): ?>
                    <p><strong>Account Name:</strong> <?= htmlspecialchars($order['gcash_account_name']) ?></p>
                    <?php endif; ?>
                    <?php if ($order['payment_proof_image']): ?>
                    <p><strong>Payment Proof:</strong></p>
                    <img src="<?= htmlspecialchars($order['payment_proof_image']) ?>" alt="Payment Proof"
                        class="customer-orders-details-payment-proof"
                        onclick="showPaymentProof('<?= htmlspecialchars($order['payment_proof_image']) ?>')">
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($order['fulfillment_method'] === 'delivery'): ?>
                <div class="customer-orders-details-detail-box">
                    <h4>Delivery Information</h4>
                    <?php if ($order['delivery_address']): ?>
                    <p><strong>Address:</strong><br><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                    <?php endif; ?>
                    <?php if ($order['contact_number']): ?>
                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($order['contact_number']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="customer-orders-details-detail-box">
                    <h4>Order Summary</h4>
                    <p><strong>Order Date:</strong> <?= date('F d, Y g:i A', strtotime($order['order_date'])) ?></p>
                    <p><strong>Total Items:</strong> <?= $itemCount ?></p>
                    <p><strong>Total Amount:</strong> ₱<?= number_format($order['total'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleOrder(orderId) {
    const content = document.getElementById(`content-${orderId}`);
    const icon = document.getElementById(`icon-${orderId}`);

    if (content.classList.contains('expanded')) {
        content.classList.remove('expanded');
        icon.classList.remove('rotated');
    } else {
        // Close all other expanded orders
        document.querySelectorAll('.customer-orders-details-order-content.expanded').forEach(el => {
            el.classList.remove('expanded');
        });
        document.querySelectorAll('.customer-orders-details-expand-icon.rotated').forEach(el => {
            el.classList.remove('rotated');
        });

        // Open this order
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

// Auto-expand first order if there's only one
document.addEventListener('DOMContentLoaded', function() {
    const orderCards = document.querySelectorAll('.customer-orders-details-order-card');
    if (orderCards.length === 1) {
        const firstOrderId = orderCards[0].querySelector('.customer-orders-details-order-header').getAttribute(
            'onclick').match(
            /\d+/)[0];
        toggleOrder(parseInt(firstOrderId));
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

    // Clear individual fields
    const clearField = (fieldId) => {
        document.getElementById(fieldId).value = '';
    };

    // Add clear buttons for individual fields (optional enhancement)
    const addClearButton = (fieldId) => {
        const field = document.getElementById(fieldId);
        if (field && field.value) {
            const clearBtn = document.createElement('button');
            clearBtn.innerHTML = '×';
            clearBtn.type = 'button';
            clearBtn.style.cssText =
                'position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:none;cursor:pointer;';
            clearBtn.onclick = () => {
                field.value = '';
                field.focus();
            };

            const fieldContainer = field.parentElement;
            fieldContainer.style.position = 'relative';
            fieldContainer.appendChild(clearBtn);
        }
    };

    // Add clear buttons to text inputs
    ['search'].forEach(addClearButton);
});
</script>

<?php include 'footer.php'; ?>