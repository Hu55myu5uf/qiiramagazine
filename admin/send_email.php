<?php
include __DIR__ . '/../includes/csrf.php';

// Start session if not already started
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
$success_count = 0;
$fail_count = 0;

// Determine recipient mode
$send_to_all = isset($_GET['all']) && $_GET['all'] == '1';
$single_user_id = intval($_GET['user_id'] ?? 0);

// If single user, fetch their info
$single_user = null;
if ($single_user_id > 0) {
    $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $single_user_id);
    $stmt->execute();
    $single_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch all active users for the dropdown
$all_users = [];
$user_res = $conn->query("SELECT id, full_name, email FROM users WHERE is_suspended = 0 ORDER BY full_name ASC");
if ($user_res) {
    while ($u = $user_res->fetch_assoc()) {
        $all_users[] = $u;
    }
}

// Handle email sending
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger'>Invalid session. Please refresh and try again.</div>";
    } else {
        $subject = trim($_POST['subject'] ?? '');
    $email_body = trim($_POST['email_body'] ?? '');
    $recipient_mode = $_POST['recipient_mode'] ?? 'single';
    $selected_user_id = intval($_POST['selected_user_id'] ?? 0);

    if (empty($subject) || empty($email_body)) {
        $message = "<div class='alert alert-warning'>Subject and message are required.</div>";
    } else {
        // Build HTML email
        $html_email = build_email_html($subject, $email_body);
        
        $recipients = [];
        
        if ($recipient_mode === 'all') {
            // Get all active users
            $res = $conn->query("SELECT id, full_name, email FROM users WHERE is_suspended = 0");
            while ($r = $res->fetch_assoc()) {
                $recipients[] = $r;
            }
        } else {
            // Single user
            if ($selected_user_id > 0) {
                $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
                $stmt->bind_param("i", $selected_user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                if ($user) {
                    $recipients[] = $user;
                }
                $stmt->close();
            }
        }

        if (empty($recipients)) {
            $message = "<div class='alert alert-danger'>No recipients found.</div>";
        } else {
            // Send emails
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Qiira Magazine <noreply@qiiramagazine.com>\r\n";
            $headers .= "Reply-To: info@qiiramagazine.com\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            foreach ($recipients as $recipient) {
                // Personalize the email
                $personalized_html = str_replace('{{USER_NAME}}', htmlspecialchars($recipient['full_name']), $html_email);
                
                if (@mail($recipient['email'], $subject, $personalized_html, $headers)) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
            }

            // Log the email
            $recipient_type = $recipient_mode === 'all' ? 'all' : 'single';
            $recipient_email = $recipient_mode === 'all' ? null : ($recipients[0]['email'] ?? null);
            $sent_by = $_SESSION['username'] ?? 'admin';
            
            $log_stmt = $conn->prepare("INSERT INTO email_log (subject, message, recipient_type, recipient_email, total_sent, sent_by) VALUES (?, ?, ?, ?, ?, ?)");
            $log_stmt->bind_param("ssssis", $subject, $email_body, $recipient_type, $recipient_email, $success_count, $sent_by);
            $log_stmt->execute();
            $log_stmt->close();

            if ($success_count > 0 && $fail_count == 0) {
                $message = "<div class='alert alert-success'><i class='fas fa-check-circle mr-2'></i>Email sent successfully to {$success_count} recipient(s).</div>";
            } elseif ($success_count > 0 && $fail_count > 0) {
                $message = "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle mr-2'></i>Sent to {$success_count}, failed for {$fail_count} recipient(s).</div>";
            } else {
                $message = "<div class='alert alert-danger'><i class='fas fa-times-circle mr-2'></i>Failed to send emails. Check your server's mail configuration.</div>";
            }
        }
    }
}
}

// Fetch recent email logs
$recent_logs = [];
$log_res = $conn->query("SELECT * FROM email_log ORDER BY sent_at DESC LIMIT 20");
if ($log_res) {
    while ($log = $log_res->fetch_assoc()) {
        $recent_logs[] = $log;
    }
}

/**
 * Build a branded HTML email template
 */
function build_email_html($subject, $body) {
    // Convert line breaks to HTML
    $body_html = nl2br(htmlspecialchars($body));
    
    return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #000000; padding: 30px; text-align: center;">
                            <h1 style="color: #d4af37; margin: 0; font-size: 28px; letter-spacing: 2px;">QIIRA MAGAZINE</h1>
                        </td>
                    </tr>
                    <!-- Greeting -->
                    <tr>
                        <td style="padding: 30px 30px 10px 30px;">
                            <p style="color: #333; font-size: 16px; margin: 0;">Hello <strong>{{USER_NAME}}</strong>,</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 10px 30px 30px 30px;">
                            <div style="color: #555; font-size: 15px; line-height: 1.8;">' . $body_html . '</div>
                        </td>
                    </tr>
                    <!-- CTA Button -->
                    <tr>
                        <td style="padding: 0 30px 30px 30px; text-align: center;">
                            <a href="https://qiiramagazine.com" style="display: inline-block; background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; text-decoration: none; padding: 12px 30px; border-radius: 25px; font-weight: bold; font-size: 14px;">Visit Qiira Magazine</a>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #111; padding: 20px 30px; text-align: center;">
                            <p style="color: #888; font-size: 12px; margin: 0;">© ' . date('Y') . ' Qiira Magazine. All rights reserved.</p>
                            <p style="color: #666; font-size: 11px; margin: 5px 0 0 0;">You received this email because you are a registered member.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}
?>

