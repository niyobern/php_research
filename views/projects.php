<?php
// Handle new project creation
if (isset($_POST['create_project'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $errors = [];
    if ($title === '') {
        $errors[] = 'Project title is required.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO ResearchProjects (title, description, researcher_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $_SESSION['user_id'], $status]);
        echo '<div class="alert alert-success">Project created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}

// Fetch all projects for the user
$stmt = $pdo->prepare("SELECT * FROM ResearchProjects WHERE researcher_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();
?>

<div class="container">
    <h2 class="mt-4">Research Projects</h2>
    <p>This is where you will be able to create, view, and manage your research projects.</p>
    <div class="alert alert-info">Project management functionality coming soon!</div>
</div>

<div class="row">
    <div class="col-md-6">
        <h2>My Research Projects</h2>
        <?php if (empty($projects)): ?>
            <div class="alert alert-info">No research projects yet. Create your first project!</div>
        <?php else: ?>
            <ul class="list-group mb-4">
                <?php foreach ($projects as $project): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($project['title']); ?></strong><br>
                        <small>Status: <?php echo htmlspecialchars($project['status']); ?> | Created: <?php echo htmlspecialchars($project['created_at']); ?></small><br>
                        <span><?php echo nl2br(htmlspecialchars($project['description'])); ?></span>
                        <div class="mt-2">
                            <a href="index.php?page=surveys&project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">View Surveys</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h2>Create New Project</h2>
        <form method="POST" class="card card-body">
            <div class="mb-3">
                <label for="title" class="form-label">Project Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <button type="submit" name="create_project" class="btn btn-primary">Create Project</button>
        </form>
    </div>
</div> 