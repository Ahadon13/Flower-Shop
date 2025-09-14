<?php 
include 'header.php';
include '../includes/db.php';
include '../models/cart_item.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

$cart = new CartItem($conn);

// Handle Add to Cart POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);
    $user_id    = $_SESSION['user_id'];

    // Check stock
    $stock_query = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
    $stock_query->bind_param("i", $product_id);
    $stock_query->execute();
    $stock_result = $stock_query->get_result()->fetch_assoc();

    if (!$stock_result || $quantity < 1 || $quantity > $stock_result['quantity']) {
        $_SESSION['messages'] = ["error" => "Invalid quantity selected."];
    } else {
        $cart->addToCart($user_id, $product_id, $quantity);
         // --- Update session cart ---
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        $_SESSION['messages'] = ["success" => "Added $quantity item(s) to cart."];

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

}

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
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add to Cart Modal -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-body">
            <img id="modalImage" src="" alt="" class="modal-image">
            <h3 id="modalName"></h3>
            <p id="modalPrice"></p>
            <p id="modalStock"></p>
            <form method="POST">
                <input type="hidden" name="product_id" id="modalProductId">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="modalQuantity" min="0" required style="padding: 8px 12px;">
                <button type="submit" name="add_to_cart" class="confirm-btn">Confirm</button>
            </form>
        </div>
    </div>
</div>

<!-- Order Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close-order">&times;</span>
        <div class="modal-body">
            <img id="orderModalImage" src="" alt="" class="modal-image">
            <h3 id="orderModalName"></h3>
            <p id="orderModalPrice"></p>
            <p id="orderModalStock"></p>
            <form id="orderForm" method="GET" action="checkout.php">
                <input type="hidden" name="products" id="orderModalProductId">
                <label for="orderQuantity">Quantity:</label>
                <input type="number" name="quantities" id="orderModalQuantity" min="0" required
                    style="padding: 8px 12px;">
                <div style="margin-top: 15px; padding: 10px; background-color: #f5f5f5; border-radius: 4px;">
                    <strong>Total Price: ₱<span id="totalPrice">0.00</span></strong>
                </div>
                <button type="submit" class="confirm-btn" style="margin-top: 15px;">Checkout</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const cartModal = document.getElementById("cartModal");
    const orderModal = document.getElementById("orderModal");
    const closeCartBtn = document.querySelector(".close");
    const closeOrderBtn = document.querySelector(".close-order");

    // Cart Modal Elements
    const modalImage = document.getElementById("modalImage");
    const modalName = document.getElementById("modalName");
    const modalPrice = document.getElementById("modalPrice");
    const modalStock = document.getElementById("modalStock");
    const modalProductId = document.getElementById("modalProductId");
    const modalQuantity = document.getElementById("modalQuantity");

    // Order Modal Elements
    const orderModalImage = document.getElementById("orderModalImage");
    const orderModalName = document.getElementById("orderModalName");
    const orderModalPrice = document.getElementById("orderModalPrice");
    const orderModalStock = document.getElementById("orderModalStock");
    const orderModalProductId = document.getElementById("orderModalProductId");
    const orderModalQuantity = document.getElementById("orderModalQuantity");
    const totalPriceElement = document.getElementById("totalPrice");

    let currentOrderPrice = 0;

    // Add to Cart functionality
    document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const name = btn.getAttribute("data-name");
            const img = btn.getAttribute("data-image");
            const stock = parseInt(btn.getAttribute("data-stock"));
            const price = btn.getAttribute("data-price");

            modalImage.src = img;
            modalName.textContent = name;
            modalPrice.textContent = "₱" + price;
            modalStock.textContent = "Available stock: " + stock;
            modalProductId.value = id;

            modalQuantity.value = 0;
            modalQuantity.max = stock;

            cartModal.style.display = "block";
        });
    });

    // Place Order functionality
    document.querySelectorAll(".place-order-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const name = btn.getAttribute("data-name");
            const img = btn.getAttribute("data-image");
            const stock = parseInt(btn.getAttribute("data-stock"));
            const price = parseFloat(btn.getAttribute("data-price"));

            currentOrderPrice = price;

            orderModalImage.src = img;
            orderModalName.textContent = name;
            orderModalPrice.textContent = "₱" + price.toFixed(2);
            orderModalStock.textContent = "Available stock: " + stock;
            orderModalProductId.value = id;

            orderModalQuantity.value = 0;
            orderModalQuantity.max = stock;

            // Calculate initial total
            updateTotalPrice();

            orderModal.style.display = "block";
        });
    });

    // Close modal functionality
    closeCartBtn.onclick = () => cartModal.style.display = "none";
    closeOrderBtn.onclick = () => orderModal.style.display = "none";

    window.onclick = (e) => {
        if (e.target == cartModal) cartModal.style.display = "none";
        if (e.target == orderModal) orderModal.style.display = "none";
    };

    // Cart quantity validation
    modalQuantity.addEventListener("input", () => {
        const max = parseInt(modalQuantity.max);
        if (modalQuantity.value > max) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'error',
                title: 'Quantity exceeds available stock!'
            });
            modalQuantity.value = max;
        } else if (modalQuantity.value < 1) {
            modalQuantity.value = 1;
        }
    });

    // Order quantity validation and total price calculation
    function updateTotalPrice() {
        const quantity = parseInt(orderModalQuantity.value) || 1;
        const total = currentOrderPrice * quantity;
        totalPriceElement.textContent = total.toFixed(2);
    }

    orderModalQuantity.addEventListener("input", () => {
        const max = parseInt(orderModalQuantity.max);
        if (orderModalQuantity.value > max) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'error',
                title: 'Quantity exceeds available stock!'
            });
            orderModalQuantity.value = max;
        } else if (orderModalQuantity.value < 1) {
            orderModalQuantity.value = 1;
        }

        updateTotalPrice();
    });
});
</script>

<?php include '../customer/footer.php'; ?>