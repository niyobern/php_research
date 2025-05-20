<?php
// Fetch user's research projects
$stmt = $pdo->prepare("
    SELECT * FROM ResearchProjects 
    WHERE researcher_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll();

// Fetch recent surveys
$stmt = $pdo->prepare("
    SELECT s.*, p.title as project_title 
    FROM Surveys s
    JOIN ResearchProjects p ON s.project_id = p.id
    WHERE p.researcher_id = ?
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$surveys = $stmt->fetchAll();

// Dashboard summary
$stmt = $pdo->prepare("SELECT COUNT(*) FROM ResearchProjects WHERE researcher_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_projects = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Surveys s JOIN ResearchProjects p ON s.project_id = p.id WHERE p.researcher_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_surveys = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Responses r JOIN Surveys s ON r.survey_id = s.id JOIN ResearchProjects p ON s.project_id = p.id WHERE p.researcher_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_responses = $stmt->fetchColumn();
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Dashboard</h2>
            <a href="index.php?page=projects&action=new" class="btn btn-primary">New Research Project</a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="number"><?php echo $total_projects; ?></div>
            <div class="label">Projects</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="number"><?php echo $total_surveys; ?></div>
            <div class="label">Surveys</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="number"><?php echo $total_responses; ?></div>
            <div class="label">Responses</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Projects -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Recent Research Projects</h4>
            </div>
            <div class="card-body">
                <?php if (empty($projects)): ?>
                    <p class="text-muted">No research projects yet. Create your first project!</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($projects as $project): ?>
                            <a href="index.php?page=projects&id=<?php echo $project['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($project['title']); ?></h5>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                                <small class="text-muted">Status: <?php echo ucfirst($project['status']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Surveys -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Recent Surveys</h4>
            </div>
            <div class="card-body">
                <?php if (empty($surveys)): ?>
                    <p class="text-muted">No surveys yet. Create a survey for your research project!</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($surveys as $survey): ?>
                            <a href="index.php?page=surveys&project_id=<?php echo $survey['project_id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($survey['title']); ?></h5>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($survey['created_at'])); ?></small>
                                </div>
                                <p class="mb-1">Project: <?php echo htmlspecialchars($survey['project_title']); ?></p>
                                <small class="text-muted">Status: <?php echo ucfirst($survey['status']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div> <!-- end .main-content --> 