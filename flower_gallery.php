<?php 
include 'includes/header.php';
include 'includes/db.php';
include 'models/cart_item.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

$cart = new CartItem($conn);

// Category filter
$categories = ["Valentine", "Birthday", "Wedding", "Funeral"];
$current_category = $_GET['category'] ?? "All";

// Count total records
$count_sql = "SELECT COUNT(*) as total FROM products WHERE quantity > 0";
if ($current_category !== "All") {
    $count_sql .= " AND occasion = '" . $conn->real_escape_string($current_category) . "'";
}
$count_result = $conn->query($count_sql);
$totalRecords = $count_result->fetch_assoc()['total'];

// Fetch products with LIMIT
$sql = "SELECT * FROM products WHERE quantity > 0";
if ($current_category !== "All") {
    $sql .= " AND occasion = '" . $conn->real_escape_string($current_category) . "'";
}
$result = $conn->query($sql);
?>

<div class="page-container">

    <!-- Category navigation -->
    <div class="category-nav">
        <a href="?category=All" class="<?= ($current_category == 'All' ? 'active' : '') ?>">All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="?category=<?= $cat ?>" class="<?= ($current_category == $cat ? 'active' : '') ?>"><?= $cat ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Products -->
    <div class="gallery-container">
        <?php if ($result->num_rows == 0): ?>
        <div class="no-products-message" style="text-align:center; color: #666; grid-column: 1 / -1; padding: 20px;">
            <p>No products found in this category. Please check back later or try another category.</p>
        </div>
        <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="gallery-item">
            <img src="../uploads/<?= $row['image'] ?>" alt="<?= $row['name'] ?>" class="product-image">
            <h3 class="product-name"><?= $row['name'] ?></h3>
            <p class="product-description"><?= $row['description'] ?></p>
            <p class="product-price">₱<?= number_format($row['price'], 2) ?></p>
            <p class="product-quantity">In stock: <?= $row['quantity'] ?></p>

            <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
                <?php if ($is_logged_in): ?>
                <button class="add-to-cart-btn" data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                    data-image="../uploads/<?= $row['image'] ?>" data-stock="<?= $row['quantity'] ?>"
                    data-price="<?= number_format($row['price'], 2) ?>"
                    style="padding: 8px 12px; border-radius: 4px; text-align: center; text-decoration: none; cursor: pointer; flex: 1; white-space: nowrap;">
                    Add to Cart
                </button>
                <button class="place-order-btn" data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>"
                    data-image="../uploads/<?= $row['image'] ?>" data-stock="<?= $row['quantity'] ?>"
                    data-price="<?= $row['price'] ?>"
                    style="padding: 8px 12px; border-radius: 4px; text-align: center; text-decoration: none; cursor: pointer; flex: 1; white-space: nowrap;">
                    Order
                </button>
                <?php else: ?>
                <a href="login.php" class="add-to-cart-btn">
                    Login to Add to Cart
                </a>
                <a href="login.php" class="place-order-btn">
                    Login to Order
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>