<?php 

include 'header.php'; 

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

?>
<style>
/* Title heading */
h1 {
    text-align: center;
    color: #D2665A;
    margin-bottom: 30px;
    font-size: 30px;
    font-family: 'Lora', serif;
}

/* Grid layout for flowers */
.flower-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 4%;
    cursor: pointer;
}

/* Individual flower card styling */
.flower-card {
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.2s ease;
}

.flower-card:hover {
    transform: translateY(-5px);
}

.flower-card img {
    width: 90%;
    height: 70%;
    display: block;
}

.flower-info {
    padding: 20px;
    text-align: center;
}

.flower-info h2 {
    font-size: 22px;
    color: #b54e58;
    margin-bottom: 10px;
}

.flower-info p {
    font-size: 15px;
    color: #555;
}

.category-section {
    text-align: center;
    margin: 30px 0;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin: 20px auto;
    max-width: 1000px;
}

.category-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    text-align: center;
    transition: transform 0.2s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.category-card:hover {
    transform: scale(1.05);
}

.category-card img {
    width: auto;
    height: 250px;
    object-fit: cover;
    object-position: center;
}

.category-card h2 {
    margin: 12px 0;
    color: #333;
}
</style>

<div class="category-section">
    <h1>Book Events</h1>
    <div class="category-grid">
        <a href="valentines.php" class="category-card">
            <img src="../Media/valentine_roses-removebg-preview.png" alt="Valentines">
            <h2>Valentines</h2>
        </a>
        <a href="wedding.php" class="category-card">
            <img src="../Media/wedding_flower-removebg-preview.png" alt="Wedding">
            <h2>Wedding</h2>
        </a>
        <a href="funeral.php" class="category-card">
            <img src="../Media/funeral_flowers4-removebg-preview.png" alt="Funeral">
            <h2>Funeral</h2>
        </a>
        <a href="birthday.php" class="category-card">
            <img src="../Media/Bulaklakan-Carnation-removebg-preview.png" alt="Birthday">
            <h2>Birthday</h2>
        </a>
    </div>
</div>


<?php include 'footer.php'; ?>