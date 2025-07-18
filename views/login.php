<?php
// Get login error from the main index file if it exists
$login_error = $login_error ?? null;
?>

<div class="row justify-content-center">
    <div class="col-12">
        <div class="card auth-card">
            <div class="card-header">
                <h3 class="text-center mb-0">Login</h3>
            </div>
            <div class="card-body">
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?page=login">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? <a href="index.php?page=register">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div> 