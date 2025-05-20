<?php
// Fetch all surveys with their project info
$stmt = $pdo->prepare("
    SELECT s.*, p.title as project_title, 
           COUNT(DISTINCT r.id) as response_count
    FROM Surveys s 
    JOIN ResearchProjects p ON s.project_id = p.id
    LEFT JOIN Responses r ON s.id = r.survey_id
    GROUP BY s.id
    ORDER BY s.created_at DESC
");
$stmt->execute();
$surveys = $stmt->fetchAll();
?>

<div class="container">
    <h2>Survey Reports</h2>
    
    <div class="row">
        <?php foreach ($surveys as $survey): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($survey['title']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            Project: <?php echo htmlspecialchars($survey['project_title']); ?>
                        </h6>
                        <p class="card-text">
                            Status: <?php echo ucfirst($survey['status']); ?><br>
                            Responses: <?php echo $survey['response_count']; ?><br>
                            Created: <?php echo date('M d, Y', strtotime($survey['created_at'])); ?>
                        </p>
                        <div class="btn-group">
                            <a href="index.php?page=view_report&survey_id=<?php echo $survey['id']; ?>" 
                               class="btn btn-primary">View Report</a>
                            <a href="generate_pdf.php?survey_id=<?php echo $survey['id']; ?>" 
                               class="btn btn-outline-primary">Download PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div> 