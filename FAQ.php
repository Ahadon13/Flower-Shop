<?php
// Optional: include header if needed
// include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FAQ</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: white;
      margin: 0;
      padding: 2em;
    }

    a.back-link {
      display: inline-block;
      margin-bottom: 1em;
      color: #C96868;
      text-decoration: none;
      font-weight: bold;
    }

    h1 {
      text-align: center;
      color: #C96868;
      margin-bottom: 1em;
    }

    .faq-container {
      max-width: 800px;
      margin: 0 auto;
    }

    .faq-item {
      border-bottom: 1px solid #e0e0e0;
      padding: 1em 0;
    }

    .faq-question {
      font-weight: bold;
      cursor: pointer;
      color: green;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .faq-answer {
      display: none;
      padding-top: 0.5em;
      color: black;
    }

    .faq-question::after {
      content: '+';
      font-size: 1.2em;
    }

    .faq-item.active .faq-question::after {
      content: '−';
    }

    .faq-item.active .faq-answer {
      display: block;
    }
  </style>
</head>
<body>

  <!-- Back link to login.php -->
  <a href="login.php" class="back-link">← Back to Login</a>

  <h1>Frequently Asked Questions (FAQ)</h1>
  <div class="faq-container">

    <div class="faq-item">
      <div class="faq-question">How do I register on the system?</div>
      <div class="faq-answer">
        To register, go to our homepage and click the <strong>“Sign Up”</strong> button. Fill out the form with your name, email, and password. Once done, click <strong>Register Now!</strong> That’s it!
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">How do I log in to my account?</div>
      <div class="faq-answer">
        Just click the <strong>“Log In”</strong> button at the top of the page, enter your email and password, then click <strong>“Login”</strong>. You’ll be directed to your dashboard.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">What types of events do you manage?</div>
      <div class="faq-answer">
        We manage weddings, birthdays, anniversaries, funerals, and other floral-themed celebrations.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">How can I book an event?</div>
      <div class="faq-answer">
        You can use our booking form, message us on social media, or call us directly. Just give us your event details.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">Can I customize floral arrangements and themes?</div>
      <div class="faq-answer">
        Yes! We offer full customization to make sure your event reflects your style and preferences.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">Do you accept last-minute bookings?</div>
      <div class="faq-answer">
        We try our best to help with urgent bookings. Just contact us right away so we can check availability.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">How can I contact Bulaklakan ni Jay?</div>
      <div class="faq-answer">
        You can email us, call, or message us on social media. Visit our Contact Page for full details.
      </div>
    </div>

  </div>

  <script>
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
      item.querySelector('.faq-question').addEventListener('click', () => {
        item.classList.toggle('active');
      });
    });
  </script>

</body>
</html>

<?php
// Optional: include footer if needed
// include 'includes/footer.php';
?>
