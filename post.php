<?php
session_start();
include __DIR__ . '/db.php';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/csrf.php';

// Get post ID from URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle comment submission
$comment_success = false;
$comment_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!verify_csrf_token()) {
        $comment_error = 'Invalid session. Please refresh and try again.';
    } else {
        $commenter_name = trim($_POST['commenter_name'] ?? '');
    $commenter_email = trim($_POST['commenter_email'] ?? '');
    $comment_text = trim($_POST['comment_text'] ?? '');
    
    if (empty($commenter_name) || empty($comment_text)) {
        $comment_error = 'Name and comment are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, commenter_name, commenter_email, comment_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $post_id, $commenter_name, $commenter_email, $comment_text);
        
        if ($stmt->execute()) {
            $comment_success = true;
        } else {
            $comment_error = 'Failed to submit comment. Please try again.';
        }
        $stmt->close();
    }
}
}

// Fetch post data
$post = null;
if ($post_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM post_table WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
}

// Fetch comments for this post
$comments = [];
if ($post) {
    $stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? AND is_approved = 1 ORDER BY comment_date DESC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $comments_result = $stmt->get_result();
    while ($row = $comments_result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
}

// Get current page URL for sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<?php if (!$post): ?>
<!-- Post Not Found -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <i class="fas fa-exclamation-triangle fa-5x icon-gold mb-4"></i>
            <h2>Post Not Found</h2>
            <p class="text-muted">The article you're looking for doesn't exist or has been removed.</p>
            <a href="index.php" class="btn btn-gold-rounded mt-3">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Post Hero Section -->
<div class="hero-slide" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo !empty($post['post_image']) ? htmlspecialchars($post['post_image']) : 'images/qira/bg5.JPG'; ?>');">
    <div class="d-flex align-items-center justify-content-center h-100 hero-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center text-white py-5">
                    <span class="badge badge-gold mb-3">
                        <?php echo ucfirst(htmlspecialchars($post['category'] ?? 'General')); ?>
                    </span>
                    <h1 class="display-4 font-weight-bold" style="font-size: calc(1.5rem + 1.5vw);"><?php echo htmlspecialchars($post['post_title']); ?></h1>
                    <div class="post-meta mt-3">
                        <span class="mx-2"><i class="fas fa-calendar-alt"></i> <?php echo date("F d, Y", strtotime($post['post_date'])); ?></span>
                        <span class="mx-2"><i class="fas fa-heart icon-gold"></i> <span id="like-count"><?php echo $post['post_likes']; ?></span> likes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Post Content Section -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Full Post Image -->
            <?php if(!empty($post['post_image'])): ?>
                <img src="<?php echo htmlspecialchars($post['post_image']); ?>" class="img-fluid rounded shadow mb-4" alt="<?php echo htmlspecialchars($post['post_title']); ?>" style="width: 100%;">
            <?php endif; ?>

            <!-- Post Content -->
            <article class="post-content mb-5">
                <?php echo nl2br(htmlspecialchars($post['post_description'])); ?>
            </article>
            
            <!-- Like & Share Section -->
            <div class="like-share-section mb-5 p-4 bg-light rounded">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h5 class="mb-0"><i class="fas fa-thumbs-up icon-gold"></i> Enjoyed this article?</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end">
                            <!-- Like Button -->
                            <button id="like-btn" class="btn btn-like mr-2" onclick="likePost(<?php echo $post_id; ?>)">
                                <i class="fas fa-heart"></i> Like
                            </button>
                            
                            <!-- Share Buttons -->
                            <div class="share-buttons">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" target="_blank" class="btn btn-share btn-facebook" title="Share on Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($post['post_title']); ?>" target="_blank" class="btn btn-share btn-twitter" title="Share on Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($post['post_title'] . ' - ' . $current_url); ?>" target="_blank" class="btn btn-share btn-whatsapp" title="Share on WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <button class="btn btn-share btn-copy" onclick="copyLink()" title="Copy Link">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <!-- Comments Section -->
            <section class="comments-section mt-5">
                <h3 class="mb-4"><i class="fas fa-comments icon-gold"></i> Comments (<?php echo count($comments); ?>)</h3>
                
                <!-- Comment Form -->
                <div class="comment-form-wrapper mb-5">
                    <?php if ($comment_success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Your comment has been posted successfully!
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($comment_error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $comment_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card comment-form-card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Leave a Comment</h5>
                            <form method="POST" action="">
                                <?php csrf_field(); ?>
                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="commenter_name" class="form-control" placeholder="Your Name *" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="email" name="commenter_email" class="form-control" placeholder="Your Email (optional)">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <textarea name="comment_text" class="form-control" rows="4" placeholder="Write your comment here... *" required></textarea>
                                </div>
                                <button type="submit" name="submit_comment" class="btn btn-gold-rounded">
                                    <i class="fas fa-paper-plane"></i> Post Comment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Comments List -->
                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No comments yet. Be the first to comment!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card mb-3">
                                <div class="d-flex">
                                    <div class="comment-avatar mr-3">
                                        <div class="avatar-circle">
                                            <?php echo strtoupper(substr($comment['commenter_name'], 0, 1)); ?>
                                        </div>
                                    </div>
                                    <div class="comment-body flex-grow-1">
                                        <div class="comment-header d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="comment-author mb-0"><?php echo htmlspecialchars($comment['commenter_name']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> <?php echo date("M d, Y \a\\t g:i A", strtotime($comment['comment_date'])); ?>
                                            </small>
                                        </div>
                                        <p class="comment-text mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Back to Articles -->
            <div class="text-center mt-5">
                <a href="index.php" class="btn btn-outline-dark btn-rounded">
                    <i class="fas fa-arrow-left"></i> Back to Articles
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Like Post Function
function likePost(postId) {
    const likeBtn = document.getElementById('like-btn');
    const likeCount = document.getElementById('like-count');
    
    const csrfToken = '<?php echo get_csrf_token(); ?>';
    
    // Check if already liked (stored in session via PHP)
    fetch('like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId + '&csrf_token=' + csrfToken
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            likeCount.textContent = data.likes;
            likeBtn.classList.add('liked');
            likeBtn.innerHTML = '<i class="fas fa-heart"></i> Liked';
        } else if (data.already_liked) {
            alert('You have already liked this post!');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Copy Link Function
function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    }).catch(err => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Link copied to clipboard!');
    });
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
