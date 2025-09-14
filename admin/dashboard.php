<?php
include '../includes/db.php';
include 'header.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Get analytics data
try {
    // Total Orders
    $totalOrdersQuery = "SELECT COUNT(*) as total FROM orders";
    $totalOrdersResult = $conn->query($totalOrdersQuery);
    $totalOrders = $totalOrdersResult->fetch_assoc()['total'];

    // Total Revenue
    $totalRevenueQuery = "SELECT SUM(total) as revenue FROM orders";
    $totalRevenueResult = $conn->query($totalRevenueQuery);
    $totalRevenue = $totalRevenueResult->fetch_assoc()['revenue'] ?? 0;

    // Total Users
    $totalUsersQuery = "SELECT COUNT(*) as total FROM users";
    $totalUsersResult = $conn->query($totalUsersQuery);
    $totalUsers = $totalUsersResult->fetch_assoc()['total'];

    // Total Products
    $totalProductsQuery = "SELECT COUNT(*) as total FROM products";
    $totalProductsResult = $conn->query($totalProductsQuery);
    $totalProducts = $totalProductsResult->fetch_assoc()['total'];

    // Products by Occasion
    $occasionQuery = "SELECT occasion, COUNT(*) as count, SUM(quantity) as total_quantity FROM products GROUP BY occasion";
    $occasionResult = $conn->query($occasionQuery);
    $occasions = [];
    while ($row = $occasionResult->fetch_assoc()) {
        $occasions[] = $row;
    }

    // Recent Orders (last 5)
    $recentOrdersQuery = "SELECT o.id, o.order_date, o.total, o.payment_method, u.full_name 
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         ORDER BY o.order_date DESC 
                         LIMIT 5";
    $recentOrdersResult = $conn->query($recentOrdersQuery);
    $recentOrders = [];
    while ($row = $recentOrdersResult->fetch_assoc()) {
        $recentOrders[] = $row;
    }

    // Recent Reviews (last 5)
    $recentReviewsQuery = "SELECT name, review, rating, created_at FROM reviews ORDER BY created_at DESC LIMIT 5";
    $recentReviewsResult = $conn->query($recentReviewsQuery);
    $recentReviews = [];
    while ($row = $recentReviewsResult->fetch_assoc()) {
        $recentReviews[] = $row;
    }

    // Recent Contact Messages (last 5)
    $recentMessagesQuery = "SELECT name, email, message, timestamp FROM contact_messages ORDER BY timestamp DESC LIMIT 5";
    $recentMessagesResult = $conn->query($recentMessagesQuery);
    $recentMessages = [];
    while ($row = $recentMessagesResult->fetch_assoc()) {
        $recentMessages[] = $row;
    }

    // Low Stock Products (quantity <= 5)
    $lowStockQuery = "SELECT name, quantity FROM products WHERE quantity <= 5 ORDER BY quantity ASC";
    $lowStockResult = $conn->query($lowStockQuery);
    $lowStockProducts = [];
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStockProducts[] = $row;
    }

    // Monthly Revenue (last 6 months)
    $monthlyRevenueQuery = "SELECT 
                               DATE_FORMAT(order_date, '%Y-%m') as month,
                               SUM(total) as revenue,
                               COUNT(*) as orders
                           FROM orders 
                           WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                           GROUP BY DATE_FORMAT(order_date, '%Y-%m')
                           ORDER BY month DESC";
    $monthlyRevenueResult = $conn->query($monthlyRevenueQuery);
    $monthlyRevenue = [];
    while ($row = $monthlyRevenueResult->fetch_assoc()) {
        $monthlyRevenue[] = $row;
    }

} catch (Exception $e) {
    $error = "Error fetching analytics data: " . $e->getMessage();
}
?>


