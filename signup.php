<?php
include 'includes/db.php';
include 'includes/header.php';
// Check if user is already logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign Up - Bulaklakan ni Jay</title>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #fffefc;
        color: #2f2f2f;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 500px;
        margin: 60px auto;
        background-color: #f9fff7;
        padding: 30px 40px;
        border-radius: 12px;
        border: 1px solid #d8e6d4;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        border-top: 5px solid #FFF574;
    }

    h2 {
        text-align: center;
        color: black;
        margin-bottom: 20px;
        font-family: 'Georgia', serif;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: black;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }

    /* Password input container for positioning the eye icon */
    .password-container {
        position: relative;
        margin-bottom: 16px;
    }

    .password-container input[type="password"],
    .password-container input[type="text"] {
        width: 100%;
        padding: 10px 40px 10px 10px;
        /* Add right padding for icon */
        margin-bottom: 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }

    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
        font-size: 1.1rem;
        transition: color 0.2s;
    }

    .password-toggle:hover {
        color: #4CAF50;
    }

    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 12px;
        width: 100%;
        font-size: 1.1rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    input[type="submit"]:hover {
        background-color: #388e3c;
    }

    .login-link {
        text-align: center;
        margin-top: 20px;
        font-size: 0.95rem;
    }

    .login-link a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: bold;
    }

    .login-link a:hover {
        text-decoration: underline;
    }

    .preview {
        text-align: center;
        margin-bottom: 15px;
    }

    .preview img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-position: center;
        object-fit: cover;
        border: 2px solid #ccc;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Create Your Account</h2>
        <form action="../models/process_signup.php" method="POST" enctype="multipart/form-data">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye password-toggle" id="togglePassword"
                    onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
            </div>

            <label for="confirm">Confirm Password</label>
            <div class="password-container">
                <input type="password" id="confirm" name="confirm" required>
                <i class="fas fa-eye password-toggle" id="toggleConfirm"
                    onclick="togglePasswordVisibility('confirm', 'toggleConfirm')"></i>
            </div>

            <label for="profile">Profile Image</label>
            <input type="file" id="profile" name="profile" accept="image/*" required onchange="previewImage(event)">

            <div class="preview">
                <img id="preview-img" src="https://via.placeholder.com/120" alt="Profile Preview">
            </div>

            <input type="submit" value="Sign Up">
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <!-- SweetAlert2 JS CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.all.min.js"></script>

    <script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('preview-img').src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    function togglePasswordVisibility(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(toggleId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // SweetAlert2 Toast Configuration
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

    // Check for session flash messages and display appropriate toast
    <?php if (isset($_SESSION['flash_message'])): ?>
    <?php if ($_SESSION['flash_type'] === 'success'): ?>
    Toast.fire({
        icon: 'success',
        title: '<?php echo addslashes($_SESSION['flash_message']); ?>'
    });
    <?php elseif ($_SESSION['flash_type'] === 'error'): ?>
    Toast.fire({
        icon: 'error',
        title: '<?php echo addslashes($_SESSION['flash_message']); ?>'
    });
    <?php elseif ($_SESSION['flash_type'] === 'warning'): ?>
    Toast.fire({
        icon: 'warning',
        title: '<?php echo addslashes($_SESSION['flash_message']); ?>'
    });
    <?php elseif ($_SESSION['flash_type'] === 'info'): ?>
    Toast.fire({
        icon: 'info',
        title: '<?php echo addslashes($_SESSION['flash_message']); ?>'
    });
    <?php endif; ?>
    <?php 
        // Clear the flash message after displaying
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    // Optional: Add form validation with SweetAlert2
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm').value;

        if (password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'Password and confirm password do not match!',
                confirmButtonColor: '#4CAF50'
            });
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password should be at least 6 characters long!',
                confirmButtonColor: '#4CAF50'
            });
            return false;
        }
    });
    </script>
</body>

</html>
<?php include 'includes/footer.php'; ?>