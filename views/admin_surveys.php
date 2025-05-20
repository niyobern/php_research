<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Access denied. Admins only.</div>';
    return;
}
// Handle survey deletion
if (isset($_GET['delete'])) {
    $sid = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM Surveys WHERE id = ?");
    $stmt->execute([$sid]);
    set_flash('Survey deleted!', 'success');
    header('Location: index.php?page=admin_surveys');
    exit();
}
// List all surveys
$stmt = $pdo->query("SELECT s.*, p.title as project_title, u.username FROM Surveys s JOIN ResearchProjects p ON s.project_id = p.id JOIN Users u ON p.researcher_id = u.id ORDER BY s.created_at DESC");
$surveys = $stmt->fetchAll();
?>
<div class="mb-4">
    <h2>Manage Surveys</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Project</th>
                <th>Researcher</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($surveys as $s): ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo htmlspecialchars($s['title']); ?></td>
                <td><?php echo htmlspecialchars($s['project_title']); ?></td>
                <td><?php echo htmlspecialchars($s['username']); ?></td>
                <td><?php echo htmlspecialchars($s['status']); ?></td>
                <td><?php echo htmlspecialchars($s['created_at']); ?></td>
                <td>
                    <a href="index.php?page=admin_surveys&delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this survey?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div> <!-- end .main-content --> 