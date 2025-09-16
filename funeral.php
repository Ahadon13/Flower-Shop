<?php
include 'includes/header.php';
include 'includes/db.php';

$occasion = "funeral";
// Fetch products
$stmt = $conn->prepare("SELECT * FROM products WHERE occasion = ?");
$stmt->bind_param("s", $occasion);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Funeral Flowers</title>
    <link rel="stylesheet" href="flower_gallery.css">
    <style>
    .flower-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin: 30px auto;
        max-width: 1000px;
    }

    .flower-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: transform 0.2s ease;
    }

    .flower-card:hover {
        transform: translateY(-5px);
    }

    .flower-card img {
        width: auto;
        height: 250px;
        object-fit: cover;
        object-position: center;
    }

    .flower-info {
        padding: 15px;
    }

    .flower-info h2 {
        margin: 0 0 8px;
        font-size: 18px;
    }

    .flower-info p {
        font-size: 14px;
        color: #666;
    }

    .book-btn {
        display: inline-block;
        margin-top: 10px;
        padding: 10px 14px;
        background: #3498db;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.3s;
    }

    .book-btn:hover {
        background: #2980b9;
    }

    .back-btn {
        display: inline-block;
        padding: 10px 16px;
        background: #95a5a6;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.3s;
    }

    .back-btn:hover {
        background: #7f8c8d;
    }
    </style>
</head>

<body>
    <!-- Back Button -->
    <div style="text-align:left; margin:20px;">
        <a href="flower_gallery.php" class="back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"
                style="margin-right: 5px; vertical-align: text-bottom;">
                <path fill-rule="evenodd"
                    d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
            </svg>
            Back to Categories
        </a>
    </div>
    <h1 style="text-align:center; margin-top:40px;">Funeral Flowers</h1>

    <div class="flower-grid">
        <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
        <div class="flower-card">
            <img src="uploads/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="flower-info">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p><strong>₱<?= number_format($product['price'], 2) ?></strong></p>

                <!-- Book Now button -->
                <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']): ?>
                <a href="customer/booking.php?product_id=<?= $product['id'] ?>" class="book-btn">Book Now</a>
                <?php else: ?>
                <a href="login.php" class="book-btn">Book Now</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div style="text-align:center; color:#666; grid-column: 1 / -1; padding: 20px;">No products found for this
            occasion.</div>
        <?php endif; ?>
    </div>
</body>

</html>

<?php include 'includes/footer.php'; ?>