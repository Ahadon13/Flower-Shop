<?php
include '../includes/db.php';
include 'header.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $success = [];

    // Update Profile Information
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        if (empty($username)) {
            $errors[] = "Username is required";
        }
        
        if (empty($errors)) {
            // Check if username already exists for other admins
            $checkQuery = "SELECT id FROM admins WHERE username = ? AND id != ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("si", $username, $admin_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Username already exists";
            } else {
                $updateQuery = "UPDATE admins SET full_name = ?, username = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("ssi", $full_name, $username, $admin_id);
                
                if ($updateStmt->execute()) {
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_full_name'] = $full_name;
                    $success[] = "Profile updated successfully";
                } else {
                    $errors[] = "Error updating profile";
                }
            }
        }
    }
    
    // Update Password
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = "All password fields are required";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        } else {
            // Verify current password
            $verifyQuery = "SELECT password FROM admins WHERE id = ?";
            $verifyStmt = $conn->prepare($verifyQuery);
            $verifyStmt->bind_param("i", $admin_id);
            $verifyStmt->execute();
            $result = $verifyStmt->get_result();
            $admin = $result->fetch_assoc();
            
            // Check if current password uses hashing
            $currentPasswordValid = false;
            if (password_verify($current_password, $admin['password'])) {
                $currentPasswordValid = true;
            } elseif ($current_password === $admin['password']) {
                // Plain text password (legacy)
                $currentPasswordValid = true;
            }
            
            if (!$currentPasswordValid) {
                $errors[] = "Current password is incorrect";
            } else {
                // Hash the new password
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                
                $updateQuery = "UPDATE admins SET password = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("si", $hashedPassword, $admin_id);
                
                if ($updateStmt->execute()) {
                    $success[] = "Password updated successfully";
                } else {
                    $errors[] = "Error updating password";
                }
            }
        }
    }
    
    // Update GCash Settings
    if (isset($_POST['update_gcash'])) {
        $gcash_number = trim($_POST['gcash_number']);
        $gcash_name = trim($_POST['gcash_name']);
        
        if (empty($gcash_number)) {
            $errors[] = "GCash number is required";
        }
        if (empty($gcash_name)) {
            $errors[] = "GCash account name is required";
        }
        
        // Validate phone number format
        if (!empty($gcash_number) && !preg_match('/^(09|\+639)\d{9}$/', $gcash_number)) {
            $errors[] = "Please enter a valid Philippine mobile number";
        }
        
        $gcash_qr_path = null;
        
        // Handle QR code upload
        if (isset($_FILES['gcash_qr']) && $_FILES['gcash_qr']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['gcash_qr'];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                $fileType = mime_content_type($file['tmp_name']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "QR code must be a JPEG or PNG image";
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $errors[] = "QR code image must be less than 5MB";
                } else {
                    $uploadDir = '../uploads/gcash/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = 'gcash_qr_' . time() . '_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $gcash_qr_path = $fileName;
                        
                        // Delete old QR code if exists
                        $oldQrQuery = "SELECT gcash_qr_code FROM admins WHERE id = ?";
                        $oldQrStmt = $conn->prepare($oldQrQuery);
                        $oldQrStmt->bind_param("i", $admin_id);
                        $oldQrStmt->execute();
                        $oldResult = $oldQrStmt->get_result();
                        $oldAdmin = $oldResult->fetch_assoc();
                        
                        if (!empty($oldAdmin['gcash_qr_code']) && file_exists($uploadDir . $oldAdmin['gcash_qr_code'])) {
                            unlink($uploadDir . $oldAdmin['gcash_qr_code']);
                        }
                    } else {
                        $errors[] = "Error uploading QR code image";
                    }
                }
            }
        }
        
        if (empty($errors)) {
            $updateQuery = "UPDATE admins SET gcash_number = ?, gcash_name = ?";
            $params = [$gcash_number, $gcash_name];
            $types = "ss";
            
            if ($gcash_qr_path) {
                $updateQuery .= ", gcash_qr_code = ?";
                $params[] = $gcash_qr_path;
                $types .= "s";
            }
            
            $updateQuery .= " WHERE id = ?";
            $params[] = $admin_id;
            $types .= "i";
            
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param($types, ...$params);
            
            if ($updateStmt->execute()) {
                $success[] = "GCash settings updated successfully";
            } else {
                $errors[] = "Error updating GCash settings";
            }
        }
    }
    
    $_SESSION['settings_messages'] = [
        'errors' => $errors,
        'success' => $success
    ];
    
    header("Location: settings.php");
    exit();
}

