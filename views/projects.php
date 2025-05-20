<?php
// Handle project deletion
if (isset($_GET['delete_project'])) {
    $delete_id = intval($_GET['delete_project']);
    $stmt = $pdo->prepare("DELETE FROM ResearchProjects WHERE id = ? AND researcher_id = ?");
    $stmt->execute([$delete_id, $_SESSION['user_id']]);
    set_flash('Project deleted successfully!', 'success');
    header('Location: index.php?page=projects');
    exit();
}
// Handle project editing
if (isset($_POST['edit_project'])) {
    $edit_id = intval($_POST['edit_id']);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $stmt = $pdo->prepare("UPDATE ResearchProjects SET title = ?, description = ?, status = ? WHERE id = ? AND researcher_id = ?");
    $stmt->execute([$title, $description, $status, $edit_id, $_SESSION['user_id']]);
    set_flash('Project updated successfully!', 'success');
    header('Location: index.php?page=projects');
    exit();
}
// Handle new project creation
$errors = [];
if (isset($_POST['create_project'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    if ($title === '') {
        $errors[] = 'Project title is required.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO ResearchProjects (title, description, researcher_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $_SESSION['user_id'], $status]);
        set_flash('Project created successfully!', 'success');
        header('Location: index.php?page=projects');
        exit();
    }
}
// Fetch all projects for the user
$stmt = $pdo->prepare("SELECT * FROM ResearchProjects WHERE researcher_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors); ?></div>
<?php endif; ?>

<div class="container">
    <h2 class="mt-4">Research Projects</h2>
    <p>This is where you will be able to create, view, and manage your research projects.</p>
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
                            <button class="btn btn-sm btn-outline-secondary" onclick="showEditProject(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars(addslashes($project['title'])); ?>', '<?php echo htmlspecialchars(addslashes($project['description'])); ?>', '<?php echo $project['status']; ?>')">Edit</button>
                            <a href="index.php?page=projects&delete_project=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
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

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">
          <div class="mb-3">
            <label for="edit_title" class="form-label">Project Title</label>
            <input type="text" class="form-control" id="edit_title" name="title" required>
          </div>
          <div class="mb-3">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-select" id="edit_status" name="status">
              <option value="draft">Draft</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_project" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function showEditProject(id, title, description, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_status').value = status;
    var modal = new bootstrap.Modal(document.getElementById('editProjectModal'));
    modal.show();
}
</script>
</div> <!-- end .main-content --> 