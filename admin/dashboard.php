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

    // NEW: Bookings Statistics
    $totalBookingsQuery = "SELECT COUNT(*) as total FROM bookings";
    $totalBookingsResult = $conn->query($totalBookingsQuery);
    $totalBookings = $totalBookingsResult->fetch_assoc()['total'];

    // Bookings Revenue
    $bookingsRevenueQuery = "SELECT SUM(p.price) as revenue FROM bookings b JOIN products p ON b.product_id = p.id";
    $bookingsRevenueResult = $conn->query($bookingsRevenueQuery);
    $bookingsRevenue = $bookingsRevenueResult->fetch_assoc()['revenue'] ?? 0;

    // Bookings by Event Type
    $bookingsEventQuery = "SELECT event_type, COUNT(*) as count FROM bookings GROUP BY event_type ORDER BY count DESC";
    $bookingsEventResult = $conn->query($bookingsEventQuery);
    $bookingsByEvent = [];
    while ($row = $bookingsEventResult->fetch_assoc()) {
        $bookingsByEvent[] = $row;
    }

    // Bookings by Payment Method
    $bookingsPaymentQuery = "SELECT mode_of_payment, COUNT(*) as count FROM bookings GROUP BY mode_of_payment";
    $bookingsPaymentResult = $conn->query($bookingsPaymentQuery);
    $bookingsByPayment = [];
    while ($row = $bookingsPaymentResult->fetch_assoc()) {
        $bookingsByPayment[] = $row;
    }

    // Recent Bookings
    $recentBookingsQuery = "SELECT b.id, b.created_at, b.event_type, b.mode_of_payment, p.name as product_name, p.price, u.full_name 
                           FROM bookings b 
                           JOIN products p ON b.product_id = p.id
                           JOIN users u ON b.user_id = u.id 
                           ORDER BY b.created_at DESC 
                           LIMIT 5";
    $recentBookingsResult = $conn->query($recentBookingsQuery);
    $recentBookings = [];
    while ($row = $recentBookingsResult->fetch_assoc()) {
        $recentBookings[] = $row;
    }

    // NEW: Ratings Statistics
    $totalReviewsQuery = "SELECT COUNT(*) as total FROM reviews";
    $totalReviewsResult = $conn->query($totalReviewsQuery);
    $totalReviews = $totalReviewsResult->fetch_assoc()['total'];

    // Average Rating
    $avgRatingQuery = "SELECT AVG(rating) as avg_rating FROM reviews";
    $avgRatingResult = $conn->query($avgRatingQuery);
    $avgRating = round($avgRatingResult->fetch_assoc()['avg_rating'] ?? 0, 1);

    // Rating Distribution
    $ratingDistQuery = "SELECT rating, COUNT(*) as count FROM reviews GROUP BY rating ORDER BY rating DESC";
    $ratingDistResult = $conn->query($ratingDistQuery);
    $ratingDistribution = [];
    $maxRatingCount = 0;
    while ($row = $ratingDistResult->fetch_assoc()) {
        $ratingDistribution[$row['rating']] = $row['count'];
        if ($row['count'] > $maxRatingCount) {
            $maxRatingCount = $row['count'];
        }
    }

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

<style>
/* NEW STYLES FOR RATINGS AND BOOKINGS */
.ratings-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.ratings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.average-rating {
    text-align: center;
}

.avg-rating-number {
    font-size: 3em;
    font-weight: bold;
    line-height: 1;
}

.avg-rating-stars {
    font-size: 1.5em;
    margin: 5px 0;
}

.avg-rating-total {
    font-size: 0.9em;
    opacity: 0.8;
}

.rating-breakdown {
    flex: 1;
    margin-left: 30px;
}

.rating-row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.rating-star {
    width: 30px;
    text-align: center;
}

.rating-bar-container {
    flex: 1;
    background: rgba(255, 255, 255, 0.2);
    height: 8px;
    border-radius: 4px;
    margin: 0 10px;
    overflow: hidden;
}

.rating-bar {
    height: 100%;
    background: #ffd700;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.rating-count {
    width: 40px;
    text-align: right;
    font-size: 0.9em;
}

