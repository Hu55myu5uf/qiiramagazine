<?php
// download.php - Handle magazine PDF downloads with limit
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=my_purchases.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$magazine_id = intval($_GET['id'] ?? 0);

if ($magazine_id <= 0) {
    die("Invalid magazine ID.");
}

// Check if user has purchased this magazine and has downloads remaining
$stmt = $conn->prepare("
    SELECT p.id, p.downloads_remaining, m.pdf_file, m.title 
    FROM purchases p 
    JOIN magazines m ON p.magazine_id = m.id 
    WHERE p.user_id = ? AND p.magazine_id = ?
");
$stmt->bind_param("ii", $user_id, $magazine_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("You have not purchased this magazine.");
}

$purchase = $result->fetch_assoc();

if ($purchase['downloads_remaining'] <= 0) {
    die("You have used all your download attempts for this magazine.");
}

if (empty($purchase['pdf_file'])) {
    die("PDF file is not available yet.");
}

$pdf_path = __DIR__ . '/' . $purchase['pdf_file'];

if (!file_exists($pdf_path)) {
    die("PDF file not found.");
}

// Decrement download count
$update = $conn->prepare("UPDATE purchases SET downloads_remaining = downloads_remaining - 1 WHERE id = ?");
$update->bind_param("i", $purchase['id']);
$update->execute();

// Serve the file
$filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $purchase['title']) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($pdf_path));
header('Cache-Control: no-cache, must-revalidate');

readfile($pdf_path);
exit;
?>
