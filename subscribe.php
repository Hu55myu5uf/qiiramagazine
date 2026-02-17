<?php
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'includes/csrf.php';
    if (!verify_csrf_token()) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
        exit;
    }
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
    if (!$checkStmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You are already subscribed.']);
    } else {
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        if (!$stmt) {
             echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error]);
             exit;
        }
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            // Send notification email
            $to = "advertising@qiiramagazine.com.ng";
            $subject = "New Newsletter Subscriber";
            $message = "A new user has subscribed to the newsletter: " . $email;
            $headers = "From: no-reply@qiiramagazine.com.ng\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            mail($to, $subject, $message, $headers);

            echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        }
        $stmt->close();
    }
    $checkStmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
$conn->close();
?>