<div class="analytics-container">
    <div class="analytics-header">
        <h1>📊 Analytics Dashboard</h1>
        <p>Get insights into your flower shop's performance</p>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error; ?></div>
    <?php else: ?>

    <!-- Main Statistics -->
    <div class="stats-grid">
        <div class="stat-card orders">
            <div class="stat-value"><?= number_format($totalOrders); ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-value">₱<?= number_format($totalRevenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card users">
            <div class="stat-value"><?= number_format($totalUsers); ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
        <div class="stat-card products">
            <div class="stat-value"><?= number_format($totalProducts); ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>

    <!-- Low Stock Warning -->
    <?php if (!empty($lowStockProducts)): ?>
    <div class="low-stock-warning">
        <h4>⚠️ Low Stock Alert</h4>
        <?php foreach ($lowStockProducts as $product): ?>
        <div class="low-stock-item">
            <span class="low-stock-name"><?= htmlspecialchars($product['name']); ?></span>
            <span class="low-stock-qty"><?= $product['quantity']; ?> left</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Products by Occasion -->
        <div class="dashboard-card">
            <h3>🌸 Products by Occasion</h3>
            <div class="occasion-grid">
                <?php foreach ($occasions as $occasion): ?>
                <div class="occasion-item <?= $occasion['occasion']; ?>">
                    <div class="occasion-count"><?= $occasion['count']; ?></div>
                    <div class="occasion-label"><?= ucfirst($occasion['occasion']); ?></div>
                    <div class="occasion-stock"><?= $occasion['total_quantity']; ?> in stock</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="dashboard-card">
            <h3>🛍️ Recent Orders</h3>
            <div class="recent-list">
                <?php if (!empty($recentOrders)): ?>
                <?php foreach ($recentOrders as $order): ?>
                <div class="recent-item">
                    <div class="order-item">
                        <div class="order-info">
                            <h5>Order #<?= $order['id']; ?></h5>
                            <small>
                                <?= $order['full_name'] ?? 'Guest'; ?> •
                                <?= date('M j, Y', strtotime($order['order_date'])); ?>
                            </small>
                            <div>
                                <span class="payment-method <?= $order['payment_method']; ?>">
                                    <?= strtoupper($order['payment_method']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="order-amount">₱<?= number_format($order['total'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="dashboard-card">
            <h3>⭐ Recent Reviews</h3>
            <div class="recent-list">
                <?php if (!empty($recentReviews)): ?>
                <?php foreach ($recentReviews as $review): ?>
                <div class="recent-item">
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-name"><?= htmlspecialchars($review['name']); ?></span>
                            <span class="review-rating">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>⭐<?php endfor; ?>
                            </span>
                        </div>
                        <p class="review-text"><?= htmlspecialchars($review['review']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Contact Messages -->
        <div class="dashboard-card">
            <h3>📧 Recent Messages</h3>
            <div class="recent-list">
                <?php if (!empty($recentMessages)): ?>
                <?php foreach ($recentMessages as $message): ?>
                <div class="recent-item">
                    <div class="message-item">
                        <div class="message-header">
                            <span class="message-from"><?= htmlspecialchars($message['name']); ?></span>

                            <small><?= date('M j, Y', strtotime($message['timestamp'])); ?></small>
                        </div>
                        <div class="message-email"><?= htmlspecialchars($message['email']); ?></div>
                        <p class="message-text"><?= htmlspecialchars($message['message']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No messages found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <?php if (!empty($monthlyRevenue)): ?>
    <div class="dashboard-card monthly-revenue">
        <h3>📈 Monthly Performance (Last 6 Months)</h3>
        <div class="revenue-list">
            <?php foreach ($monthlyRevenue as $month): ?>
            <div class="revenue-month">
                <div class="revenue-month-name"><?= date('F Y', strtotime($month['month'] . '-01')); ?></div>
                <div class="revenue-amount">₱<?= number_format($month['revenue'], 2); ?></div>
                <div class="revenue-orders"><?= $month['orders']; ?> orders</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Add some interactive effects
    $('.stat-card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );

    // Auto-refresh data every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>

<?php include 'footer.php'; ?>