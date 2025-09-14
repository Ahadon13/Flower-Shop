<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <style>
    /* Reset Defaults */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* This CSS is for the body */
    body {
        font-family: 'Inter', sans-serif;
    }

    /* Navigation Bar CSS Design */
    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: #FFF574;
    }

    /* For Logo */
    .logo img {
        height: 70px;
        width: 70px;
        border-radius: 120px;
    }

    /* Navigation Links */
    ul {
        list-style: none;
        display: flex;
    }

    ul li {
        margin: 0 15px;
        position: relative;
    }

    ul li a {
        color: #626F47;
        text-decoration: none;
        font-weight: bold;
        font-size: larger;
        font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
    }

    /* Hero Section in the Body */
    .hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 50px;
        background: #f9f9f9;
        text-align: justify;
        flex-wrap: wrap;
    }

    /* Text Area in the Hero Section */
    .hero-text {
        flex: 1;
        padding-right: 50px;
        margin-bottom: 20px;
    }

    /* Image Area in the Hero Section */
    .hero-image {
        flex: 1;
        text-align: center;
    }

    /* Hero Image Responsiveness */
    .hero-image img {
        max-width: 100%;
        height: auto;
    }

    /* Hero Heading Style */
    .hero h1 {
        font-size: 3em;
        margin-bottom: 20px;
        color: #333;
    }

    /* Paragraph Style */
    .hero p {
        font-size: 1.2em;
        line-height: 1.6;
        color: #555;
    }

    /* Call to Action Button */
    .button {
        background-color: #ffa500;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.2em;
        margin-top: 20px;
    }

    /* Button Hover Effect */
    button:hover {
        background-color: #FBE4B3;
    }

    /* Footer Styling */
    footer {
        background: #FFF574;
        text-align: center;
        font-size: 14px;
        height: 70px;
        margin-top: 50px;
        padding: 10px;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        nav {
            flex-direction: column;
            align-items: flex-start;
        }

        ul {
            flex-direction: column;
            width: 100%;
        }

        ul li {
            margin: 10px 0;
        }

        .hero {
            flex-direction: column;
            padding: 20px;
            text-align: center;
        }

        .hero-text {
            padding-right: 0;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 2.5em;
        }

        .hero p {
            font-size: 1em;
        }
    }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-text">
            <h1>ABOUT US</h1>
            <p>In 1996, Manuel Deguito turned his passion for floristry into a thriving family business, founding
                Bulaklakan ni Jay in Calamba City, Laguna. Inspired by his professor’s excellence in floral artistry, he
                set out to create a flower shop that combined traditional floral designs with modern artistry. Over the
                years, Bulaklakan ni Jay has built a strong reputation for providing fresh, high-quality floral
                arrangements for all occasions.</p>
            <p>At Bulaklakan ni Jay, we offer a wide variety of flowers, including roses, sunflowers, carnations,
                tulips, and more. Whether you need a bouquet for a romantic gesture, a wedding arrangement, or a solemn
                tribute, we specialize in customizing floral designs to suit your needs and preferences. Our commitment
                to quality and affordability ensures that each bouquet is thoughtfully crafted while fitting within your
                budget.</p>
            <p>At Bulaklakan ni Jay, we believe that flowers speak the language of love, sympathy, and celebration.
                Check our website today and experience the beauty of handcrafted floral arrangements made with passion
                and care.</p>
            <button class="button" onclick="location.href='reviews.php'">Send us your feedback</button>

        </div>
        <div class="hero-image">
            <img src="images/about us.jpg" alt="About Us Image">
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>

</html>