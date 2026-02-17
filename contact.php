<?php
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/csrf.php';

$success_msg = "";
$error_msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token()) {
        $error_msg = "Invalid session. Please refresh and try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $error_msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            // Send email
            $to = "contact@qiiramagazine.com.ng";
            $subject = "New Contact Message from " . $name;
            $email_content = "Name: $name\n";
            $email_content .= "Email: $email\n\n";
            $email_content .= "Message:\n$message\n";
            $headers = "From: no-reply@qiiramagazine.com.ng\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            mail($to, $subject, $email_content, $headers);

            $success_msg = "Thank you for your message! We will get back to you soon.";
            // Clear form values on success
            $name = $email = $message = "";
        } else {
            $error_msg = "Sorry, there was an error sending your message. Please try again.";
        }
        $stmt->close();
    }
}
}
?>

<!-- Hero Section with Contact Form -->
<div class="hero-slide" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg5.JPG');">
    <div class="d-flex align-items-center justify-content-center h-100 hero-content">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-lg border-0" style="border-radius: 15px; background: rgba(255,255,255,0.7); backdrop-filter: blur(5px);">
                    <div class="card-body p-5">
                        <h2 class="mb-4"><i class="fas fa-paper-plane"></i> Send us a Message</h2>
                        
                        <?php if($success_msg): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($error_msg): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php csrf_field(); ?>
                            <div class="form-group">
                                <label for="name">Your Name</label>
                                <input type="text" 
                                       id="name"
                                       name="name" 
                                       class="form-control form-control-lg" 
                                       placeholder="Enter your full name"
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                       style="border-radius: 10px;"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" 
                                       id="email"
                                       name="email" 
                                       class="form-control form-control-lg" 
                                       placeholder="Enter your email address"
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                       style="border-radius: 10px;"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Your Message</label>
                                <textarea id="message"
                                          name="message" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Write your message here..."
                                          style="border-radius: 10px;"
                                          required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-dark btn-lg btn-block" style="border-radius: 10px;">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info Cards -->
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 mb-3" style="border-radius: 15px; background: rgba(0,0,0,0.8);">
                    <div class="card-body p-4 text-white">
                        <h4><i class="fas fa-map-marker-alt" style="color: #d4af37;"></i> Our Location</h4>
                        <p class="mb-0 text-muted">Qiira Magazine</p>
                    </div>
                </div>
                
                <div class="card shadow-lg border-0 mb-3" style="border-radius: 15px; background: rgba(0,0,0,0.8);">
                    <div class="card-body p-4 text-white">
                        <h4><i class="fas fa-phone" style="color: #d4af37;"></i> Phone</h4>
                        <p class="mb-0 text-muted">+234 9029725892</p>
                    </div>
                </div>
                
                <div class="card shadow-lg border-0 mb-3" style="border-radius: 15px; background: rgba(0,0,0,0.8);">
                    <div class="card-body p-4 text-white">
                        <h4><i class="fas fa-envelope" style="color: #d4af37;"></i> Email</h4>
                        <p class="mb-0 text-muted">advertising@qiiramagazine.com.ng</p>
                    </div>
                </div>
                
                <div class="card shadow-lg border-0" style="border-radius: 15px; background: rgba(0,0,0,0.8);">
                    <div class="card-body p-4 text-white">
                        <h4><i class="fas fa-clock" style="color: #d4af37;"></i> Business Hours</h4>
                        <p class="mb-0 text-muted">Monday - Friday: 8AM - 4PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
