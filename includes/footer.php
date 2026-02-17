        </div>
        <!-- End Main Content -->

        <!-- Modern Footer -->
        <footer class="mt-auto footer-main">
            <div class="container py-5">
                <div class="row">
                    <!-- Brand Column -->
                    <div class="col-lg-4 col-12 mb-4 text-center text-lg-left">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo $assets_path; ?>images/qira/qiiralogo.png" width="50" height="50" alt="Qiira Logo" class="mr-2">
                            <h4 class="text-white mb-0">Qiira Magazine</h4>
                        </div>
                        <p class="text-muted">
                            Your trusted source for insightful articles on History, Culture, Education, Business, and Politics.
                        </p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/share/1AUMcUPMJJ/?mibextid=wwXIfr" class="text-white mr-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                            <a href="https://x.com/qiiramagazine?s=21" class="text-white mr-3"><i class="fab fa-twitter fa-lg"></i></a>
                            <a href="https://www.instagram.com/qiiramagazine?igsh=MTc5cnAyZmNsejAwNQ==" class="text-white mr-3"><i class="fab fa-instagram fa-lg"></i></a>
                            <a href="https://www.linkedin.com/company/qiira-magazine/" class="text-white mr-3"><i class="fab fa-linkedin-in fa-lg"></i></a>
                        </div>
                    </div>
                    
                    <!-- Categories Column -->
                    <div class="col-lg-2 col-6 mb-4">
                        <h5 class="text-white mb-3">Categories</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="category.php?cat=history" class="text-muted">History</a></li>
                            <li class="mb-2"><a href="category.php?cat=culture" class="text-muted">Culture</a></li>
                            <li class="mb-2"><a href="category.php?cat=education" class="text-muted">Education</a></li>
                            <li class="mb-2"><a href="category.php?cat=business" class="text-muted">Business</a></li>
                            <li class="mb-2"><a href="category.php?cat=politics" class="text-muted">Politics</a></li>
                        </ul>
                    </div>
                    
                    <!-- Quick Links Column -->
                    <div class="col-lg-2 col-6 mb-4">
                        <h5 class="text-white mb-3">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="<?php echo $assets_path; ?>index.php" class="text-muted">Home</a></li>
                            <li class="mb-2"><a href="<?php echo $assets_path; ?>about.php" class="text-muted">About Us</a></li>
                            <li class="mb-2"><a href="<?php echo $assets_path; ?>magazines.php" class="text-muted">Magazines</a></li>
                            <li class="mb-2"><a href="<?php echo $assets_path; ?>contact.php" class="text-muted">Contact</a></li>
                        </ul>
                    </div>
                    
                    <!-- Newsletter Column -->
                    <div class="col-lg-4 col-12 mb-4 text-center text-lg-left">
                        <h5 class="text-white mb-3">Stay Updated</h5>
                        <p class="text-muted">Subscribe to our newsletter for the latest news/posts and updates.</p>
                        <form class="mt-3" id="newsletterForm">
                            <?php if (function_exists('csrf_field')) csrf_field(); ?>
                            <div class="input-group">
                                <input type="email" class="form-control newsletter-input" id="newsletterEmail" placeholder="Enter your email" required>
                                <div class="input-group-append">
                                    <button class="btn newsletter-btn" type="submit">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Copyright Bar -->
            <div class="copyright-bar">
                <div class="container py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-left">
                            <p class="text-muted mb-0">
                                &copy; <?php echo date('Y'); ?> <strong class="text-white">Qiira Company Limited</strong>. All Rights Reserved.
                            </p>
                        </div>
                        <div class="col-md-6 text-center text-md-right">
                            <a href="#" class="text-muted mx-2">Privacy Policy</a>
                            <a href="#" class="text-muted mx-2">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ViiSec Branding -->
            <div class="viisec-footer-container">
                <a href="https://viisec.onrender.com/" target="_blank" class="viisec-footer-badge" style="text-decoration: none;">
                    <img src="<?php echo $assets_path; ?>images/viisec-logo.png" alt="ViiSec Logo" class="viisec-logo-img">
                    <span class="viisec-footer-text">Developed by ViiSec Software Solutions</span>
                </a>
            </div>
        </footer>
    <script src="<?php echo $assets_path; ?>js/newsletter.js"></script>
</body>
</html>