.bookings-summary {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.bookings-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.booking-stat {
    text-align: center;
    padding: 15px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}

.booking-stat-number {
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 5px;
}

.booking-stat-label {
    font-size: 0.9em;
    opacity: 0.9;
}

.bookings-breakdown {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.breakdown-section h4 {
    margin-bottom: 15px;
    font-size: 1.1em;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.breakdown-item:last-child {
    border-bottom: none;
}

.breakdown-label {
    font-weight: 500;
}

.breakdown-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.9em;
}

/* Updated main stats grid to accommodate new bookings stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card.bookings {
    background: white;
    color: white;
}

@media (max-width: 768px) {
    .ratings-header {
        flex-direction: column;
        gap: 20px;
    }

    .rating-breakdown {
        margin-left: 0;
    }

    .bookings-breakdown {
        grid-template-columns: 1fr;
    }
}
</style>

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
        <div class="stat-card bookings">
            <div class="stat-value"><?= number_format($totalBookings); ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
    </div>

    <!-- NEW: Ratings Summary -->
    <?php if ($totalReviews > 0): ?>
    <div class="ratings-summary">
        <div class="ratings-header">
            <div class="average-rating">
                <div class="avg-rating-number"><?= $avgRating; ?></div>
                <div class="avg-rating-stars">
                    <?php 
                    $fullStars = floor($avgRating);
                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                    for ($i = 0; $i < $fullStars; $i++) echo '⭐';
                    if ($hasHalfStar) echo '⭐';
                    ?>
                </div>
                <div class="avg-rating-total"><?= number_format($totalReviews); ?> reviews</div>
            </div>
            <div class="rating-breakdown">
                <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                <div class="rating-row">
                    <div class="rating-star"><?= $rating; ?>⭐</div>
                    <div class="rating-bar-container">
                        <?php 
                        $count = $ratingDistribution[$rating] ?? 0;
                        $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        ?>
                        <div class="rating-bar" style="width: <?= $percentage; ?>%"></div>
                    </div>
                    <div class="rating-count"><?= $count; ?></div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

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

    <!-- NEW: Bookings Summary -->
    <?php if ($totalBookings > 0): ?>
    <div class="bookings-summary">
        <h3 style="margin-bottom: 20px;">📅 Bookings Overview</h3>

        <div class="bookings-stats">
            <div class="booking-stat">
                <div class="booking-stat-number"><?= number_format($totalBookings); ?></div>
                <div class="booking-stat-label">Total Bookings</div>
            </div>
            <div class="booking-stat">
                <div class="booking-stat-number">₱<?= number_format($bookingsRevenue, 2); ?></div>
                <div class="booking-stat-label">Bookings Revenue</div>
            </div>
            <div class="booking-stat">
                <div class="booking-stat-number">
                    ₱<?= $totalBookings > 0 ? number_format($bookingsRevenue / $totalBookings, 2) : '0.00'; ?></div>
                <div class="booking-stat-label">Average Booking</div>
            </div>
        </div>

        <div class="bookings-breakdown">
            <div class="breakdown-section">
                <h4>📋 By Event Type</h4>
                <?php foreach ($bookingsByEvent as $event): ?>
                <div class="breakdown-item">
                    <span class="breakdown-label"><?= ucfirst($event['event_type']); ?></span>
                    <span class="breakdown-count"><?= $event['count']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="breakdown-section">
                <h4>💳 By Payment Method</h4>
                <?php foreach ($bookingsByPayment as $payment): ?>
                <div class="breakdown-item">
                    <span class="breakdown-label"><?= ucfirst($payment['mode_of_payment']); ?></span>
                    <span class="breakdown-count"><?= $payment['count']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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

        <!-- NEW: Recent Bookings -->
        <?php if (!empty($recentBookings)): ?>
        <div class="dashboard-card">
            <h3>📅 Recent Bookings</h3>
            <div class="recent-list">
                <?php foreach ($recentBookings as $booking): ?>
                <div class="recent-item">
                    <div class="order-item">
                        <div class="order-info">
                            <h5>Booking #<?= $booking['id']; ?></h5>
                            <small>
                                <?= htmlspecialchars($booking['full_name']); ?> •
                                <?= date('M j, Y', strtotime($booking['created_at'])); ?>
                            </small>
                            <div>
                                <span class="payment-method <?= $booking['mode_of_payment']; ?>">
                                    <?= strtoupper($booking['mode_of_payment']); ?>
                                </span>
                                <span class="event-type-badge"
                                    style="background: #17a2b8; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 5px;">
                                    <?= strtoupper($booking['event_type']); ?>
                                </span>
                            </div>
                            <small style="color: #666;"><?= htmlspecialchars($booking['product_name']); ?></small>
                        </div>
                        <div class="order-amount">₱<?= number_format($booking['price'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

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
        <div class="dashboard-card" style="grid-column: 1 / -1;">
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

    // Animate rating bars
    $('.rating-bar').each(function() {
        var width = $(this).css('width');
        $(this).css('width', '0');
        $(this).animate({
            width: width
        }, 1000);
    });

    // Auto-refresh data every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>

<?php include 'footer.php'; ?>