<?php
// my_purchases.php - View purchased magazines
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

// Fetch user's purchases
$stmt = $conn->prepare("
    SELECT p.*, m.title, m.issue, m.image, m.pdf_file 
    FROM purchases p 
    JOIN magazines m ON p.magazine_id = m.id 
    WHERE p.user_id = ? 
    ORDER BY p.purchased_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$purchases = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container py-5" style="margin-top: 100px;">
    <h2 class="mb-4" style="color: #d4af37;"><i class="fas fa-book-open mr-2"></i> My Purchased Magazines</h2>
    
    <?php if ($purchases->num_rows === 0): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">You haven't purchased any magazines yet.</h4>
            <a href="magazines.php" class="btn mt-3" style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000;">
                <i class="fas fa-book mr-2"></i> Browse Magazines
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php while ($purchase = $purchases->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow" style="background: #111; border: 1px solid rgba(212,175,55,0.2); border-radius: 15px; overflow: hidden;">
                        <?php if ($purchase['image']): ?>
                            <img src="<?php echo htmlspecialchars($purchase['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($purchase['title']); ?>" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title" style="color: #d4af37;"><?php echo htmlspecialchars($purchase['title']); ?></h5>
                            <?php if ($purchase['issue']): ?>
                                <p class="text-muted small mb-2">Issue: <?php echo htmlspecialchars($purchase['issue']); ?></p>
                            <?php endif; ?>
                            <p class="text-white-50 small mb-3">
                                <i class="fas fa-calendar-alt mr-1"></i> Purchased: <?php echo date('M j, Y', strtotime($purchase['purchased_at'])); ?>
                            </p>
                            
                            <?php if ($purchase['downloads_remaining'] > 0 && $purchase['pdf_file']): ?>
                                <a href="download.php?id=<?php echo $purchase['magazine_id']; ?>" class="btn btn-success w-100">
                                    <i class="fas fa-download mr-2"></i> Download (<?php echo $purchase['downloads_remaining']; ?> left)
                                </a>
                            <?php elseif ($purchase['downloads_remaining'] <= 0): ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-times-circle mr-2"></i> No Downloads Left
                                </button>
                            <?php else: ?>
                                <button class="btn btn-warning w-100" disabled>
                                    <i class="fas fa-clock mr-2"></i> PDF Coming Soon
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
