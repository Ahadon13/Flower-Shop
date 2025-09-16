<?php 
include '../includes/db.php'; 
include 'header.php'; 

$is_logged_in = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
if (!$is_logged_in) {
    header("Location: ../login.php");
    exit;
}

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

/* Rating Distribution Styles */
.rating-overview {
    max-width: 800px;
    margin: 2em auto 1em;
    background: #ffffff;
    padding: 2em;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.overview-title {
    text-align: center;
    color: #7D0A0A;
    font-size: 1.5em;
    margin-bottom: 1.5em;
    font-weight: 600;
}

.rating-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 1em;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2em;
}

.overall-rating {
    text-align: center;
    flex: 1;
    min-width: 200px;
}

.average-score {
    font-size: 3em;
    font-weight: bold;
    color: #7D0A0A;
    line-height: 1;
}

.average-stars {
    color: #FFD700;
    font-size: 1.5em;
    margin: 0.5em 0;
}

.total-reviews {
    color: #666;
    font-size: 1em;
}

.rating-breakdown {
    flex: 2;
    min-width: 300px;
}

.rating-row {
    display: flex;
    align-items: center;
    margin-bottom: 0.8em;
    gap: 1em;
}

.rating-label-dist {
    display: flex;
    align-items: center;
    gap: 0.5em;
    min-width: 80px;
    font-weight: 500;
    color: #333;
}

.rating-stars-dist {
    color: #FFD700;
    font-size: 1.1em;
}

.progress-bar-container {
    flex: 1;
    height: 20px;
    background-color: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #FFD700, #FFA500);
    border-radius: 10px;
    transition: width 0.8s ease;
    position: relative;
}

.progress-bar.empty {
    background: #e0e0e0;
}

.rating-count {
    min-width: 60px;
    text-align: right;
    font-weight: 500;
    color: #666;
}

.percentage {
    font-size: 0.9em;
    color: #888;
}

.filter-container {
    max-width: 800px;
    margin: 0 auto 1em;
    background: #ffffff;
    padding: 1.5em;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    text-align: center;
}

.filter-title {
    color: #7D0A0A;
    font-size: 1.3em;
    margin-bottom: 1em;
    font-weight: 600;
}

.filter-stars {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    border: 2px solid #e2e2e2;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafafa;
    font-size: 14px;
    font-weight: 500;
}

.filter-option:hover {
    border-color: #FFD700;
    background: #fff9e6;
}

.filter-option.active {
    border-color: #FFD700;
    background: #fff3cd;
    color: #7D0A0A;
}

.filter-option .filter-stars-display {
    color: #FFD700;
    font-size: 16px;
}

.clear-filter {
    background: #f8f9fa;
    color: #666;
}

.clear-filter.active {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
}

@media (max-width: 768px) {
    .rating-stats {
        flex-direction: column;
        gap: 2em;
    }

    .rating-row {
        flex-wrap: wrap;
        gap: 0.5em;
    }

    .rating-label-dist {
        min-width: 60px;
    }

    .progress-bar-container {
        min-width: 200px;
    }
}
</style>

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

<?php
// Get rating distribution data
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$total_reviews = 0;
$total_rating_sum = 0;

$distribution_sql = "SELECT rating, COUNT(*) as count FROM reviews GROUP BY rating";
$distribution_result = $conn->query($distribution_sql);

if ($distribution_result) {
    while ($row = $distribution_result->fetch_assoc()) {
        $rating_counts[$row['rating']] = $row['count'];
        $total_reviews += $row['count'];
        $total_rating_sum += ($row['rating'] * $row['count']);
    }
}

$average_rating = $total_reviews > 0 ? round($total_rating_sum / $total_reviews, 1) : 0;

$rating_filter = isset($_GET['filter_rating']) ? (int)$_GET['filter_rating'] : 0;
?>

