<?php
include __DIR__ . '/../db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control - Admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Require user_id parameter
$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    header("Location: manage_users.php");
    exit();
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

// Fetch purchases
$purchase_stmt = $conn->prepare("
    SELECT p.*, m.title AS magazine_title, m.issue, m.image, m.price AS magazine_price
    FROM purchases p
    JOIN magazines m ON p.magazine_id = m.id
    WHERE p.user_id = ?
    ORDER BY p.purchased_at DESC
");
$purchase_stmt->bind_param("i", $user_id);
$purchase_stmt->execute();
$purchases = $purchase_stmt->get_result();

// Calculate totals
$total_spent = 0;
$total_purchases = 0;
$purchase_rows = [];
while ($row = $purchases->fetch_assoc()) {
    $purchase_rows[] = $row;
    $total_spent += $row['amount'];
    $total_purchases++;
}
$purchase_stmt->close();

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <!-- Back Link -->
    <div class="mb-3">
        <a href="manage_users.php" class="btn btn-dark" style="border-radius: 10px;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Manage Users
        </a>
    </div>

    <!-- User Info Card -->
    <div class="card shadow-lg border-0 mb-4" style="border-radius: 15px; background: #111;">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-1 text-center">
                    <i class="fas fa-user-circle fa-3x" style="color: #d4af37;"></i>
                </div>
                <div class="col-md-5">
                    <h4 class="mb-1 text-white"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="mb-0 text-white-50"><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="col-md-3 text-center">
                    <small class="text-muted d-block">Total Purchases</small>
                    <h3 class="mb-0" style="color: #d4af37;"><?php echo $total_purchases; ?></h3>
                </div>
                <div class="col-md-3 text-center">
                    <small class="text-muted d-block">Total Spent</small>
                    <h3 class="mb-0 text-success">₦<?php echo number_format($total_spent, 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="card shadow-lg border-0" style="border-radius: 15px;">
        <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
            <h4 class="mb-0">
                <i class="fas fa-shopping-bag" style="color: #d4af37;"></i>
                Purchase History
            </h4>
        </div>
        <div class="card-body">
            <?php if (empty($purchase_rows)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">This user hasn't purchased any magazines yet.</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="datatable">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Magazine</th>
                                <th>Issue</th>
                                <th>Amount</th>
                                <th>Downloads Left</th>
                                <th>Reference</th>
                                <th>Purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchase_rows as $index => $purchase): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if ($purchase['image']): ?>
                                            <img src="../<?php echo htmlspecialchars($purchase['image']); ?>" 
                                                 alt="" style="width: 40px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 8px; vertical-align: middle;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($purchase['magazine_title']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($purchase['issue'] ?? '—'); ?></td>
                                    <td><span class="text-success font-weight-bold">₦<?php echo number_format($purchase['amount'], 2); ?></span></td>
                                    <td>
                                        <?php if ($purchase['downloads_remaining'] > 0): ?>
                                            <span class="badge badge-success"><?php echo $purchase['downloads_remaining']; ?> left</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">0 left</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code class="small"><?php echo htmlspecialchars($purchase['paystack_reference'] ?? '—'); ?></code></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($purchase['purchased_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        "order": [[6, "desc"]]
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
