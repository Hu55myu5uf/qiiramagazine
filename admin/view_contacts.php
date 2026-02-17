<?php
include __DIR__ . '/../db.php';
include __DIR__ . '/../includes/csrf.php';

// Start session if header is moved
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Access Control - Admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/../includes/header.php';

$message = "";

    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger'>Invalid session. Please refresh and try again.</div>";
    } elseif (($action == "mark_read" || $action == "delete") && $id > 0) {
        if ($action == "mark_read") {
            $stmt = $conn->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            $message = "<div class='alert alert-success'>Marked as read.</div>";
        }
        $stmt->close();
    } elseif ($action == "delete" && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            $message = "<div class='alert alert-warning'>Message deleted.</div>";
        }
        $stmt->close();
    }
}

// Get contact submissions
$submissions = $conn->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC");
$unread_count = $conn->query("SELECT COUNT(*) as cnt FROM contact_submissions WHERE is_read = 0")->fetch_assoc()['cnt'];
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11 col-md-12">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope" style="color: #d4af37;"></i> Contact Submissions
                        <?php if($unread_count > 0): ?>
                            <span class="badge badge-pill badge-danger ml-2"><?php echo $unread_count; ?> new</span>
                        <?php endif; ?>
                    </h4>
                    <a href="manage_posts.php" class="btn btn-outline-light btn-sm" style="border-radius: 20px;">
                        <i class="fas fa-arrow-left"></i> Back to Posts
                    </a>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <?php if($submissions->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable">
                            <thead class="text-white" style="background: #000;">
                                <tr>
                                    <th>Status</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sub = $submissions->fetch_assoc()): ?>
                                <tr class="<?php echo $sub['is_read'] ? '' : 'table-warning'; ?>" style="<?php echo $sub['is_read'] ? '' : 'background-color: rgba(212, 175, 55, 0.1);'; ?>">
                                    <td>
                                        <?php if($sub['is_read']): ?>
                                            <span class="badge badge-secondary" style="border-radius: 10px;">Read</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #d4af37; color: #000; border-radius: 10px;">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-weight-bold"><?php echo htmlspecialchars($sub['name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" class="text-dark">
                                            <?php echo htmlspecialchars($sub['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($sub['message'], 0, 80)); ?>
                                        <?php if(strlen($sub['message']) > 80): ?>...<?php endif; ?>
                                    </td>
                                    <td><?php echo date("M d, Y H:i", strtotime($sub['submitted_at'])); ?></td>
                                    <td>
                                        <!-- View Modal Trigger -->
                                        <button type="button" class="btn btn-info btn-sm rounded-circle" data-toggle="modal" data-target="#modal<?php echo $sub['id']; ?>" title="View Message">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if(!$sub['is_read']): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" name="action" value="mark_read" class="btn btn-success btn-sm rounded-circle" title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm rounded-circle" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="modal<?php echo $sub['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                                            <div class="modal-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-envelope" style="color: #d4af37;"></i> Message from <?php echo htmlspecialchars($sub['name']); ?>
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">From</small>
                                                    <h6><?php echo htmlspecialchars($sub['name']); ?> &lt;<a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" style="color: #d4af37;"><?php echo htmlspecialchars($sub['email']); ?></a>&gt;</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">Date</small>
                                                    <h6><?php echo date("l, F d, Y \a\\t h:i A", strtotime($sub['submitted_at'])); ?></h6>
                                                </div>
                                                <hr>
                                                <div class="p-3 rounded" style="background: #f8f9fa; border-left: 4px solid #d4af37;">
                                                    <?php echo nl2br(htmlspecialchars($sub['message'])); ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" class="btn" style="background: #d4af37; color: #000; border-radius: 20px;">
                                                    <i class="fas fa-reply"></i> Reply
                                                </a>
                                                <button type="button" class="btn btn-dark" data-dismiss="modal" style="border-radius: 20px;">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x mb-3 text-muted"></i>
                        <h4 class="text-muted">No messages yet</h4>
                        <p class="text-muted">Contact form submissions will appear here.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            "order": [[ 4, "desc" ]]
        });
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>