<!-- Rating Overview Section -->
<div class="rating-overview">
    <h3 class="overview-title">Rating Overview</h3>

    <?php if ($total_reviews > 0): ?>
    <div class="rating-stats">
        <div class="overall-rating">
            <div class="average-score"><?php echo $average_rating; ?></div>
            <div class="average-stars">
                <?php
                $full_stars = floor($average_rating);
                $half_star = ($average_rating - $full_stars) >= 0.5;
                
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $full_stars) {
                        echo "★";
                    } elseif ($i == $full_stars + 1 && $half_star) {
                        echo "☆"; // You could use a half-star character here if available
                    } else {
                        echo "☆";
                    }
                }
                ?>
            </div>
            <div class="total-reviews">Based on <?php echo $total_reviews; ?>
                review<?php echo $total_reviews != 1 ? 's' : ''; ?></div>
        </div>

        <div class="rating-breakdown">
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <?php 
            $count = $rating_counts[$i];
            $percentage = $total_reviews > 0 ? round(($count / $total_reviews) * 100, 1) : 0;
            ?>
            <div class="rating-row">
                <div class="rating-label-dist">
                    <span><?php echo $i; ?></span>
                    <span class="rating-stars-dist">★</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar <?php echo $count == 0 ? 'empty' : ''; ?>"
                        style="width: <?php echo $percentage; ?>%"></div>
                </div>
                <div class="rating-count">
                    <?php echo $count; ?> <span class="percentage">(<?php echo $percentage; ?>%)</span>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php else: ?>
    <div style="text-align: center; color: #666; padding: 2em;">
        <p>No reviews yet! Be the first to leave a review.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Filter Section -->
<div class="filter-container">
    <div class="filter-title">Filter Reviews by Rating</div>
    <div class="filter-stars">
        <div class="filter-option clear-filter <?php echo $rating_filter == 0 ? 'active' : ''; ?>"
            onclick="filterReviews(0)">
            All Reviews
        </div>

        <?php for ($i = 5; $i >= 1; $i--): ?>
        <div class="filter-option <?php echo $rating_filter == $i ? 'active' : ''; ?>"
            onclick="filterReviews(<?php echo $i; ?>)">
            <span class="filter-stars-display">
                <?php 
                for ($j = 1; $j <= $i; $j++) {
                    echo "★";
                }
                for ($j = $i + 1; $j <= 5; $j++) {
                    echo "☆";
                }
                ?>
            </span>
            <span><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?> (<?php echo $rating_counts[$i]; ?>)</span>
        </div>
        <?php endfor; ?>
    </div>
</div>

<?php
$sql = "SELECT name, review, rating, created_at FROM reviews";
if ($rating_filter > 0) {
    $sql .= " WHERE rating = " . $rating_filter;
}
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

// Get filtered review count
$filtered_count = 0;
if ($rating_filter > 0) {
    $filtered_count = $rating_counts[$rating_filter];
} else {
    $filtered_count = $total_reviews;
}
?>

<!-- Reviews Section -->
<div class="reviews">
    <h2>Customer Reviews
        <?php if ($rating_filter > 0): ?>
        (<?php echo $rating_filter; ?> Star<?php echo $rating_filter > 1 ? 's' : ''; ?> - <?php echo $filtered_count; ?>
        review<?php echo $filtered_count != 1 ? 's' : ''; ?>)
        <?php else: ?>
        (<?php echo $filtered_count; ?> total review<?php echo $filtered_count != 1 ? 's' : ''; ?>)
        <?php endif; ?>
    </h2>

    <?php
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
        if ($rating_filter > 0) {
            echo "<p>No " . $rating_filter . " star reviews yet!</p>";
        } else {
            echo "<p>No reviews yet!</p>";
        }
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

    // Animate progress bars on page load
    setTimeout(function() {
        $('.progress-bar').each(function() {
            const width = $(this).css('width');
            $(this).css('width', '0%').animate({
                width: width
            }, 1000);
        });
    }, 500);
});

// Filter function
function filterReviews(rating) {
    const currentUrl = new URL(window.location.href);

    if (rating === 0) {
        currentUrl.searchParams.delete('filter_rating');
    } else {
        currentUrl.searchParams.set('filter_rating', rating);
    }

    window.location.href = currentUrl.toString();
}
</script>
<?php include 'footer.php'; ?>