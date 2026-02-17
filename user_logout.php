<?php
// user_logout.php - User Logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear user session data only (preserve admin session if needed)
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);

// If no admin session, destroy entire session
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['role'])) {
    session_destroy();
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    unset($_SESSION['role']);
}

header("Location: index.php");
exit;
?>
