<?php
// register.php - User Registration
include 'db.php';
include 'includes/csrf.php';
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            // Create account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $hashed_password);
            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<div class="container py-5" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="border-radius: 20px; background: #111;">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4" style="color: #d4af37;">Create Account</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br><a href="login.php" class="text-success fw-bold">Click here to login</a>
                        </div>
                    <?php else: ?>
                    
                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <div class="form-group mb-3">
                            <label class="text-white mb-2">Full Name</label>
                            <input type="text" name="full_name" class="form-control form-control-lg" 
                                   style="background: #222; border: 1px solid #333; color: #fff; border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="text-white mb-2">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg" 
                                   style="background: #222; border: 1px solid #333; color: #fff; border-radius: 10px;"
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="text-white mb-2">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg" 
                                   style="background: #222; border: 1px solid #333; color: #fff; border-radius: 10px;"
                                   required minlength="6">
                        </div>
                        
                        <div class="form-group mb-4">
                            <label class="text-white mb-2">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control form-control-lg" 
                                   style="background: #222; border: 1px solid #333; color: #fff; border-radius: 10px;"
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-lg w-100 mb-3" 
                                style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </button>
                    </form>
                    
                    <?php endif; ?>
                    
                    <p class="text-center text-muted mt-3 mb-0">
                        Already have an account? <a href="login.php" style="color: #d4af37;">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
