<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FAQ Icon</title>
  <style>
    body {
      height: 100vh;
      margin: 0;
      background-color: #f9f9f9;
      font-family: Arial, sans-serif;
      position: relative;
    }

    .faq-icon {
      position: fixed;
      top: 20px;
      right: 20px;
      font-size: 15px;
      width: 40px;
      height: 40px;
      background-color: #FFF574; 
      color: black;
      border-radius: 15px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      z-index: 1000;
    }

    .faq-icon:hover {
      background-color: yellow;
      color: black;
    }
  </style>
</head>
<body>

<a href="FAQ.php" class="faq-icon" title="Go to FAQ Page">FAQ</a>

</body>
</html>
