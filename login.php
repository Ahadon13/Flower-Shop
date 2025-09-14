<?php
session_start();
include 'includes/db.php';
include 'includes/FAQ_icon.php';
$error = '';

// Check if user is already logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
  header("Location: index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];

    // Prepare & execute query
    $stmt = $conn->prepare("SELECT id, full_name, profile_image, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify user and password
    if ($user) {
        // User exists, check password
        if (password_verify($password_input, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_profile'] = $user['profile_image'];
            $_SESSION['authenticated'] = true;

             // --- Load user's cart items from DB ---
            $cartQuery = $conn->prepare("
                SELECT product_id, SUM(quantity) as total_quantity
                FROM cart_items 
                WHERE user_id = ?
                GROUP BY product_id
            ");
            $cartQuery->bind_param("i", $user['id']);
            $cartQuery->execute();
            $cartResult = $cartQuery->get_result();

            $_SESSION['cart'] = [];
            while ($row = $cartResult->fetch_assoc()) {
                $_SESSION['cart'][$row['product_id']] = $row['total_quantity'];
            }

            header("Location: customer/dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <style>
    :root {
        --accent: #4CAF50;
        --accent-dark: #45a049;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background: #f9f9f9;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        flex-direction: column;
    }

    a.back-link {
        display: inline-block;
        margin-bottom: 1em;
        color: #C96868;
        text-decoration: none;
        font-weight: bold;
    }

    .error-message {
        color: red;
        margin-bottom: 15px;
        font-size: 14px;
        text-align: center;
        width: 100%;
    }

    .login-box {
        background-color: #fff;
        text-align: center;
        width: 100%;
        max-width: 420px;
        margin: 60px auto;
        padding: 28px;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        border-top: 5px solid #FFF574;
    }

    .login-box h2 {
        margin-bottom: 18px;
        color: #333;
    }

    .form-group {
        margin: 10px 0;
        width: 100%;
        position: relative;
    }

    input[type="email"],
    input[type="password"],
    input[type="text"] {
        width: 100%;
        padding: 12px 44px 12px 12px;
        /* leave space for toggle */
        margin: 0;
        border: 1px solid #ccc;
        border-radius: 10px;
        font-size: 14px;
        box-sizing: border-box;
    }

    /* Toggle button (eye) */
    .password-toggle {
        position: absolute;
        top: 50%;
        right: 8px;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        padding: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .password-toggle:focus {
        outline: 2px solid rgba(76, 175, 80, 0.25);
    }

    .password-toggle svg {
        width: 20px;
        height: 20px;
        display: block;
    }

    button[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: var(--accent);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        margin-top: 12px;
    }

    button[type="submit"]:hover {
        background-color: var(--accent-dark);
    }

    .meta {
        font-size: 13px;
        margin-top: 12px;
    }

    .meta a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <!-- Back link to index.php -->
    <a href="index.php" class="back-link">← Back to Home</a>

    <div class="login-box" aria-labelledby="login-heading">
        <h2 id="login-heading">Login</h2>
        <form method="POST" autocomplete="off" novalidate>
            <div class="form-group">
                <label for="email" class="sr-only" style="display:none;">Email</label>
                <input id="email" type="email" name="email" placeholder="Email" required>
            </div>

            <div class="form-group" style="margin-bottom: 6px;">
                <label for="password" class="sr-only" style="display:none;">Password</label>
                <input id="password" type="password" name="password" placeholder="Password" required
                    aria-describedby="toggleHelp">
                <button type="button" id="togglePassword" class="password-toggle" aria-pressed="false"
                    aria-label="Show password" title="Show password">
                    <!-- Eye icon (open) -->
                    <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7z"
                            stroke="#333" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="3" stroke="#333" stroke-width="1.2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <!-- Eye-off icon (closed), hidden by default -->
                    <svg id="eyeClosed" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true" style="display:none;">
                        <path
                            d="M17.94 17.94C16.11 19.12 14.12 19.8 12 19.8c-5 0-9.27-3.11-11-7 1.03-2.3 2.66-4.2 4.64-5.5"
                            stroke="#333" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M1 1l22 22" stroke="#333" stroke-width="1.2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <?php if (!empty($error)): ?>
            <div class="error-message" style="margin-top: 10px;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>

            <button type="submit">Login</button>

            <div class="meta">
                <p style="margin:8px 0 0;">Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>
        </form>
    </div>

    <script>
    (function() {
        const pwd = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        toggle.addEventListener('click', () => {
            const isHidden = pwd.type === 'password';
            if (isHidden) {
                pwd.type = 'text';
                toggle.setAttribute('aria-pressed', 'true');
                toggle.setAttribute('aria-label', 'Hide password');
                toggle.title = 'Hide password';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                pwd.type = 'password';
                toggle.setAttribute('aria-pressed', 'false');
                toggle.setAttribute('aria-label', 'Show password');
                toggle.title = 'Show password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        });

        // Optional: allow toggling with Enter/Space when focused
        toggle.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggle.click();
            }
        });
    })();
    </script>
</body>

</html>