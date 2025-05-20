    </div>
    <footer class="footer mt-5 py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3">Research Survey Tool</h5>
                    <p class="text-muted mb-0">A comprehensive platform for managing research surveys and collecting responses.</p>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="index.php?page=projects" class="text-muted text-decoration-none">Projects</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a href="index.php?page=admin" class="text-muted text-decoration-none">Admin Panel</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Contact</h5>
                    <ul class="list-unstyled text-muted">
                        <li><i class="bi bi-envelope me-2"></i>support@researchsurvey.com</li>
                        <li><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> Research Survey Management Tool. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-muted text-decoration-none">Help Center</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/main.js"></script>
</body>
</html> 