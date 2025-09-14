<?php

// Regular page load - include files after AJAX handling
include '../includes/db.php';
include '../models/cart_item.php';
include 'header.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cartItem = new CartItem($conn);

// Handle AJAX requests BEFORE any includes that might output HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    // Prevent any output before JSON
    ob_clean();
    
    try {
        if (isset($_POST['action'])) {
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            
            switch ($_POST['action']) {
                case 'update_quantity':
                    $new_quantity = (int)$_POST['quantity'] ?? 0;
                    
                    // Get product details to check available stock
                    $stmt = $conn->prepare("SELECT quantity, name FROM products WHERE id = ?");
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    
                    if ($new_quantity > $product['quantity']) {
                        echo json_encode([
                            'success' => false,
                            'message' => "Cannot add more items. Only {$product['quantity']} {$product['name']} available in stock.",
                            'max_quantity' => $product['quantity']
                        ]);
                        exit;
                    }
                    
                    if ($new_quantity <= 0) {
                        $cartItem->removeFromCart($user_id, $product_id);
                        $cartItem->syncSessionCartItem($product_id, 'remove');
                        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
                    } else {
                        $cartItem->updateQuantity($user_id, $product_id, $new_quantity);
                        $cartItem->syncSessionCartItem($product_id, 'update', $new_quantity);
                        echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
                    }
                    exit;
                    
                case 'delete_item':
                    $cartItem->removeFromCart($user_id, $product_id);
                    $cartItem->syncSessionCartItem($product_id, 'remove');
                    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
                    exit;
                    
                case 'clear_cart':
                    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $cartItem->syncSessionCartItem(null, 'clear');
                    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
                    exit;
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
    
    // If we get here, something went wrong
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Handle checkout (non-AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout']) && isset($_SESSION['user_id'])) {
    $customer_id = $_SESSION['user_id'];
    // Get cart items from database
    $cart_items = $cartItem->getCartItems($customer_id);
   
    if ($cart_items->num_rows > 0) {
        $product_ids = [];
        $quantities = [];
        $cart_item_ids = [];
        
        // Collect all cart data
        while ($item = $cart_items->fetch_assoc()) {
            $product_ids[] = $item['product_id'];
            $quantities[] = $item['quantity'];
            $cart_item_ids[] = $item['id']; // Assuming cart_items table has 'id' column
        }
        
        // Convert arrays to comma-separated strings
        $products_param = implode(',', $product_ids);
        $quantities_param = implode(',', $quantities);
        $cart_ids_param = implode(',', $cart_item_ids);
        
        // Redirect to checkout with all cart data
        header("Location: checkout.php?products=$products_param&quantities=$quantities_param&cart_ids=$cart_ids_param");
        exit();
    }
}

// Get cart items from database
$cart_items = $cartItem->getCartItems($user_id);
?>

<div class="cart-container-wrapper">
    <div class="cart-container">
        <h2>Your Cart</h2>

        <div id="cart-items">
            <?php if ($cart_items->num_rows > 0): ?>
            <?php 
                $total = 0;
                while ($item = $cart_items->fetch_assoc()): 
                    $line_total = $item['price'] * $item['quantity'];
                    $total += $line_total;
                    
                    // Get available stock with prepared statement
                    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
                    $stmt->bind_param("i", $item['product_id']);
                    $stmt->execute();
                    $stock_result = $stmt->get_result();
                    $stock = $stock_result->fetch_assoc()['quantity'];
                ?>
            <div class="cart-item" data-product-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>"
                data-max-stock="<?= $stock ?>">
                <div class="item-info">
                    <?php $image_path = !empty($item['image']) ? "../uploads/{$item['image']}" : "https://via.placeholder.com/80x80?text=No+Image"; ?>
                    <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                    <div class="item-details">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <div class="quantity-controls">
                            <button class="quantity-btn minus-btn"
                                data-product-id="<?= $item['product_id'] ?>">−</button>
                            <span class="quantity-display"><?= $item['quantity'] ?></span>
                            <button class="quantity-btn plus-btn"
                                data-product-id="<?= $item['product_id'] ?>">+</button>
                        </div>
                        <h4>Stock: <?= htmlspecialchars($stock) ?></h4>
                    </div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div class="item-price">₱<span class="line-total"><?= number_format($line_total, 2) ?></span></div>
                    <button class="delete-btn" data-product-id="<?= $item['product_id'] ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endwhile; ?>

            <div class="cart-actions">
                <div class="total-price">
                    Total: ₱<span id="grand-total"><?= number_format($total, 2) ?></span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-danger" id="clear-cart-btn">Clear Cart</button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="checkout" class="btn btn-primary">Proceed to Checkout</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p>Looks like you havent added any items to your cart yet.</p>
                <a href="dashboard.php" class="btn btn-primary">Continue Shopping</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Event delegation for dynamic content
    $(document).on('click', '.minus-btn', function() {
        const productId = $(this).data('product-id');
        updateQuantity(productId, 'minus');
    });

    $(document).on('click', '.plus-btn', function() {
        const productId = $(this).data('product-id');
        updateQuantity(productId, 'plus');
    });

    $(document).on('click', '.delete-btn', function() {
        const productId = $(this).data('product-id');
        deleteItem(productId);
    });

    $(document).on('click', '#clear-cart-btn', function() {
        clearCart();
    });

    function updateQuantity(productId, action) {
        const cartItem = $(`.cart-item[data-product-id="${productId}"]`);
        const currentQty = parseInt(cartItem.find('.quantity-display').text()) || 0;
        const price = parseFloat(cartItem.attr('data-price')) || 0; // Use attr() instead of data()
        const maxStock = parseInt(cartItem.attr('data-max-stock')) || 0;

        let newQty = currentQty;
        if (action === 'plus') {
            newQty = currentQty + 1;
            if (newQty > maxStock) {
                Toast.fire({
                    icon: 'warning',
                    title: `Cannot add more items. Only ${maxStock} available in stock.`
                });
                return;
            }
        } else if (action === 'minus') {
            newQty = currentQty - 1;
        }

        if (newQty < 0) return;

        // Disable buttons during request
        cartItem.find('.quantity-btn').prop('disabled', true);

        $.ajax({
            url: window.location.pathname,
            type: 'POST',
            data: {
                ajax: true,
                action: 'update_quantity',
                product_id: productId,
                quantity: newQty
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (newQty === 0) {
                        // Remove the item from display with animation
                        cartItem.fadeOut(300, function() {
                            $(this).remove();
                            recalculateTotal();
                            checkEmptyCart();
                        });
                    } else {
                        // Update quantity display
                        cartItem.find('.quantity-display').text(newQty);

                        // Calculate and update line total
                        const lineTotal = price * newQty;
                        cartItem.find('.line-total').text(number_format(lineTotal, 2));

                        // Update grand total
                        recalculateTotal();
                    }

                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                } else {
                    // Revert to original quantity if there's an error
                    if (response.max_quantity) {
                        cartItem.attr('data-max-stock', response.max_quantity);
                    }

                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });

                Toast.fire({
                    icon: 'error',
                    title: 'An error occurred. Please refresh the page and try again.'
                });
            },
            complete: function() {
                // Re-enable buttons
                cartItem.find('.quantity-btn').prop('disabled', false);
            }
        });
    }

    function deleteItem(productId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Remove this item from your cart?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteBtn = $(`.delete-btn[data-product-id="${productId}"]`);
                deleteBtn.prop('disabled', true);

                $.ajax({
                    url: window.location.pathname,
                    type: 'POST',
                    data: {
                        ajax: true,
                        action: 'delete_item',
                        product_id: productId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $(`.cart-item[data-product-id="${productId}"]`).fadeOut(300,
                                function() {
                                    $(this).remove();
                                    recalculateTotal();
                                    checkEmptyCart();
                                });

                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Failed to remove item'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });

                        Toast.fire({
                            icon: 'error',
                            title: 'An error occurred while deleting the item'
                        });
                    },
                    complete: function() {
                        deleteBtn.prop('disabled', false);
                    }
                });
            }
        });
    }

    function clearCart() {
        Swal.fire({
            title: 'Clear entire cart?',
            text: "This will remove all items from your cart!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, clear cart!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#clear-cart-btn').prop('disabled', true);

                $.ajax({
                    url: window.location.pathname,
                    type: 'POST',
                    data: {
                        ajax: true,
                        action: 'clear_cart'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('.cart-item').fadeOut(300, function() {
                                $('#cart-items').html(`
                                    <div class="empty-cart">
                                        <h3>Your cart is empty</h3>
                                        <p>Looks like you haven't added any items to your cart yet.</p>
                                        <a href="dashboard.php" class="btn btn-primary">Continue Shopping</a>
                                    </div>
                                `);
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Cart cleared successfully'
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Failed to clear cart'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });

                        Toast.fire({
                            icon: 'error',
                            title: 'An error occurred while clearing the cart'
                        });
                    },
                    complete: function() {
                        $('#clear-cart-btn').prop('disabled', false);
                    }
                });
            }
        });
    }

    // More reliable function to recalculate total
    function recalculateTotal() {
        let total = 0;

        $('.cart-item:visible').each(function() {
            const $item = $(this);
            const price = parseFloat($item.attr('data-price')) || 0;
            const qty = parseInt($item.find('.quantity-display').text()) || 0;
            const itemTotal = price * qty;

            total += itemTotal;
        });

        // Update the grand total display
        $('#grand-total').text(number_format(total, 2));
    }

    // Alternative function that works with the current display values
    function updateGrandTotal() {
        let total = 0;

        $('.cart-item:visible').each(function() {
            const lineTotalText = $(this).find('.line-total').text();
            const lineTotal = parseFloat(lineTotalText.replace(/,/g, '')) || 0;
            total += lineTotal;
        });

        $('#grand-total').text(number_format(total, 2));
    }

    function checkEmptyCart() {
        if ($('.cart-item:visible').length === 0) {
            $('#cart-items').fadeOut(200, function() {
                $(this).html(`
                    <div class="empty-cart">
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="dashboard.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                `).fadeIn(200);
            });
        }
    }

    function number_format(number, decimals = 2) {
        const num = parseFloat(number) || 0;
        return num.toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    // Initialize totals on page load (in case of any discrepancies)
    recalculateTotal();
});
</script>

<?php include 'footer.php'; ?>