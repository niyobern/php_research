<?php
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
if (!$survey_id) {
    echo '<div class="alert alert-danger">No survey selected.</div>';
    return;
}

// Fetch survey info
$stmt = $pdo->prepare("
    SELECT s.*, p.title as project_title 
    FROM Surveys s 
    JOIN ResearchProjects p ON s.project_id = p.id 
    WHERE s.id = ?
");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();

if (!$survey) {
    echo '<div class="alert alert-danger">Survey not found.</div>';
    return;
}

// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

// Fetch responses
$stmt = $pdo->prepare("
    SELECT r.*, q.question_text, q.question_type 
    FROM Responses r 
    JOIN Questions q ON r.question_id = q.id 
    WHERE r.survey_id = ? 
    ORDER BY r.created_at ASC
");
$stmt->execute([$survey_id]);
$responses = $stmt->fetchAll();

// Group responses by participant
$participant_responses = [];
foreach ($responses as $response) {
    $participant_id = $response['participant_id'];
    if (!isset($participant_responses[$participant_id])) {
        $participant_responses[$participant_id] = [];
    }
    $participant_responses[$participant_id][] = $response;
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Report: <?php echo htmlspecialchars($survey['title']); ?></h2>
        <a href="index.php?page=generate_pdf&survey_id=<?php echo $survey_id; ?>" 
           class="btn btn-primary">Download PDF</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Survey Information</h5>
            <p class="card-text">
                Project: <?php echo htmlspecialchars($survey['project_title']); ?><br>
                Status: <?php echo ucfirst($survey['status']); ?><br>
                Total Responses: <?php echo count($participant_responses); ?><br>
                Created: <?php echo date('M d, Y', strtotime($survey['created_at'])); ?>
            </p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Question Summary</h5>
            <?php foreach ($questions as $question): ?>
                <div class="mb-4">
                    <h6><?php echo htmlspecialchars($question['question_text']); ?></h6>
                    <small class="text-muted">Type: <?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?></small>
                    
                    <?php
                    // Get responses for this question
                    $question_responses = array_filter($responses, function($r) use ($question) {
                        return $r['question_id'] == $question['id'];
                    });
                    
                    if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'single_choice'):
                        $options = json_decode($question['options'], true);
                        $option_counts = array_fill_keys($options, 0);
                        foreach ($question_responses as $response) {
                            $response_options = json_decode($response['response_text'], true);
                            if (is_array($response_options)) {
                                foreach ($response_options as $opt) {
                                    if (isset($option_counts[$opt])) {
                                        $option_counts[$opt]++;
                                    }
                                }
                            }
                        }
                    ?>
                        <div class="mt-2">
                            <?php foreach ($option_counts as $option => $count): ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span><?php echo htmlspecialchars($option); ?></span>
                                    <span class="badge bg-primary"><?php echo $count; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($question['question_type'] === 'rating'): 
                        $ratings = array_map(function($r) { return intval($r['response_text']); }, $question_responses);
                        $avg_rating = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                    ?>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Average Rating</span>
                                <span class="badge bg-primary"><?php echo number_format($avg_rating, 1); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Total Responses</span>
                                <span class="badge bg-primary"><?php echo count($question_responses); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Individual Responses</h5>
            <?php foreach ($participant_responses as $participant_id => $participant_data): ?>
                <div class="mb-4">
                    <h6>Response #<?php echo $participant_id; ?></h6>
                    <small class="text-muted">
                        Submitted: <?php echo date('M d, Y H:i', strtotime($participant_data[0]['created_at'])); ?>
                    </small>
                    <div class="mt-2">
                        <?php foreach ($participant_data as $response): ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($response['question_text']); ?></strong><br>
                                <?php
                                if ($response['question_type'] === 'multiple_choice') {
                                    $answers = json_decode($response['response_text'], true);
                                    echo implode(', ', array_map('htmlspecialchars', $answers));
                                } else {
                                    echo htmlspecialchars($response['response_text']);
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 