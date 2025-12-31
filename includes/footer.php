        </div>
        <!-- End Main Content -->

        <!-- Footer -->
        <footer class="mt-5">
            <div id="footer1" class="container-fluid py-3">
                <div class="row">
                    <div class="col-12 text-center">
                        <a class="footerlinks mx-2" href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
                        <a class="footerlinks mx-2" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                        <a class="footerlinks mx-2" href="category.php"><i class="fas fa-folder"></i> Categories</a>
                        <a class="footerlinks mx-2" href="editor_login.php"><i class="fas fa-user-edit"></i> Editor Portal</a>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <a class="footerlinks mx-2" href="manage_posts.php"><i class="fas fa-cog"></i> Manage Posts</a>
                            <a class="footerlinks mx-2" href="manage_editors.php"><i class="fas fa-users"></i> Manage Editors</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div id="footer2" class="container-fluid py-3">
                <div class="row">
                    <div class="col-12 text-center">
                        <p style="color: whitesmoke; margin: 0;">
                            &copy; <?php echo date('Y'); ?> All Rights Reserved. 
                            <a class="footerlinks" href="index.php">Qiira Company Limited</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
</body>
</html>
