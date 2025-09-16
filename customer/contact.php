<?php 
include '../includes/db.php'; 
include 'header.php'; 

$toast_message = '';
$toast_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = htmlspecialchars($_POST['name']);
  $email = htmlspecialchars($_POST['email']);
  $message = htmlspecialchars($_POST['message']);

  // Basic validation
  if (!empty($name) && !empty($email) && !empty($message)) {
    // Prepared statement for safety
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
      $toast_message = "Thank you, $name! We have received your message.";
      $toast_type = 'success';
    } else {
      $toast_message = "Error: " . $stmt->error;
      $toast_type = 'error';
    }

    $stmt->close();
  } else {
    $toast_message = "Please fill out all fields correctly.";
    $toast_type = 'error';
  }
}
?>
<style>
.contact-container {
    background: #fff;
    /* white background */
    color: #333;
    /* dark text */
    padding: 40px;
    border-radius: 10px;
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
    display: flex;
    flex-direction: row;
    gap: 40px;
    align-items: flex-start;

    /* New styling */
    border-top: 5px solid #f1c40f;
    /* yellow top border */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    /* subtle shadow */
}

.contact-form {
    background: #fff;
    /* white background */
    color: #333;
    /* dark text */
    padding: 40px;
    border-radius: 10px;
    width: 90%;
    max-width: 800px;
    margin: 20px auto;

    /* New styling */
    border-top: 5px solid #f1c40f;
    /* yellow top border */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    /* subtle shadow */
}

.contact-info,
.contact-map {
    flex: 1;
    min-width: 0;
}

.contact-info h2 {
    font-size: 30px;
    margin-bottom: 15px;
}

.info-block {
    display: flex;
    align-items: flex-start;
    margin-bottom: 60px;
}

.info-block i {
    font-size: 30px;
    margin-right: 15px;
    background-color: white;
    color: black;
    border-radius: 50%;
    padding: 10px;
}

.info-text {
    text-align: left;
    font-size: 30px;
}

.info-text p {
    margin: 0;
    font-size: 30px;
}

.contact-map iframe {
    width: 100%;
    height: 100%;
    min-height: 500px;
    border: 0;
    border-radius: 10px;
}

.contact-form h2 {
    font-size: 30px;
    margin-bottom: 15px;
}

.contact-form p.description {
    font-size: 26px;
    margin-bottom: 30px;
    line-height: 1.6;
    color: gray;
    text-align: justify;
}

form {
    text-align: left;
}

form input,
form textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid gray;
    transition: border-color 0.3s ease;
}

form input:focus,
form textarea:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
}

form button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    margin-top: 10px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #45a049;
}

@media (max-width: 768px) {
    .contact-container {
        flex-direction: column;
        padding: 20px;
    }

    .contact-map iframe {
        height: 300px;
    }

    .contact-form {
        padding: 20px;
    }
}
</style>
<!-- Contact Info and Map Section -->
<div class="contact-container">
    <!-- Left: Contact Info -->
    <div class="contact-info">
        <div class="info-block">
            <i class="fa fa-map-marker"></i>
            <div class="info-text">
                <strong>Address</strong>
                <p>160 J.P Rizal St. Bry. 2 Calamba City Laguna</p>
            </div>
        </div>
        <div class="info-block">
            <i class="fa fa-phone"></i>
            <div class="info-text">
                <strong>Phone</strong>
                <p>0929-306-0309</p>
            </div>
        </div>
        <div class="info-block">
            <i class="fa fa-envelope"></i>
            <div class="info-text">
                <strong>Email</strong>
                <p>bulaklakannijay@gmail.com</p>
            </div>
        </div>
    </div>

    <!-- Right: Google Map -->
    <div class="contact-map">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3867.708975369268!2d121.1595881!3d14.2118112!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd63d2d9969869%3A0x25cb0427a7ed0d1!2sBULAKLAKAN%20ni%20Jay!5e0!3m2!1sen!2sph!4v1743904928814!5m2!1sen!2sph"
            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

<!-- Contact Form Section -->
<div class="contact-form">
    <h2>Contact Us</h2>
    <p class="description">
        Need assistance or want to place an order? Contact us now and we'll get back to you as soon as we can!
    </p>

    <form method="POST" action="" id="contactForm">
        <div style="margin-bottom: 15px;">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required>
        </div>
        <div style="margin-bottom: 15px;">
            <label for="message">Message:</label><br>
            <textarea id="message" name="message" rows="5" required></textarea>
        </div>
        <button type="submit">Send Message</button>
    </form>
</div>

<script>
$(document).ready(function() {
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

    // Form validation with SweetAlert toast
    $('#contactForm').on('submit', function(e) {
        const name = $('#name').val().trim();
        const email = $('#email').val().trim();
        const message = $('#message').val().trim();

        if (!name || !email || !message) {
            e.preventDefault();

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
                title: 'Please fill out all fields.'
            });

            return false;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();

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
                icon: 'error',
                title: 'Please enter a valid email address.'
            });

            return false;
        }
    });

    // Clear form after successful submission
    <?php if (!empty($toast_message) && $toast_type === 'success'): ?>
    $('#contactForm')[0].reset();
    <?php endif; ?>
});
</script>

<?php include 'footer.php'; ?>