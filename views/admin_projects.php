<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Access denied. Admins only.</div>';
    return;
}
// Handle project deletion
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM ResearchProjects WHERE id = ?");
    $stmt->execute([$pid]);
    set_flash('Project deleted!', 'success');
    header('Location: index.php?page=admin_projects');
    exit();
}
// List all projects
$stmt = $pdo->query("SELECT p.*, u.username FROM ResearchProjects p JOIN Users u ON p.researcher_id = u.id ORDER BY p.created_at DESC");
$projects = $stmt->fetchAll();
?>
<div class="mb-4">
    <h2>Manage Projects</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Researcher</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['title']); ?></td>
                <td><?php echo htmlspecialchars($p['username']); ?></td>
                <td><?php echo htmlspecialchars($p['status']); ?></td>
                <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                <td>
                    <a href="index.php?page=admin_projects&delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this project?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div> <!-- end .main-content --> 