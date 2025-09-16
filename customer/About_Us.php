<?php 

include 'header.php'; 

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;

if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

?>
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
        <img src="../images/about us.jpg" alt="About Us Image">
    </div>
</div>
<!-- Footer -->
<?php include 'footer.php'; ?>