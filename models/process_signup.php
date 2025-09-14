<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    
    if ($password !== $confirm) {
        $_SESSION['flash_message'] = "Passwords do not match.";
        $_SESSION['flash_type'] = "error";
        header("Location: ../signup.php");
        exit;
    }
    
    // Additional password validation
    if (strlen($password) < 6) {
        $_SESSION['flash_message'] = "Password should be at least 6 characters long.";
        $_SESSION['flash_type'] = "warning";
        header("Location: ../signup.php");
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_message'] = "Please enter a valid email address.";
        $_SESSION['flash_type'] = "error";
        header("Location: ../signup.php");
        exit;
    }
    
    // Validate name (not empty and reasonable length)
    if (empty($name) || strlen($name) < 2) {
        $_SESSION['flash_message'] = "Please enter a valid full name.";
        $_SESSION['flash_type'] = "error";
        header("Location: ../signup.php");
        exit;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $profileImage = null;
    
    // Handle profile image upload
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir  = "../uploads/profiles/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['profile']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['flash_message'] = "Please upload a valid image file (JPEG, PNG, GIF, or WebP).";
            $_SESSION['flash_type'] = "error";
            header("Location: ../signup.php");
            exit;
        }
        
        // Validate file size (max 5MB)
        if ($_FILES['profile']['size'] > 5 * 1024 * 1024) {
            $_SESSION['flash_message'] = "Profile image must be less than 5MB.";
            $_SESSION['flash_type'] = "error";
            header("Location: ../signup.php");
            exit;
        }
        
        $fileName   = time() . "_" . basename($_FILES['profile']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['profile']['tmp_name'], $targetPath)) {
            $profileImage = $fileName;
        } else {
            $_SESSION['flash_message'] = "Failed to upload profile image. Please try again.";
            $_SESSION['flash_type'] = "error";
            header("Location: ../signup.php");
            exit;
        }
    }
    
    try {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['flash_message'] = "This email is already registered. Please use a different email or try logging in.";
            $_SESSION['flash_type'] = "error";
            header("Location: ../signup.php");
            exit;
        }
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, profile_image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $profileImage);
        
        if ($stmt->execute()) {
            // ✅ Automatically log in user after sign-up
            $newUserId = $stmt->insert_id;
            $_SESSION['user_id']   = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_profile'] = $profileImage;
            $_SESSION['authenticated'] = true;
            
            // Set success message for dashboard
            $_SESSION['flash_message'] = "Welcome, " . htmlspecialchars($name) . "! Your account has been created successfully and you are now logged in.";
            $_SESSION['flash_type'] = "success";
            
            header("Location: ../customer/dashboard.php");
            exit;
        } else {
            $_SESSION['flash_message'] = "An error occurred while creating your account. Please try again.";
            $_SESSION['flash_type'] = "error";
            header("Location: ../signup.php");
            exit;
        }
        
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate entry error specifically
        if ($e->getCode() === 1062) {
            $_SESSION['flash_message'] = "This email is already registered. Please use a different email or try logging in.";
            $_SESSION['flash_type'] = "error";
        } else {
            $_SESSION['flash_message'] = "A database error occurred. Please try again later.";
            $_SESSION['flash_type'] = "error";
        }
        header("Location: ../signup.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "An unexpected error occurred. Please try again later.";
        $_SESSION['flash_type'] = "error";
        header("Location: ../signup.php");
        exit;
    }
    
} else {
    // Not a POST request
    $_SESSION['flash_message'] = "Invalid request method.";
    $_SESSION['flash_type'] = "error";
    header("Location: ../signup.php");
    exit;
}
?>