// Fetch current admin data
$adminQuery = "SELECT * FROM admins WHERE id = ?";
$adminStmt = $conn->prepare($adminQuery);
$adminStmt->bind_param("i", $admin_id);
$adminStmt->execute();
$result = $adminStmt->get_result();
$currentAdmin = $result->fetch_assoc();
?>
<div class="settings-container">
    <div class="settings-header">
        <h1>⚙️ Admin Settings</h1>
        <p>Manage your account and system settings</p>
    </div>
    <?php if (isset($_SESSION['settings_messages'])) { $messages = $_SESSION['settings_messages']; if (!empty($messages['errors'])) { echo '<div class="alert alert-danger">'; foreach ($messages['errors'] as $error) { echo '<p>• ' . htmlspecialchars($error) . '</p>'; } echo '</div>'; } if (!empty($messages['success'])) { echo '<div class="alert alert-success">'; foreach ($messages['success'] as $success) { echo '<p>• ' . htmlspecialchars($success) . '</p>'; } echo '</div>'; } unset($_SESSION['settings_messages']); } ?>
    <div class="settings-tabs"> <button class="tab-button active" data-tab="profile">👤 Profile</button> <button
            class="tab-button" data-tab="password">🔒 Password</button> <button class="tab-button" data-tab="gcash">💰
            GCash Settings</button> </div> <!-- Profile Tab -->
    <div class="tab-content active" id="profile">
        <div class="settings-card">
            <h3>Profile Information</h3>
            <form method="post">
                <div class="form-row">
                    <div class="form-group"> <label for="full_name">Full Name</label> <input type="text" id="full_name"
                            name="full_name" value="<?= htmlspecialchars($currentAdmin['full_name'] ?? ''); ?>"
                            required>
                        <div class="help-text">Your display name for the admin panel</div>
                    </div>
                    <div class="form-group"> <label for="username">Username</label> <input type="text" id="username"
                            name="username" value="<?= htmlspecialchars($currentAdmin['username']); ?>" required>
                        <div class="help-text">Used for logging into the admin panel</div>
                    </div>
                </div> <button type="submit" name="update_profile" class="btn">Update Profile</button>
            </form>
        </div>
    </div> <!-- Password Tab -->
    <div class="tab-content" id="password">
        <div class="settings-card">
            <h3>Change Password</h3>
            <form method="post">
                <div class="form-group"> <label for="current_password">Current Password</label> <input type="password"
                        id="current_password" name="current_password" required> </div>
                <div class="form-row">
                    <div class="form-group"> <label for="new_password">New Password</label> <input type="password"
                            id="new_password" name="new_password" required> </div>
                    <div class="form-group"> <label for="confirm_password">Confirm New Password</label> <input
                            type="password" id="confirm_password" name="confirm_password" required> </div>
                </div>
                <div class="password-requirements">
                    <h5>Password Requirements:</h5>
                    <ul>
                        <li>At least 6 characters long</li>
                        <li>Should contain letters and numbers</li>
                        <li>Avoid using personal information</li>
                    </ul>
                </div> <button type="submit" name="update_password" class="btn">Update Password</button>
            </form>
        </div>
    </div> <!-- GCash Tab -->
    <div class="tab-content" id="gcash">
        <div class="settings-card">
            <h3>GCash Payment Settings</h3>
            <p style="color: #666; margin-bottom: 20px;"> Configure your GCash details for customer payments. This
                information will be displayed to customers during checkout. </p>
            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group"> <label for="gcash_number">GCash Number</label> <input type="tel"
                            id="gcash_number" name="gcash_number"
                            value="<?= htmlspecialchars($currentAdmin['gcash_number'] ?? ''); ?>"
                            placeholder="09XX-XXX-XXXX" required>
                        <div class="help-text">Enter your GCash mobile number (e.g., 09XX-XXX-XXXX)</div>
                    </div>
                    <div class="form-group"> <label for="gcash_name">GCash Account Name</label> <input type="text"
                            id="gcash_name" name="gcash_name"
                            value="<?= htmlspecialchars($currentAdmin['gcash_name'] ?? ''); ?>"
                            placeholder="Full Name as registered in GCash" required>
                        <div class="help-text">Name registered to your GCash account</div>
                    </div>
                </div>
                <div class="form-group"> <label for="gcash_qr">GCash QR Code Image</label> <input type="file"
                        id="gcash_qr" name="gcash_qr" accept="image/*">
                    <div class="help-text">Upload your GCash QR code image (JPEG/PNG, max 5MB)</div>
                    <?php if (!empty($currentAdmin['gcash_qr_code'])): ?> <div class="qr-preview">
                        <p><strong>Current QR Code:</strong></p> <img
                            src="../uploads/gcash/<?= htmlspecialchars($currentAdmin['gcash_qr_code']); ?>"
                            alt="Current GCash QR Code" class="current-qr">
                    </div> <?php endif; ?>
                </div> <button type="submit" name="update_gcash" class="btn">Update GCash Settings</button>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['settings_messages'])): 
    $messages = $_SESSION['settings_messages']; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test SweetAlert availability
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert not loaded');
        return;
    }

    <?php if (!empty($messages['success'])): ?>
    <?php foreach ($messages['success'] as $msg): ?>
    Swal.fire({
        toast: true,
        icon: 'success',
        title: '<?= addslashes(htmlspecialchars($msg)); ?>',
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($messages['errors'])): ?>
    <?php foreach ($messages['errors'] as $msg): ?>
    Swal.fire({
        toast: true,
        icon: 'error',
        title: '<?= addslashes(htmlspecialchars($msg)); ?>',
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });
    <?php endforeach; ?>
    <?php endif; ?>
});
</script>
<?php unset($_SESSION['settings_messages']); endif; ?>
<script>
$(document).ready(function() {
    // Tab switching
    $('.tab-button').click(function() {
        const targetTab = $(this).data('tab');
        // Remove active classes
        $('.tab-button').removeClass('active');
        $('.tab-content').removeClass('active');
        // Add active classes
        $(this).addClass('active');
        $('#' + targetTab).addClass('active');
    });

    // Password confirmation validation
    $('#confirm_password').on('keyup', function() {
        const newPassword = $('#new_password').val();
        const confirmPassword = $(this).val();
        if (confirmPassword && newPassword !== confirmPassword) {
            $(this).css('border-color', '#dc3545');
        } else {
            $(this).css('border-color', '#ddd');
        }
    });

    // GCash number formatting
    $('#gcash_number').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 0) {
            if (value.startsWith('63')) {
                value = '+' + value;
            } else if (!value.startsWith('09')) {
                value = '09' + value;
            }
        }
        $(this).val(value);
    });

    // File upload preview
    $('#gcash_qr').change(function() {
        const file = this.files[0];
        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG or PNG)');
                $(this).val('');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                $(this).val('');
                return;
            }
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('.qr-preview').html(
                    '<p><strong>New QR Code Preview:</strong></p>' +
                    '<img src="' + e.target.result +
                    '" alt="QR Code Preview" class="current-qr">'
                );
            };
            reader.readAsDataURL(file);
        }
    });

    // Form validation before submit
    $('form').submit(function(e) {
        const form = $(this);
        // Password form validation
        if (form.find('input[name="update_password"]').length) {
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        }
        // GCash form validation
        if (form.find('input[name="update_gcash"]').length) {
            const gcashNumber = $('#gcash_number').val();
            const phoneRegex = /^(09|\+639)\d{9}$/;
            if (!phoneRegex.test(gcashNumber)) {
                e.preventDefault();
                alert('Please enter a valid Philippine mobile number!');
                return false;
            }
        }
    });
});
</script>

<?php include 'footer.php'; ?>