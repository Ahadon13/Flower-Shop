<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bulaklakan ni Jay</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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

    .page-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .category-nav {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .category-nav a {
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        background-color: #eee;
        color: #333;
        transition: all 0.3s ease;
    }

    .category-nav a.active,
    .category-nav a:hover {
        background-color: #27ae60;
        color: #fff;
    }

    .gallery-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

    .gallery-item {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .product-image {
        width: 100%;
        max-height: 200px;
        object-fit: contain;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .product-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #2c3e50;
    }

    .product-description {
        font-size: 14px;
        margin-bottom: 10px;
        color: #666;
    }

    .product-price {
        font-size: 16px;
        color: #27ae60;
        margin-bottom: 8px;
    }

    .product-quantity {
        font-size: 14px;
        margin-bottom: 15px;
        color: #555;
    }

    .add-to-cart-btn {
        display: inline-block;
        padding: 10px 15px;
        background-color: #27ae60;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .add-to-cart-btn:hover {
        background-color: #2ecc71;
    }

    .place-order-btn {
        display: inline-block;
        padding: 10px 15px;
        background-color: #27ae60;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .place-order-btn:hover {
        background-color: #2ecc71;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        overflow: auto;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        text-align: center;
        position: relative;
    }

    .modal-image {
        width: 300px;
        height: auto;
        margin-bottom: 10px;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        cursor: pointer;
        font-size: 30px;
    }

    .close-order {
        position: absolute;
        top: 10px;
        right: 15px;
        cursor: pointer;
        font-size: 30px;
    }

    .confirm-btn {
        background: #27ae60;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    .confirm-btn:hover {
        background: #2ecc71;
    }
    </style>
</head>

<body>
    <header>
        <img src="Media/logo.jpg" alt="Bulaklakan ni Jay Logo" class="site-logo">
        <nav>
            <a href="index.php">Home</a>
            <a href="About_Us.php">About Us</a>
            <a href="flower_gallery.php">Flowers</a>
            <a href="occasions.php">Occasions</a>
            <a href="contact.php">Contact</a>
            <?php if (empty($_SESSION['authenticated'])): ?>
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Log In</a>
            <?php endif; ?>
            </a>
            <?php if (!empty($_SESSION['authenticated'])): ?>
            <a href="/customer/home.php">
                🏠
                Home
            </a>
            <?php endif; ?>
        </nav>
    </header>
</body>

</html>