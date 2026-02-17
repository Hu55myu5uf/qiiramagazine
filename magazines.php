<?php
include __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user's purchased magazine IDs if logged in
$user_purchases = [];
if (isset($_SESSION['user_id'])) {
    $p_stmt = $conn->prepare("SELECT magazine_id, downloads_remaining FROM purchases WHERE user_id = ?");
    $p_stmt->bind_param("i", $_SESSION['user_id']);
    $p_stmt->execute();
    $p_result = $p_stmt->get_result();
    while ($p = $p_result->fetch_assoc()) {
        $user_purchases[$p['magazine_id']] = $p['downloads_remaining'];
    }
    $p_stmt->close();
}

include __DIR__ . '/includes/header.php';

// Get all magazines from database
$magazines = $conn->query("SELECT * FROM magazines ORDER BY created_at DESC");
?>

<!-- Hero Slider Section -->
<div id="magCarousel" class="carousel slide carousel-fade" data-ride="carousel">
    <ol class="carousel-indicators">
        <li data-target="#magCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#magCarousel" data-slide-to="1"></li>
        <li data-target="#magCarousel" data-slide-to="2"></li>
        <li data-target="#magCarousel" data-slide-to="3"></li>
        <li data-target="#magCarousel" data-slide-to="4"></li>
    </ol>
    <div class="carousel-inner">
        <!-- Slide 1 -->
        <div class="carousel-item active hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Our Magazines</h1>
                    <p class="lead hero-lead-text">Explore our collection of premium publications</p>
                </div>
            </div>
        </div>
        <!-- Slide 2 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg6.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Latest Issues</h1>
                    <p class="lead hero-lead-text">Stay up to date with current events</p>
                </div>
            </div>
        </div>
        <!-- Slide 3 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Premium Content</h1>
                    <p class="lead hero-lead-text">Quality journalism at your fingertips</p>
                </div>
            </div>
        </div>
        <!-- Slide 4 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg6.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Subscribe Today</h1>
                    <p class="lead hero-lead-text">Join our community of readers</p>
                </div>
            </div>
        </div>
        <!-- Slide 5 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Collect Them All</h1>
                    <p class="lead hero-lead-text">Build your Qiira Magazine library</p>
                </div>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#magCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#magCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <div class="row">
        <?php if($magazines && $magazines->num_rows > 0): ?>
            <?php while($mag = $magazines->fetch_assoc()): ?>
            <div class="col-md-3 col-6 mb-4">
                <div class="card h-100 shadow border-0" style="border-radius: 15px; overflow: hidden;">
                    <?php if(!empty($mag['image'])): ?>
                        <div style="height: 300px; background: #f8f9fa;">
                            <img src="<?php echo htmlspecialchars($mag['image']); ?>" class="card-img-top" style="width: 100%; height: 100%; object-fit: contain;" alt="<?php echo htmlspecialchars($mag['title']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                            <i class="fas fa-newspaper fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Description -->
                    <div class="card-body text-center p-3">
                        <h6 class="mb-1 font-weight-bold"><?php echo htmlspecialchars($mag['title']); ?></h6>
                        <?php if(!empty($mag['issue'])): ?>
                            <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($mag['issue']); ?></small>
                        <?php endif; ?>
                        
                        <?php if($mag['category'] == 'free'): ?>
                            <h5 class="text-success font-weight-bold">Free</h5>
                        <?php else: ?>
                            <h5 style="color: #d4af37;">₦<?php echo number_format($mag['price'], 2); ?></h5>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="card-footer bg-white border-0 p-3">
                        <?php 
                        $mag_id = $mag['id'];
                        $is_purchased = isset($user_purchases[$mag_id]);
                        $downloads_left = $is_purchased ? $user_purchases[$mag_id] : 0;
                        ?>
                        
                        <?php if($mag['category'] == 'free' && !empty($mag['pdf_file'])): ?>
                            <!-- Free magazine with PDF -->
                            <a href="<?php echo htmlspecialchars($mag['pdf_file']); ?>" target="_blank" class="btn btn-block btn-success" style="border-radius: 20px;">
                                <i class="fas fa-file-download"></i> Download Free PDF
                            </a>
                        <?php elseif($is_purchased && $downloads_left > 0 && !empty($mag['pdf_file'])): ?>
                            <!-- Purchased - can download -->
                            <a href="download.php?id=<?php echo $mag_id; ?>" class="btn btn-block btn-success" style="border-radius: 20px;">
                                <i class="fas fa-download"></i> Download (<?php echo $downloads_left; ?> left)
                            </a>
                        <?php elseif($is_purchased && $downloads_left <= 0): ?>
                            <!-- Purchased but no downloads left -->
                            <button class="btn btn-block btn-secondary" style="border-radius: 20px;" disabled>
                                <i class="fas fa-times-circle"></i> No Downloads Left
                            </button>
                        <?php elseif($is_purchased && empty($mag['pdf_file'])): ?>
                            <!-- Purchased but PDF not available yet -->
                            <button class="btn btn-block btn-warning" style="border-radius: 20px;" disabled>
                                <i class="fas fa-clock"></i> PDF Coming Soon
                            </button>
                        <?php elseif(isset($_SESSION['user_id'])): ?>
                            <!-- Logged in but not purchased -->
                            <a href="checkout.php?id=<?php echo $mag_id; ?>" class="btn btn-block" style="background: #d4af37; color: #000; border-radius: 20px;">
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </a>
                        <?php else: ?>
                            <!-- Not logged in -->
                            <a href="login.php?redirect=<?php echo urlencode('checkout.php?id=' . $mag_id); ?>" class="btn btn-block" style="background: #d4af37; color: #000; border-radius: 20px;">
                                <i class="fas fa-sign-in-alt"></i> Login to Buy
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- No magazines yet -->
            <div class="col-12">
                <div class="alert alert-light text-center py-5 shadow-sm" style="border-radius: 15px;">
                    <i class="fas fa-newspaper fa-4x mb-3" style="color: #d4af37;"></i>
                    <h4>No magazines available yet</h4>
                    <p class="text-muted">Check back soon for our latest publications!</p>
                    <a href="index.php" class="btn mt-3" style="background: #000; color: #fff; border-radius: 20px;">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Back to Home -->
    <?php if($magazines && $magazines->num_rows > 0): ?>
    <div class="row mt-5">
        <div class="col-12 text-center">
            <a href="index.php" class="btn btn-outline-dark btn-lg" style="border-radius: 30px;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
