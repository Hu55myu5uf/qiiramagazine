<?php
// payment_callback.php - Handle Paystack payment callback
include 'db.php';
include 'paystack_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reference = $_GET['reference'] ?? '';
$error = '';
$success = false;
$magazine = null;

if (empty($reference)) {
    $error = 'Invalid payment reference.';
} else {
    // Verify transaction with Paystack
    $response = paystack_request('/transaction/verify/' . rawurlencode($reference));
    
    if ($response && $response['status'] === true && $response['data']['status'] === 'success') {
        $data = $response['data'];
        $metadata = $data['metadata'] ?? [];
        
        $user_id = intval($metadata['user_id'] ?? 0);
        $magazine_id = intval($metadata['magazine_id'] ?? 0);
        $amount = $data['amount'] / 100; // Convert from kobo to Naira
        
        // Verify user matches session
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
            $error = 'Session mismatch. Please contact support.';
        } elseif ($magazine_id <= 0) {
            $error = 'Invalid magazine in payment data.';
        } else {
            // Check if purchase already exists (prevent duplicates)
            $check = $conn->prepare("SELECT id FROM purchases WHERE paystack_reference = ?");
            $check->bind_param("s", $reference);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                // Already processed
                $success = true;
            } else {
                // Record the purchase
                $stmt = $conn->prepare("INSERT INTO purchases (user_id, magazine_id, paystack_reference, amount, downloads_remaining) VALUES (?, ?, ?, ?, 5)");
                $stmt->bind_param("iisd", $user_id, $magazine_id, $reference, $amount);
                
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = 'Failed to record purchase. Please contact support with reference: ' . $reference;
                }
                $stmt->close();
            }
            $check->close();
            
            // Fetch magazine details for display
            if ($success) {
                $mag_stmt = $conn->prepare("SELECT * FROM magazines WHERE id = ?");
                $mag_stmt->bind_param("i", $magazine_id);
                $mag_stmt->execute();
                $magazine = $mag_stmt->get_result()->fetch_assoc();
                $mag_stmt->close();
            }
        }
    } else {
        $error = 'Payment verification failed: ' . ($response['message'] ?? 'Unknown error');
    }
}

include 'includes/header.php';
?>

<div class="container py-5" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 text-center" style="border-radius: 20px; background: #111;">
                <div class="card-body p-5">
                    <?php if ($success): ?>
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h2 class="mb-3" style="color: #d4af37;">Payment Successful!</h2>
                        <p class="text-white-50 mb-4">
                            Thank you for your purchase. Your magazine is now available for download.
                        </p>
                        
                        <?php if ($magazine): ?>
                            <div class="bg-dark p-3 rounded mb-4">
                                <h5 class="text-white mb-1"><?php echo htmlspecialchars($magazine['title']); ?></h5>
                                <p class="text-muted small mb-0">5 downloads available</p>
                            </div>
                        <?php endif; ?>
                        
                        <a href="my_purchases.php" class="btn btn-lg" 
                           style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; border-radius: 10px;">
                            <i class="fas fa-download mr-2"></i> Go to My Purchases
                        </a>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="fas fa-times-circle fa-5x text-danger"></i>
                        </div>
                        <h2 class="mb-3 text-danger">Payment Failed</h2>
                        <p class="text-white-50 mb-4">
                            <?php echo htmlspecialchars($error); ?>
                        </p>
                        <a href="magazines.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Magazines
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
