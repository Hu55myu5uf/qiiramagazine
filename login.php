<?php
// login.php - User Login
include 'db.php';
include 'includes/csrf.php';

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: " . $redirect);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = 'user';
                
                header("Location: " . $redirect);
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="container py-5" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px; background: #111;">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4" style="color: #d4af37;">Welcome Back</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <div class="form-group mb-3">
                            <label class="text-white mb-2">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg" 
                                   style="background: #222; border: 1px solid #333; color: #fff; border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label class="text-white mb-2">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg" 
                                   style="background: #222; border: 1px solid #333; color: #fff; border-radius: 10px;"
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-lg w-100 mb-3" 
                                style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                    </form>
                    
                    <p class="text-center text-muted mt-3 mb-0">
                        Don't have an account? <a href="register.php" style="color: #d4af37;">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
