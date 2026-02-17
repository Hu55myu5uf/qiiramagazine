<?php
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Slider Section -->
<div id="aboutCarousel" class="carousel slide carousel-fade" data-ride="carousel">
    <ol class="carousel-indicators">
        <li data-target="#aboutCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#aboutCarousel" data-slide-to="1"></li>
        <li data-target="#aboutCarousel" data-slide-to="2"></li>
        <li data-target="#aboutCarousel" data-slide-to="3"></li>
        <li data-target="#aboutCarousel" data-slide-to="4"></li>
    </ol>
    <div class="carousel-inner">
        <!-- Slide 1 -->
        <div class="carousel-item active hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">About Qiira Magazine</h1>
                    <p class="lead hero-lead-text">Your trusted source for insightful articles</p>
                </div>
            </div>
        </div>
        
        <!-- Slide 2 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg6.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Our Vision</h1>
                    <p class="lead hero-lead-text">Empowering communities through knowledge</p>
                </div>
            </div>
        </div>
        
        <!-- Slide 3 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Our Mission</h1>
                    <p class="lead hero-lead-text">Delivering high-quality content that informs and inspires</p>
                </div>
            </div>
        </div>
        
        <!-- Slide 4 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg6.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Our Legacy</h1>
                    <p class="lead hero-lead-text">Documenting history and shaping the future</p>
                </div>
            </div>
        </div>
        
        <!-- Slide 5 -->
        <div class="carousel-item hero-slide" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/qira/bg5.JPG');">
            <div class="d-flex align-items-center justify-content-center h-100 hero-content">
                <div class="container text-white text-center">
                    <h1 class="display-3 font-weight-bold">Join Our Journey</h1>
                    <p class="lead hero-lead-text">Be part of the Qiira community</p>
                </div>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#aboutCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#aboutCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    
    <!-- Mission Statement -->
    <div class="row mb-5">
        <div class="col-md-10 mx-auto">
            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4 font-weight-bold"><i class="fas fa-bullseye" style="color: #d4af37;"></i> Our Mission</h2>
                    <p class="lead text-center text-muted">
                        At Qiira Magazine, we are dedicated to delivering high-quality content that informs, educates, and inspires our readers. 
                        We believe in the power of knowledge to transform lives and communities.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- What We Cover -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4"><i class="fas fa-newspaper" style="color: #d4af37;"></i> What We Cover</h2>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow border-0 text-white text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg1.png'); background-size: cover; border-radius: 15px;">
                <div class="card-body py-5 d-flex flex-column justify-content-center">
                    <i class="fas fa-landmark fa-3x mb-3" style="color: #d4af37;"></i>
                    <h4>History</h4>
                    <p class="small">Exploring the past to understand our present and shape our future.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow border-0 text-white text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg2.jpg'); background-size: cover; border-radius: 15px;">
                <div class="card-body py-5 d-flex flex-column justify-content-center">
                    <i class="fas fa-theater-masks fa-3x mb-3" style="color: #d4af37;"></i>
                    <h4>Culture</h4>
                    <p class="small">Celebrating diversity and the rich tapestry of human expression.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow border-0 text-white text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg3.png'); background-size: cover; border-radius: 15px;">
                <div class="card-body py-5 d-flex flex-column justify-content-center">
                    <i class="fas fa-graduation-cap fa-3x mb-3" style="color: #d4af37;"></i>
                    <h4>Education</h4>
                    <p class="small">Empowering minds through knowledge and lifelong learning.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow border-0 text-white text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg4.png'); background-size: cover; border-radius: 15px;">
                <div class="card-body py-5 d-flex flex-column justify-content-center">
                    <i class="fas fa-briefcase fa-3x mb-3" style="color: #d4af37;"></i>
                    <h4>Business</h4>
                    <p class="small">Insights into entrepreneurship, commerce, and economic trends.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow border-0 text-white text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/qira/bg6.JPG'); background-size: cover; border-radius: 15px;">
                <div class="card-body py-5 d-flex flex-column justify-content-center">
                    <i class="fas fa-balance-scale fa-3x mb-3" style="color: #d4af37;"></i>
                    <h4>Politics</h4>
                    <p class="small">Analyzing governance, policy, and civic engagement.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
