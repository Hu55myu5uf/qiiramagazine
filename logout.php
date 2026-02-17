<?php
session_start();
// Store role to decide where to redirect after logout
$role = $_SESSION['role'] ?? '';
session_destroy();

if ($role === 'editor') {
    header("Location: admin/editor_login.php");
} else {
    header("Location: admin/login.php");
}
exit();
?>
