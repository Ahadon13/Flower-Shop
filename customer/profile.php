<?php
include '../includes/db.php';
include 'header.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT full_name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_image = $user['profile_image'];

    // Handle image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../uploads/profiles/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $file_name;
        }
    }

    // Initialize stmt variable
    $stmt = null;
    $update_success = false;

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $_SESSION['profile_message'] = "password_mismatch";
            // Don't prepare any statement, just set the error message
            $update_success = false;
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, profile_image = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $hashed_password, $profile_image, $user_id);
            $update_success = $stmt->execute();
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $full_name, $email, $profile_image, $user_id);
        $update_success = $stmt->execute();
    }

    // Handle the result
    if ($update_success) {
        $_SESSION['user_profile'] = $profile_image;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['profile_message'] = "success";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        if (!isset($_SESSION['profile_message'])) {
            $_SESSION['profile_message'] = "error";
        }
    }

    // Close statement if it exists
    if ($stmt !== null) {
        $stmt->close();
    }
}
?>

<div class="profile-container my-5" style="max-width:600px;">
    <h2 class="mb-4 text-center">My Profile</h2>

    <form method="POST" action="profile.php" class="profile-card p-4 shadow" enctype="multipart/form-data">
        <div class="mb-3" style="display: flex; flex-direction: column; align-items: center;">
            <img id="profilePreview"
                src="<?php echo !empty($user['profile_image']) ? '../uploads/profiles/' . htmlspecialchars($user['profile_image']) : 'assets/default-profile.png'; ?>"
                alt="Profile Image" class="profile-image-preview" style="border:3px solid #ddd;">
            <input type="file" class="profile-input mt-3" name="profile_image" id="profileImageInput" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" class="profile-input" id="full_name" name="full_name"
                value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="profile-input" id="email" name="email"
                value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <div class="password-container">
                <input type="password" class="profile-input" id="password" name="password">
                <i class="fas fa-eye password-toggle" id="togglePassword"
                    onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
            </div>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <div class="password-container">
                <input type="password" class="profile-input" id="confirm_password" name="confirm_password">
                <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"
                    onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></i>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
    </form>
</div>

<script>
<?php if (isset($_SESSION['profile_message'])): ?>
<?php if ($_SESSION['profile_message'] === "success"): ?>
Swal.fire({
    toast: true,
    icon: 'success',
    title: 'Profile updated successfully!',
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true
});
<?php elseif ($_SESSION['profile_message'] === "password_mismatch"): ?>
Swal.fire({
    toast: true,
    icon: 'error',
    title: 'Passwords do not match!',
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true
});
<?php elseif ($_SESSION['profile_message'] === "error"): ?>
Swal.fire({
    toast: true,
    icon: 'error',
    title: 'Error updating profile!',
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true
});
<?php endif; ?>
<?php unset($_SESSION['profile_message']); // clear after showing ?>
<?php endif; ?>

document.getElementById("profileImageInput").addEventListener("change", function(event) {
    const [file] = this.files;
    if (file) {
        document.getElementById("profilePreview").src = URL.createObjectURL(file);
    }
});

// Password toggle functionality
function togglePasswordVisibility(passwordFieldId, toggleIconId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleIcon = document.getElementById(toggleIconId);

    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>

<?php include 'footer.php'; ?>