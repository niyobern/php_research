<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Access denied. Admins only.</div>';
    return;
}
?>
<div class="mb-4">
    <h2>Admin Panel</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! Use the links below to manage the system.</p>
    <ul class="list-group">
        <li class="list-group-item"><a href="index.php?page=admin_users">Manage Users</a></li>
        <li class="list-group-item"><a href="index.php?page=admin_projects">Manage Projects</a></li>
        <li class="list-group-item"><a href="index.php?page=admin_surveys">Manage Surveys</a></li>
    </ul>
</div>
</div> <!-- end .main-content --> 