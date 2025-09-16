<?php 
include 'header.php';
include '../includes/db.php';

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

?>
<!-- Welcome Section with Text -->
<section class="text">
    <div class="text-content">
        <h1>Welcome to Bulaklakan ni Jay</h1>
        <p>“Where flowers bloom so does hope.” – Lady Bird Johnson</p>
    </div>
</section>

<!-- Image Slider Section -->
<section class="slider">
    <div class="slides">
        <img src="../Media/Sunflower.png" class="slide" alt="Sunflower">
        <img src="../Media/Tulips.jpg" class="slide" alt="Tulips">
        <img src="../Media/Rose.png" class="slide" alt="Roses">
        <img src="../Media/6-res-roses-.jpg" class="slide" alt="ROSE">
        <img src="../Media/Rose.jpg" class="slide" alt="Rose">
        <img src="../Media/flowers.jpg" class="slide" alt="Flower">
    </div>
</section>
<script src="../home.js"></script>
<?php include 'footer.php'; ?>