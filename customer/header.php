<?php
ob_start();
session_start();
// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800; // 30 minutes in seconds

// Check if the session has a timestamp
if (isset($_SESSION['last_activity'])) {
    // Calculate time since last activity
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    // If more than 30 minutes have passed, destroy session and redirect
    if ($elapsed_time > $timeout_duration) {
        session_destroy();
        header("Location: ../login.php?timeout=1");
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bulaklakan ni Jay</title>
    <link rel="stylesheet" href="../css/customer/style.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- jQuery and SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <header>
        <div class="header-container">
            <img src="../Media/logo.jpg" alt="Bulaklakan ni Jay Logo" class="site-logo">
            <nav>
                <a href="dashboard.php" style="position: relative; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="#5A3E36" style="width: 24px; height: 24px; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Dashboard
                </a>
                <a href="cart.php" id="cart-link" style="position: relative; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="#5A3E36" style="width: 24px; height: 24px; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <?php
                    $count = 0;
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $qty) {
                            $count += $qty;
                        }
                    }
                    ?>
                    <span id="cart-count"
                        style="position: absolute; top: 5px; left: 4px; background-color: #ff4444; color: white; border-radius: 100%; padding: 5px; font-size: 12px; font-weight: bold; display: flex; align-items: center; justify-content: center; height: 18px; min-width: 18px; text-align: center; line-height: 1;">
                        <?php echo $count > 0 ? $count : '0'; ?>
                    </span>
                    Cart
                </a>
                <a href="orders.php" id="cart-link" style="position: relative; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="#5A3E36" style="width: 24px; height: 24px; vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    Orders
                </a>
                <div class="profile-dropdown">
                    <a href="#" class="profile-toggle">
                        <?php if(isset($_SESSION['user_profile']) && !empty($_SESSION['user_profile'])): ?>
                        <img src="../uploads/profiles/<?php echo $_SESSION['user_profile']; ?>" alt="Profile"
                            class="profile-image">
                        <?php else: ?>
                        <img src="../uploads/profiles/default.jpg" alt="Profile" class="profile-image">
                        <?php endif; ?>
                        <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Profile'; ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="../customer/profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Log out</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <script>
    $(document).ready(function() {
        $('.profile-toggle').click(function(e) {
            e.preventDefault();
            $('.dropdown-menu').toggleClass('show');
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('.profile-dropdown').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    });
    </script>
    <main>