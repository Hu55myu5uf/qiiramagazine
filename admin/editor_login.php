<?php
include __DIR__ . '/../db.php';
include __DIR__ . '/../includes/csrf.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_msg = "";

// If already logged in as editor, redirect
if(isset($_SESSION['role']) && $_SESSION['role'] == 'editor') {
    header("Location: editor_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!verify_csrf_token()) {
        $error_msg = "Invalid session. Please refresh and try again.";
    } elseif (empty($username) || empty($password)) {
        $error_msg = "All fields are required.";
    } else {
        // Use prepared statements
        $stmt = $conn->prepare("SELECT * FROM editors_table WHERE username = ?");
        if (!$stmt) {
            $error_msg = "Database error: " . htmlspecialchars($conn->error);
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Check if account is suspended
                if (isset($row['is_suspended']) && $row['is_suspended'] == 1) {
                    $error_msg = "Your account has been suspended. Please contact the administrator.";
                // Verify hashed password
                } elseif (password_verify($password, $row['editor_password'])) {
                    $_SESSION['editor_username'] = $row['username'];
                    $_SESSION['editor_name'] = $row['editor_name'];
                    $_SESSION['role'] = 'editor';
                    
                    // Regenerate session ID
                    session_regenerate_id(true);

                    if ($row['is_first_login']) {
                        header("Location: update_password.php");
                    } else {
                        header("Location: editor_dashboard.php");
                    }
                    exit();
                } else {
                    $error_msg = "Invalid Username or Password";
                }
            } else {
                $error_msg = "Invalid Username or Password";
            }
            $stmt->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Editor Login Hero Section with Slideshow Background -->
<div class="login-hero-container">
    
    <!-- Background Carousel -->
    <div id="editorLoginCarousel" class="carousel slide carousel-fade position-absolute w-100 h-100" data-ride="carousel" style="top: 0; left: 0; z-index: 1;">
        <div class="carousel-inner h-100">
            <!-- Slide 1 -->
            <div class="carousel-item active h-100" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/qira/bg5.JPG'); background-size: cover; background-position: center center;"></div>
            <!-- Slide 2 -->
            <div class="carousel-item h-100" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/qira/bg6.JPG'); background-size: cover; background-position: center center;"></div>
            <!-- Slide 3 -->
            <div class="carousel-item h-100" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/qira/bg5.JPG'); background-size: cover; background-position: center center;"></div>
            <!-- Slide 4 -->
            <div class="carousel-item h-100" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/qira/bg6.JPG'); background-size: cover; background-position: center center;"></div>
            <!-- Slide 5 -->
            <div class="carousel-item h-100" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/qira/bg5.JPG'); background-size: cover; background-position: center center;"></div>
        </div>
        <a class="carousel-control-prev" href="#editorLoginCarousel" role="button" data-slide="prev" style="z-index: 3;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#editorLoginCarousel" role="button" data-slide="next" style="z-index: 3;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <!-- Login Form Overlay -->
    <div class="container d-flex align-items-center justify-content-center" style="position: relative; z-index: 2; min-height: 100vh;">
        <div class="row justify-content-center w-100 mx-0">
            <div class="col-xl-4 col-lg-5 col-md-7 col-sm-10 col-12">
                <div class="card shadow-lg border-0" style="border-radius: 20px; background: rgba(255,255,255,0.95);">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div style="width: 80px; height: 80px; background: #d4af37; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                                <i class="fas fa-pen-fancy fa-2x text-white"></i>
                            </div>
                            <h3 class="font-weight-bold">Editor Login</h3>
                            <p class="text-muted">Access your editor dashboard</p>
                        </div>
                        
                        <?php if($error_msg): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php csrf_field(); ?>
                            <div class="form-group">
                                <label class="font-weight-bold">Username</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-user text-muted"></i></span>
                                    </div>
                                    <input type="text" name="username" class="form-control form-control-lg border-left-0" placeholder="Enter your username" style="border-radius: 0 10px 10px 0;" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-key text-muted"></i></span>
                                    </div>
                                    <input type="password" name="password" id="password" class="form-control form-control-lg border-left-0 border-right-0" placeholder="Enter password" style="border-radius: 0;" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white border-left-0" style="border-radius: 0 10px 10px 0; cursor: pointer;" onclick="togglePassword('password', 'toggleIcon')">
                                            <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-lg btn-block mt-4" style="background: #d4af37; color: #000; border-radius: 10px; transition: all 0.3s;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Are you an Admin? <a href="login.php" class="text-dark font-weight-bold">Login here</a></p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="../index.php" class="text-muted font-weight-bold" style="text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
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



