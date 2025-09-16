<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bulaklakan ni Jay - Book Events</title>
    <link rel="stylesheet" href="flower_gallery.css">
    <style>
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
</head>

<body>
    <div class="category-section">
        <h1>Book Events</h1>
        <div class="category-grid">
            <a href="valentines.php" class="category-card">
                <img src="Media/valentine_roses-removebg-preview.png" alt="Valentines">
                <h2>Valentines</h2>
            </a>
            <a href="wedding.php" class="category-card">
                <img src="Media/wedding_flower-removebg-preview.png" alt="Wedding">
                <h2>Wedding</h2>
            </a>
            <a href="funeral.php" class="category-card">
                <img src="Media/funeral_flowers4-removebg-preview.png" alt="Funeral">
                <h2>Funeral</h2>
            </a>
            <a href="birthday.php" class="category-card">
                <img src="Media/Bulaklakan-Carnation-removebg-preview.png" alt="Birthday">
                <h2>Birthday</h2>
            </a>
        </div>
    </div>
</body>

</html>

<?php include 'includes/footer.php'; ?>