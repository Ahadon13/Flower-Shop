<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<title>Bulaklakan ni Jay</title>
<style>
/* Reset & base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #FAF8F6;
    color: #444;
    line-height: 1.6;
}

/* Header */
header {
    display: flex;
    align-items: center;
    justify-content: center;
    /* center logo + nav as a group */
    background: #FFF574;
    padding: 10px 40px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    gap: 40px;
    /* space between logo and nav */
}

/* Logo */
.site-logo {
    width: 60px;
    height: auto;
    border-radius: 50%;
}

/* Navigation */
nav {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

nav a {
    font-weight: bold;
    color: #5A3E36;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background 0.3s, color 0.3s;
}

nav a:hover {
    background: yellow;
    color: red;
}

/* Main content area */
main {
    max-width: 1000px;
    margin: 0 auto 40px;
    padding: 0 20px;
}

/* Utility: centered container */
.container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cart-icon-link {
    margin-left: auto;
    color: black;
    /* 👈 Makes icon and text black */
    font-weight: bold;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
}

.cart-icon-link:hover {
    background-color: yellow;
}
</style>

<body>
    <header>
        <img src="Media/logo.jpg" alt="Bulaklakan ni Jay Logo" class="site-logo">
        <nav>
            <a href="index.php">Home</a>
            <a href="About_Us.php">About Us</a>
            <a href="flower_gallery.php">Flowers</a>
            <a href="contact.php">Contact</a>
            <?php if (empty($_SESSION['authenticated'])): ?>
            <a href="signup.php">Sign Up</a>
            <?php endif; ?>
            </a>
            <?php if (!empty($_SESSION['authenticated'])): ?>
            <a href="/customer/dashboard.php">
                🏠
                Dashboard
            </a>
            <?php endif; ?>
        </nav>
    </header>
</body>

</html>