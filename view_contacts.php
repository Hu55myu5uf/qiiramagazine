<?php
include 'db.php';
include 'includes/header.php';

// Access Control - Admin only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle mark as read / delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($action == "mark_read" && $id > 0) {
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

<div class="container-fluid" style="margin-top: 20px;">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope"></i> Contact Submissions
                        <?php if($unread_count > 0): ?>
                            <span class="badge badge-danger"><?php echo $unread_count; ?> new</span>
                        <?php endif; ?>
                    </h4>
                    <a href="manage_posts.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Posts
                    </a>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <?php if($submissions->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead class="thead-dark">
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
                                <tr class="<?php echo $sub['is_read'] ? '' : 'table-info'; ?>">
                                    <td>
                                        <?php if($sub['is_read']): ?>
                                            <span class="badge badge-secondary"><i class="fas fa-check"></i> Read</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary"><i class="fas fa-envelope"></i> New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($sub['name']); ?></strong></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>">
                                            <?php echo htmlspecialchars($sub['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($sub['message'], 0, 100)); ?>
                                        <?php if(strlen($sub['message']) > 100): ?>...<?php endif; ?>
                                    </td>
                                    <td><?php echo date("M d, Y H:i", strtotime($sub['submitted_at'])); ?></td>
                                    <td>
                                        <!-- View Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal<?php echo $sub['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if(!$sub['is_read']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" name="action" value="mark_read" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="modal<?php echo $sub['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Message from <?php echo htmlspecialchars($sub['name']); ?></h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Email:</strong> 
                                                    <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>">
                                                        <?php echo htmlspecialchars($sub['email']); ?>
                                                    </a>
                                                </p>
                                                <p><strong>Date:</strong> <?php echo date("F d, Y H:i", strtotime($sub['submitted_at'])); ?></p>
                                                <hr>
                                                <p><strong>Message:</strong></p>
                                                <div class="bg-light p-3 rounded">
                                                    <?php echo nl2br(htmlspecialchars($sub['message'])); ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" class="btn btn-primary">
                                                    <i class="fas fa-reply"></i> Reply
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <h4>No messages yet</h4>
                        <p>Contact form submissions will appear here.</p>
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

<?php include 'includes/footer.php'; ?>
