<?php
include '../includes/db.php';
include '../includes/pagination.php';
include '../models/product.php';
include 'header.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$productObj = new Product($conn);

// Handle Add Product
if (isset($_POST['add_product'])) {
    $errors = $productObj->add($_POST, $_FILES['product_image']);
    $_SESSION['messages'] = empty($errors) ? ["success" => "Product added successfully."] : $errors;
    header("Location: products.php");
    exit();
}

// Handle Update Product
if (isset($_POST['update_product'])) {
    $id = (int)$_POST['product_id'];
    $errors = $productObj->update($id, $_POST, $_FILES['product_image']);
    $_SESSION['messages'] = empty($errors) ? ["success" => "Product updated successfully."] : $errors;
    header("Location: products.php");
    exit();
}

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];
    $productObj->delete($id);
    $_SESSION['messages'] = ["success" => "Product deleted successfully."];
    header("Location: products.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$totalRecords = $productObj->getTotal();
$result = $productObj->getPaginated($limit, $offset);
?>


<!-- Add button -->
<div class="top-actions">
    <button id="openAddModal" class="add-btn"><i class="fas fa-plus"></i> Add New Flower</button>
</div>

<!-- Product List -->
<div class="product-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
    <?php 
            $imgPath = !empty($row['image']) ? "../uploads/{$row['image']}" : "https://via.placeholder.com/230x180?text=No+Image";
            $quantity = (int)$row['quantity'];
            
            // Determine stock status
            $stockClass = '';
            $ribbonClass = '';
            $ribbonText = '';
            $quantityClass = 'in-stock';
            
            if ($quantity === 0) {
                $stockClass = 'out-of-stock';
                $ribbonClass = 'out-of-stock';
                $ribbonText = 'Out of Stock';
                $quantityClass = 'out-of-stock';
            } elseif ($quantity <= 5) { // You can adjust this threshold
                $stockClass = 'low-stock';
                $ribbonClass = 'low-stock';
                $ribbonText = 'Low Stock';
                $quantityClass = 'low-stock';
            }
        ?>
    <div class="product-card <?= $stockClass; ?>" data-product='<?= json_encode($row); ?>'>
        <?php if ($ribbonText): ?>
        <div class="stock-ribbon <?= $ribbonClass; ?>"><?= $ribbonText; ?></div>
        <?php endif; ?>

        <img src="<?= $imgPath; ?>" alt="<?= $row['name']; ?>">
        <div class="product-details">
            <h4><?= $row['name']; ?></h4>
            <p class="price">₱<?= number_format($row['price'], 2); ?></p>
            <p>Occasion: <?= $row['occasion']; ?></p>
            <p>Quantity: <span class="quantity-display <?= $quantityClass; ?>"><?= $row['quantity']; ?></span>
            </p>
            <p><?= $row['description']; ?></p>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php renderPagination($totalRecords, $limit, $page, '?page='); ?>

<!-- Add Product Modal -->
<div class="modal" id="addProductModal">
    <div class="modal-content">
        <span class="close-modal" data-close="addProductModal">&times;</span>
        <form method="post" enctype="multipart/form-data" id="addProductForm">
            <h3>Add Product</h3>
            <input type="text" name="product_name" class="box" placeholder="Name" required>
            <input type="number" name="product_price" class="box" placeholder="Price" step="0.01" required>
            <input type="number" name="product_quantity" class="box" placeholder="Quantity" required>
            <select name="product_occasion" class="box" required>
                <option value="">-- Select Occasion --</option>
                <option value="birthday">Birthday</option>
                <option value="wedding">Wedding</option>
                <option value="funeral">Funeral</option>
                <option value="valentine">Valentine</option>
            </select>
            <textarea name="product_description" class="box" placeholder="Description" required></textarea>
            <input type="file" name="product_image" class="box" accept="image/*" required>
            <input type="submit" class="btn" name="add_product" value="Add Product">
        </form>
    </div>
</div>

<!-- Update Product Modal -->
<div class="modal" id="updateProductModal" style="overflow: auto;">
    <div class="modal-content">
        <span class="close-modal" data-close="updateProductModal">&times;</span>
        <form method="post" enctype="multipart/form-data" id="updateProductForm">
            <h3>Update Product</h3>
            <input type="hidden" name="product_id" id="update_id">
            <div class="image-preview">
                <p>Current Image:</p>
                <img id="update_product_image_preview" src="" alt="Product Image"
                    style="max-width: 150px; max-height: 120px; border-radius: 5px; margin-bottom: 10px;">
            </div>
            <input type="text" name="product_name" id="update_name" class="box" required>
            <input type="number" name="product_price" id="update_price" class="box" step="0.01" required>
            <input type="number" name="product_quantity" id="update_quantity" class="box" required>
            <select name="product_occasion" id="update_occasion" class="box" required>
                <option value="">-- Select Occasion --</option>
                <option value="birthday">Birthday</option>
                <option value="wedding">Wedding</option>
                <option value="funeral">Funeral</option>
                <option value="valentine">Valentine</option>
            </select>
            <textarea name="product_description" id="update_description" class="box"></textarea>
            <input type="file" name="product_image" class="box" accept="image/*">
            <input type="submit" class="btn" name="update_product" value="Update">
            <button type="button" class="btn" id="deleteBtn"
                style="background:#dc3545; margin-top: 10px;">Delete</button>
        </form>
    </div>
</div>

</main>

<script>
$(document).ready(function() {
    // Show toast notifications for session messages
    <?php if (!empty($_SESSION['messages'])): ?>
    <?php foreach ($_SESSION['messages'] as $type => $msg): ?>
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
        icon: '<?= is_string($type) ? $type : 'error'; ?>',
        title: '<?= addslashes($msg); ?>'
    });
    <?php endforeach; ?>
    <?php unset($_SESSION['messages']); ?>
    <?php endif; ?>

    // Modal handling
    function openModal(id) {
        $('#' + id).css('display', 'flex');
    }

    function closeModal(id) {
        $('#' + id).css('display', 'none');
    }

    $('.close-modal').on('click', function() {
        closeModal($(this).data('close'));
    });

    // Close modal when clicking outside
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            $(this).css('display', 'none');
        }
    });

    // Add product modal
    $('#openAddModal').on('click', function() {
        openModal('addProductModal');
    });

    // Update product modal - enhanced to show stock status warning
    $('.product-card').on('click', function() {
        const p = JSON.parse($(this).attr('data-product'));
        const quantity = parseInt(p.quantity);

        $('#update_id').val(p.id);
        $('#update_name').val(p.name);
        $('#update_price').val(p.price);
        $('#update_quantity').val(p.quantity);
        $('#update_occasion').val(p.occasion);
        $('#update_description').val(p.description);

        // Set image preview
        const imageSrc = p.image ? "../uploads/" + p.image :
            "https://via.placeholder.com/150x120?text=No+Image";
        $("#update_product_image_preview").attr('src', imageSrc);

        // Show stock status warning if applicable
        if (quantity === 0) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: 'warning',
                title: 'This product is out of stock!'
            });
        } else if (quantity <= 5) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: 'info',
                title: 'This product has low stock (≤5 items)'
            });
        }

        openModal('updateProductModal');
    });

    // Form validation for Add Product
    $('#addProductForm').on('submit', function(e) {
        const name = $('input[name="product_name"]').val().trim();
        const price = $('input[name="product_price"]').val();
        const quantity = $('input[name="product_quantity"]').val();
        const occasion = $('select[name="product_occasion"]').val();
        const description = $('textarea[name="product_description"]').val().trim();
        const image = $('input[name="product_image"]')[0].files.length;

        if (!name || !price || !quantity || !occasion || !description || image === 0) {
            e.preventDefault();

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

            Toast.fire({
                icon: 'warning',
                title: 'Please fill out all fields and select an image.'
            });

            return false;
        }

        // Validate price and quantity are positive numbers
        if (parseFloat(price) <= 0 || parseInt(quantity) < 0) {
            e.preventDefault();

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

            Toast.fire({
                icon: 'error',
                title: 'Price must be greater than 0 and quantity cannot be negative.'
            });

            return false;
        }
    });

    // Form validation for Update Product
    $('#updateProductForm').on('submit', function(e) {
        const name = $('#update_name').val().trim();
        const price = $('#update_price').val();
        const quantity = $('#update_quantity').val();
        const occasion = $('#update_occasion').val();

        if (!name || !price || !quantity || !occasion) {
            e.preventDefault();

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

            Toast.fire({
                icon: 'warning',
                title: 'Please fill out all required fields.'
            });

            return false;
        }

        // Validate price and quantity are positive numbers
        if (parseFloat(price) <= 0 || parseInt(quantity) < 0) {
            e.preventDefault();

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

            Toast.fire({
                icon: 'error',
                title: 'Price must be greater than 0 and quantity cannot be negative.'
            });

            return false;
        }
    });

    // Delete confirmation with SweetAlert
    $('#deleteBtn').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a hidden form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const productIdInput = document.createElement('input');
                productIdInput.type = 'hidden';
                productIdInput.name = 'product_id';
                productIdInput.value = $('#update_id').val();

                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_product';
                deleteInput.value = '1';

                form.appendChild(productIdInput);
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // File input validation (image files only)
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
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

                Toast.fire({
                    icon: 'error',
                    title: 'Please select a valid image file (JPEG, PNG, GIF).'
                });

                this.value = '';
                return false;
            }

            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
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

                Toast.fire({
                    icon: 'error',
                    title: 'File size must be less than 5MB.'
                });

                this.value = '';
                return false;
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>