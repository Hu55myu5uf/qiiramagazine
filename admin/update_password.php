<?php
include __DIR__ . '/../db.php';
include __DIR__ . '/../includes/csrf.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'editor') {
    header("Location: editor_login.php");
    exit();
}

$editor_username = $_SESSION['editor_username'];
$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (!verify_csrf_token()) {
        $error_msg = "Invalid session. Please refresh and try again.";
    } elseif (empty($new_password) || empty($confirm_password)) {
        $error_msg = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE editors_table SET editor_password = ?, is_first_login = 0 WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $editor_username);
        
        if ($stmt->execute()) {
            $success_msg = "Password updated successfully. Redirecting...";
            echo "<script>setTimeout(function(){ window.location.href = 'editor_dashboard.php'; }, 2000);</script>";
        } else {
            $error_msg = "Error updating password. Please try again.";
        }
        $stmt->close();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="login-hero-container">
    <div id="passwordUpdateCarousel" class="carousel slide carousel-fade position-absolute w-100 h-100" data-ride="carousel" style="top: 0; left: 0; z-index: 1;">
        <div class="carousel-inner h-100">
            <div class="carousel-item active h-100" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/qira/bg5.JPG'); background-size: cover; background-position: center center;"></div>
        </div>
    </div>

    <div class="container d-flex align-items-center justify-content-center" style="position: relative; z-index: 2; min-height: 100vh;">
        <div class="row justify-content-center w-100 mx-0">
            <div class="col-xl-4 col-lg-5 col-md-7 col-sm-10 col-12">
                <div class="card shadow-lg border-0" style="border-radius: 20px; background: rgba(255,255,255,0.95);">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div style="width: 80px; height: 80px; background: #d4af37; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                                <i class="fas fa-lock fa-2x text-white"></i>
                            </div>
                            <h3 class="font-weight-bold">Update Password</h3>
                            <p class="text-muted">Please set a new password for your account</p>
                        </div>
                        
                        <?php if($error_msg): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                            </div>
                        <?php endif; ?>

                        <?php if($success_msg): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php csrf_field(); ?>
                            <div class="form-group">
                                <label class="font-weight-bold">New Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-key text-muted"></i></span>
                                    </div>
                                    <input type="password" name="new_password" id="new_password" class="form-control form-control-lg border-left-0 border-right-0" placeholder="Min 6 characters" style="border-radius: 0;" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white border-left-0" style="border-radius: 0 10px 10px 0; cursor: pointer;" onclick="togglePassword('new_password', 'toggleNew')">
                                            <i class="fas fa-eye text-muted" id="toggleNew"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Confirm New Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-check text-muted"></i></span>
                                    </div>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg border-left-0 border-right-0" placeholder="Confirm password" style="border-radius: 0;" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white border-left-0" style="border-radius: 0 10px 10px 0; cursor: pointer;" onclick="togglePassword('confirm_password', 'toggleConfirm')">
                                            <i class="fas fa-eye text-muted" id="toggleConfirm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-lg btn-block mt-4" style="background: #d4af37; color: #000; border-radius: 10px; transition: all 0.3s;">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
