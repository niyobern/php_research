<?php
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if (!$project_id) {
    echo '<div class="alert alert-danger">No project selected.</div>';
    return;
}
// Fetch project info
$stmt = $pdo->prepare("SELECT * FROM ResearchProjects WHERE id = ? AND researcher_id = ?");
$stmt->execute([$project_id, $_SESSION['user_id']]);
$project = $stmt->fetch();
if (!$project) {
    echo '<div class="alert alert-danger">Project not found or access denied.</div>';
    return;
}
// Handle survey deletion
if (isset($_GET['delete_survey'])) {
    $delete_id = intval($_GET['delete_survey']);
    $stmt = $pdo->prepare("DELETE FROM Surveys WHERE id = ?");
    $stmt->execute([$delete_id]);
    set_flash('Survey deleted successfully!', 'success');
    header('Location: index.php?page=surveys&project_id=' . intval($_GET['project_id']));
    exit();
}
// Handle new survey creation
if (isset($_POST['create_survey'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $errors = [];
    if ($title === '') {
        $errors[] = 'Survey title is required.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO Surveys (project_id, title, description, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$project_id, $title, $description, $status]);
        echo '<div class="alert alert-success">Survey created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}
// Fetch all surveys for the project
$stmt = $pdo->prepare("SELECT * FROM Surveys WHERE project_id = ? ORDER BY created_at DESC");
$stmt->execute([$project_id]);
$surveys = $stmt->fetchAll();
?>
<div class="row">
    <div class="col-md-6">
        <h2>Surveys for Project: <?php echo htmlspecialchars($project['title']); ?></h2>
        <?php if (empty($surveys)): ?>
            <div class="alert alert-info">No surveys yet. Create your first survey!</div>
        <?php else: ?>
            <ul class="list-group mb-4">
                <?php foreach ($surveys as $survey): ?>
                    <?php
                    // Get response count for this survey
                    $stmt2 = $pdo->prepare("SELECT COUNT(DISTINCT participant_id) FROM Responses WHERE survey_id = ?");
                    $stmt2->execute([$survey['id']]);
                    $response_count = $stmt2->fetchColumn();
                    // Status badge
                    $badge_class = 'secondary';
                    if ($survey['status'] === 'active') $badge_class = 'success';
                    elseif ($survey['status'] === 'closed') $badge_class = 'danger';
                    ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($survey['title']); ?></strong>
                        <span class="badge bg-<?php echo $badge_class; ?> ms-2"><?php echo ucfirst($survey['status']); ?></span>
                        <span class="badge bg-info text-dark ms-2">Responses: <?php echo $response_count; ?></span><br>
                        <small>Status: <?php echo htmlspecialchars($survey['status']); ?> | Created: <?php echo htmlspecialchars($survey['created_at']); ?></small><br>
                        <span><?php echo nl2br(htmlspecialchars($survey['description'])); ?></span>
                        <div class="mt-2">
                            <a href="index.php?page=create_survey&survey_id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit Survey</a>
                            <a href="index.php?page=surveys&project_id=<?php echo $project_id; ?>&delete_survey=<?php echo $survey['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this survey?');">Delete</a>
                            <a href="index.php?page=view_responses&survey_id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-outline-success">View Responses</a>
                            <a href="index.php?page=public_survey&survey_id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-outline-info" target="_blank" id="public-link-<?php echo $survey['id']; ?>">Public Link</a>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyPublicLink('<?php echo htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . '/index.php?page=public_survey&survey_id=' . $survey['id']); ?>')">Copy Link</button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h2>Create New Survey</h2>
        <form method="POST" class="card card-body">
            <div class="mb-3">
                <label for="title" class="form-label">Survey Title</label>
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
                    <option value="closed">Closed</option>
                </select>
            </div>
            <button type="submit" name="create_survey" class="btn btn-primary">Create Survey</button>
        </form>
    </div>
</div>
<script>
function copyPublicLink(link) {
    navigator.clipboard.writeText(link).then(function() {
        alert('Public link copied to clipboard!');
    });
}
</script>
</div> <!-- end .main-content --> 