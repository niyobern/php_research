<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Access denied. Admins only.</div>';
    return;
}
// Handle promote/demote
if (isset($_GET['promote'])) {
    $uid = intval($_GET['promote']);
    $stmt = $pdo->prepare("UPDATE Users SET role = 'admin' WHERE id = ?");
    $stmt->execute([$uid]);
    set_flash('User promoted to admin!', 'success');
    header('Location: index.php?page=admin_users');
    exit();
}
if (isset($_GET['demote'])) {
    $uid = intval($_GET['demote']);
    $stmt = $pdo->prepare("UPDATE Users SET role = 'researcher' WHERE id = ?");
    $stmt->execute([$uid]);
    set_flash('User demoted to researcher!', 'success');
    header('Location: index.php?page=admin_users');
    exit();
}
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
    $stmt->execute([$uid]);
    set_flash('User deleted!', 'success');
    header('Location: index.php?page=admin_users');
    exit();
}
// List users
$stmt = $pdo->query("SELECT * FROM Users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<div class="mb-4">
    <h2>Manage Users</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['role']); ?></td>
                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                <td>
                    <?php if ($u['role'] === 'researcher'): ?>
                        <a href="index.php?page=admin_users&promote=<?php echo $u['id']; ?>" class="btn btn-sm btn-success">Promote to Admin</a>
                    <?php elseif ($u['role'] === 'admin' && $u['id'] != $_SESSION['user_id']): ?>
                        <a href="index.php?page=admin_users&demote=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Demote</a>
                    <?php endif; ?>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="index.php?page=admin_users&delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div> <!-- end .main-content --> 