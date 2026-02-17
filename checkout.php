<?php
// checkout.php - Initialize Paystack payment for magazine purchase
include 'db.php';
include 'paystack_config.php';
include 'includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$magazine_id = intval($_GET['id'] ?? 0);
$error = '';

if ($magazine_id <= 0) {
    header("Location: magazines.php");
    exit;
}

// Fetch magazine details
$stmt = $conn->prepare("SELECT * FROM magazines WHERE id = ?");
$stmt->bind_param("i", $magazine_id);
$stmt->execute();
$magazine = $stmt->get_result()->fetch_assoc();

if (!$magazine) {
    header("Location: magazines.php");
    exit;
}

// Check if already purchased
$check = $conn->prepare("SELECT id FROM purchases WHERE user_id = ? AND magazine_id = ?");
$check->bind_param("ii", $_SESSION['user_id'], $magazine_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header("Location: my_purchases.php");
    exit;
}

// Handle payment initialization
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Invalid session. Please refresh and try again.";
    } else {
        $email = $_SESSION['user_email'];
    $amount = $magazine['price'] * 100; // Convert to kobo
    $reference = 'QM_' . time() . '_' . $_SESSION['user_id'] . '_' . $magazine_id;
    
    $callback_url = SITE_BASE_URL . '/payment_callback.php';
    
    $data = [
        'email' => $email,
        'amount' => $amount,
        'reference' => $reference,
        'callback_url' => $callback_url,
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'magazine_id' => $magazine_id,
            'magazine_title' => $magazine['title']
        ]
    ];
    
    $response = paystack_request('/transaction/initialize', 'POST', $data);
    
    if ($response && $response['status'] === true) {
        // Redirect to Paystack payment page
        header("Location: " . $response['data']['authorization_url']);
        exit;
    } else {
        $error = $response['message'] ?? 'Failed to initialize payment. Please try again.';
    }
}
}

include 'includes/header.php';
?>

<div class="container py-5" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0" style="border-radius: 20px; background: #111; overflow: hidden;">
                <!-- Magazine Preview -->
                <?php if ($magazine['image']): ?>
                    <img src="<?php echo htmlspecialchars($magazine['image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($magazine['title']); ?>"
                         style="height: 250px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body p-4">
                    <h3 class="mb-2" style="color: #d4af37;"><?php echo htmlspecialchars($magazine['title']); ?></h3>
                    <?php if ($magazine['issue']): ?>
                        <p class="text-muted mb-3">Issue: <?php echo htmlspecialchars($magazine['issue']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($magazine['description']): ?>
                        <p class="text-white-50 mb-4"><?php echo htmlspecialchars(substr($magazine['description'], 0, 200)); ?>...</p>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <div class="bg-dark p-3 rounded mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white">Total Price:</span>
                            <span class="h4 mb-0" style="color: #d4af37;">₦<?php echo number_format($magazine['price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="mb-4 text-white-50 small">
                        <i class="fas fa-check-circle text-success mr-2"></i> Instant digital download<br>
                        <i class="fas fa-check-circle text-success mr-2"></i> 5 download attempts included<br>
                        <i class="fas fa-check-circle text-success mr-2"></i> Secure payment via Paystack
                    </div>
                    
                    <form method="POST" action="">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-lg w-100" 
                                style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; border-radius: 10px; font-weight: 600;">
                            <i class="fas fa-lock mr-2"></i> Pay ₦<?php echo number_format($magazine['price'], 2); ?>
                        </button>
                    </form>

                    
                    <p class="text-center text-muted mt-3 mb-0">
                        <a href="magazines.php" class="text-muted"><i class="fas fa-arrow-left mr-1"></i> Back to Magazines</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
