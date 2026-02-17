<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

include 'includes/csrf.php';
if (!verify_csrf_token()) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

// Check if user already liked this post (using session)
if (!isset($_SESSION['liked_posts'])) {
    $_SESSION['liked_posts'] = [];
}

if (in_array($post_id, $_SESSION['liked_posts'])) {
    // Get current like count
    $stmt = $conn->prepare("SELECT post_likes FROM post_table WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => false, 
        'already_liked' => true, 
        'likes' => $post['post_likes']
    ]);
    exit;
}

// Increment like count
$stmt = $conn->prepare("UPDATE post_table SET post_likes = post_likes + 1 WHERE post_id = ?");
$stmt->bind_param("i", $post_id);

if ($stmt->execute()) {
    // Mark as liked in session
    $_SESSION['liked_posts'][] = $post_id;
    
    // Get new like count
    $stmt->close();
    $stmt = $conn->prepare("SELECT post_likes FROM post_table WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'likes' => $post['post_likes']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
