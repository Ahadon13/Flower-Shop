<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 

$toast_message = '';
$toast_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $review = $conn->real_escape_string($_POST['review']);
    $rating = (int)$_POST['rating'];
    $created_at = date('Y-m-d H:i:s');

    if (!empty($name) && !empty($review) && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (name, review, rating, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $review, $rating, $created_at);
        if ($stmt->execute()) {
            $toast_message = 'Thank you for your feedback!';
            $toast_type = 'success';
        } else {
            $toast_message = 'Failed to submit review. Please try again.';
            $toast_type = 'error';
        }
        $stmt->close();
    } else {
        $toast_message = 'Please fill out all fields correctly.';
        $toast_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Reviews</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #EEEEEE;
        color: #333;
    }

    h1 {
        text-align: center;
        color: #7D0A0A;
        font-size: 2.2em;
    }

    form {
        max-width: 800px;
        margin: 0 auto;
        padding: 2em;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 10px 25px hsla(0, 0.00%, 0.00%, 0.08);
    }

    input,
    textarea {
        width: 100%;
        padding: 12px 7px;
        margin: 0.8em 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        background: #fafafa;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    input:focus,
    textarea:focus {
        border-color: #4CAF50;
        background: #fff;
        box-shadow: 0 0 6px rgba(76, 175, 80, 0.3);
        outline: none;
    }

    button {
        background-color: #4CAF50;
        color: white;
        padding: 14px 25px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
        width: 100%;
        margin-top: 1em;
    }

    button:hover {
        background-color: #45a049;
    }

    .reviews {
        max-width: 800px;
        margin: 1em auto;
        background: #ffffff;
        padding: 2%;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .reviews h2 {
        text-align: center;
        color: #7D0A0A;
        font-size: 1.9em;
    }

    .review-item {
        border-bottom: 1px solid #e2e2e2;
        padding: 1.0em 0;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-item strong {
        font-size: 18px;
        color: #2c3e50;
    }

    .review-item small {
        color: #888;
        display: block;
    }

    .stars {
        color: #FFD700;
        font-size: 22px;
        margin: 5px 0;
    }

    .review-item p {
        font-size: 15.5px;
        color: #444;
        white-space: pre-line;
        margin-top: 10px;
    }

    /* Star Rating Styles */
    .rating-container {
        margin: 0.8em 0;
    }

    .rating-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    /* Hide the default number input */
    #rating {
        display: none;
    }

    /* Custom star rating styles */
    .star-rating {
        display: flex;
        gap: 5px;
        margin-bottom: 10px;
    }

    .star {
        font-size: 30px;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s ease;
        user-select: none;
    }

    .star:hover,
    .star.active {
        color: #FFD700;
    }

    .star.hover {
        color: #FFA500;
    }

    .rating-text {
        font-size: 14px;
        color: #666;
        margin-left: 10px;
        font-style: italic;
    }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <h1>FEEDBACK</h1>
    <p style="text-align: justify; max-width: 700px; margin: 0 auto 2em; font-size: 1.1em; color: #555;">
        We value your thoughts! Please share your experience with us by leaving a rating and review below. Your feedback
        helps us grow and serve you better.
    </p>

    <form method="POST">
        <input type="text" name="name" placeholder="Your name" required>

        <div class="rating-container">
            <label class="rating-label" for="rating">Rate us:</label>
            <div class="star-rating" id="star-rating">
                <span class="star" data-rating="1">★</span>
                <span class="star" data-rating="2">★</span>
                <span class="star" data-rating="3">★</span>
                <span class="star" data-rating="4">★</span>
                <span class="star" data-rating="5">★</span>
            </div>
            <span class="rating-text" id="rating-text">Click to rate</span>
            <input type="hidden" name="rating" id="rating" value="" required>
        </div>

        <textarea name="review" placeholder="Your review..." rows="5" required></textarea>
        <button type="submit">Submit</button>
    </form>

    <div class="reviews">
        <h2>Customer Reviews</h2>
        <?php
    $sql = "SELECT name, review, rating, created_at FROM reviews ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='review-item'>";
            echo "<strong>" . htmlspecialchars($row['name']) . "</strong><br>";
            echo "<small>" . $row['created_at'] . "</small><br>";

            echo "<div class='stars'>";
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $row['rating'] ? "★" : "☆";
            }
            echo " (" . $row['rating'] . "/5)";
            echo "</div>";

            echo "<p>" . nl2br(htmlspecialchars($row['review'])) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No reviews yet!</p>";
    }
    ?>
    </div>

    <script>
    $(document).ready(function() {
        const stars = $('.star');
        const ratingInput = $('#rating');
        const ratingText = $('#rating-text');
        const ratingMessages = {
            1: 'Very Bad',
            2: 'Bad',
            3: 'Okay',
            4: 'Good',
            5: 'Excellent'
        };

        // Show toast notification if there's a message
        <?php if (!empty($toast_message)): ?>
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

        Toast.fire({
            icon: '<?php echo $toast_type; ?>',
            title: '<?php echo addslashes($toast_message); ?>'
        });
        <?php endif; ?>

        stars.on('click', function() {
            const rating = $(this).data('rating');
            ratingInput.val(rating);
            updateStars(rating);
            ratingText.text(ratingMessages[rating]);
        });

        stars.on('mouseenter', function() {
            highlightStars($(this).data('rating'));
        });

        $('.star-rating').on('mouseleave', function() {
            const currentRating = ratingInput.val();
            if (currentRating) {
                updateStars(currentRating);
            } else {
                resetStars();
            }
        });

        function updateStars(rating) {
            stars.each(function() {
                $(this).toggleClass('active', $(this).data('rating') <= rating).removeClass('hover');
            });
        }

        function highlightStars(rating) {
            stars.each(function() {
                $(this).toggleClass('hover', $(this).data('rating') <= rating);
            });
        }

        function resetStars() {
            stars.removeClass('active hover');
        }

        $('form').on('submit', function(e) {
            if (!ratingInput.val()) {
                e.preventDefault();

                // SweetAlert error toast for missing rating
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'warning',
                    title: 'Please select a rating before submitting.'
                });

                return false;
            }
        });
    });
    </script>

</body>

</html>