<div class="container-fluid admin-container" style="margin-top: 30px; margin-bottom: 50px;">
    <!-- Back Link -->
    <div class="mb-3">
        <a href="manage_users.php" class="btn btn-dark" style="border-radius: 10px;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Manage Users
        </a>
    </div>

    <div class="row">
        <!-- Compose Email -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-paper-plane" style="color: #d4af37;"></i> Compose Email
                    </h4>
                </div>
                <div class="card-body">
                    <?php echo $message; ?>

                    <form method="POST">
                        <?php csrf_field(); ?>
                        <!-- Recipient Mode -->
                        <div class="form-group">
                            <label class="font-weight-bold">Send To</label>
                            <select name="recipient_mode" id="recipient_mode" class="form-control form-control-lg" style="border-radius: 10px;" onchange="toggleRecipientField()">
                                <option value="single" <?php echo (!$send_to_all) ? 'selected' : ''; ?>>Single User</option>
                                <option value="all" <?php echo ($send_to_all) ? 'selected' : ''; ?>>All Active Users (<?php echo count($all_users); ?>)</option>
                            </select>
                        </div>

                        <!-- Single User Selector -->
                        <div class="form-group" id="single_user_group" style="<?php echo $send_to_all ? 'display:none;' : ''; ?>">
                            <label class="font-weight-bold">Select User</label>
                            <select name="selected_user_id" id="selected_user_id" class="form-control form-control-lg" style="border-radius: 10px;">
                                <option value="">-- Choose a user --</option>
                                <?php foreach ($all_users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo ($single_user && $single_user['id'] == $u['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['full_name']); ?> (<?php echo htmlspecialchars($u['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- All Users Info -->
                        <div class="form-group" id="all_users_info" style="<?php echo $send_to_all ? '' : 'display:none;'; ?>">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-1"></i> This will send to <strong><?php echo count($all_users); ?></strong> active registered users.
                            </div>
                        </div>

                        <!-- Quick Templates -->
                        <div class="form-group">
                            <label class="font-weight-bold">Quick Templates</label>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 mb-1" onclick="applyTemplate('magazine')">
                                    <i class="fas fa-book mr-1"></i> New Magazine
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 mb-1" onclick="applyTemplate('news')">
                                    <i class="fas fa-newspaper mr-1"></i> Latest News
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="applyTemplate('promo')">
                                    <i class="fas fa-tags mr-1"></i> Promotion
                                </button>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="form-group">
                            <label class="font-weight-bold">Subject</label>
                            <input type="text" name="subject" id="email_subject" class="form-control form-control-lg"
                                   placeholder="Enter email subject" style="border-radius: 10px;" required>
                        </div>

                        <!-- Message Body -->
                        <div class="form-group">
                            <label class="font-weight-bold">Message</label>
                            <textarea name="email_body" id="email_body" class="form-control" rows="10"
                                      placeholder="Write your message here..." style="border-radius: 10px; resize: vertical;" required></textarea>
                            <small class="text-muted">Line breaks will be preserved. The recipient's name is added automatically.</small>
                        </div>

                        <!-- Send Button -->
                        <button type="submit" class="btn btn-lg btn-block" style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #000; border-radius: 10px; font-weight: 600;"
                                onclick="return confirm('Are you sure you want to send this email?');">
                            <i class="fas fa-paper-plane mr-2"></i> Send Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email Log -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-header text-white" style="background: #000; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0">
                        <i class="fas fa-history" style="color: #d4af37;"></i> Recent Emails
                    </h4>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($recent_logs)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No emails sent yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_logs as $log): ?>
                            <div class="border rounded p-3 mb-3" style="border-radius: 10px !important;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 font-weight-bold"><?php echo htmlspecialchars($log['subject']); ?></h6>
                                    <?php if ($log['recipient_type'] === 'all'): ?>
                                        <span class="badge badge-primary">All Users</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Single</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted d-block">
                                    <?php if ($log['recipient_type'] === 'single' && $log['recipient_email']): ?>
                                        <i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($log['recipient_email']); ?>
                                    <?php endif; ?>
                                    <i class="fas fa-paper-plane mr-1 ml-1"></i> <?php echo $log['total_sent']; ?> sent
                                    &middot;
                                    <i class="fas fa-clock mr-1"></i> <?php echo date('M d, Y h:i A', strtotime($log['sent_at'])); ?>
                                </small>
                                <p class="text-muted small mt-2 mb-0" style="max-height: 60px; overflow: hidden;">
                                    <?php echo htmlspecialchars(substr($log['message'], 0, 120)); ?><?php echo strlen($log['message']) > 120 ? '...' : ''; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRecipientField() {
    const mode = document.getElementById('recipient_mode').value;
    document.getElementById('single_user_group').style.display = mode === 'single' ? '' : 'none';
    document.getElementById('all_users_info').style.display = mode === 'all' ? '' : 'none';
}

function applyTemplate(type) {
    const subject = document.getElementById('email_subject');
    const body = document.getElementById('email_body');

    if (type === 'magazine') {
        subject.value = '📖 New Magazine Available - Qiira Magazine';
        body.value = 'We are excited to announce that a new issue of Qiira Magazine is now available!\n\nHead over to our website to check out the latest edition packed with insightful articles on History, Culture, Education, Business, and Politics.\n\nDon\'t miss out — grab your copy today!';
    } else if (type === 'news') {
        subject.value = '📰 Latest News/Posts Update - Qiira Magazine';
        body.value = 'We have fresh new content for you on Qiira Magazine!\n\nOur latest news and posts cover a wide range of topics. Visit our website to read the newest articles and stay informed.\n\nHappy reading!';
    } else if (type === 'promo') {
        subject.value = '🎉 Special Offer - Qiira Magazine';
        body.value = 'Great news! We have a special offer just for you.\n\nFor a limited time, enjoy exclusive access to our premium content. Visit our website to learn more about this promotion.\n\nThank you for being a valued reader!';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
