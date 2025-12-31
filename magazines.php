<?php
include 'db.php';
include 'includes/header.php';

// Get all magazines from database
$magazines = $conn->query("SELECT * FROM magazines ORDER BY created_at DESC");
?>

<!-- Hero Section -->
<div style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/qira/bg6.JPG'); background-size: cover; background-position: center; padding: 60px 0;">
    <div class="container text-white text-center">
        <h1 class="display-4"><i class="fas fa-newspaper"></i> Our Magazines</h1>
        <p class="lead mb-0">Explore our collection of premium magazines</p>
    </div>
</div>

<div class="container" style="margin-top: 40px; margin-bottom: 50px;">
    
    <div class="row">
        <?php if($magazines && $magazines->num_rows > 0): ?>
            <?php while($mag = $magazines->fetch_assoc()): ?>
            <div class="col-md-3 col-6 mb-4">
                <div class="card h-100 shadow">
                    <?php if(!empty($mag['image'])): ?>
                        <img src="<?php echo htmlspecialchars($mag['image']); ?>" class="card-img-top" style="height: 250px; object-fit: contain; background: #f8f9fa;" alt="<?php echo htmlspecialchars($mag['title']); ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 250px;">
                            <i class="fas fa-newspaper fa-3x text-white"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Description -->
                    <div class="card-body text-center p-2">
                        <h6 class="mb-1"><?php echo htmlspecialchars($mag['title']); ?></h6>
                        <?php if(!empty($mag['issue'])): ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($mag['issue']); ?></small>
                        <?php endif; ?>
                        <small class="text-success"><strong>$<?php echo number_format($mag['price'], 2); ?></strong></small>
                    </div>
                    
                    <!-- Buy Button -->
                    <div class="card-footer bg-white p-2">
                        <?php if(!empty($mag['buy_link'])): ?>
                            <a href="<?php echo htmlspecialchars($mag['buy_link']); ?>" target="_blank" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- No magazines yet -->
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-newspaper fa-4x mb-3"></i>
                    <h4>No magazines available yet</h4>
                    <p>Check back soon for our latest publications!</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Back to Home -->
    <?php if($magazines && $magazines->num_rows > 0): ?>
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="index.php" class="btn btn-outline-dark btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
