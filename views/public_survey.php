<?php
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
if (!$survey_id) {
    echo '<div class="alert alert-danger">No survey selected.</div>';
    return;
}
// Fetch survey info
$stmt = $pdo->prepare("SELECT * FROM Surveys WHERE id = ? AND status = 'active'");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();
if (!$survey) {
    echo '<div class="alert alert-danger">Survey not found or not active.";</div>';
    return;
}
// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC, id ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_survey'])) {
    $participant_id = uniqid('anon_', true);
    foreach ($questions as $q) {
        $response = $_POST['q_' . $q['id']] ?? null;
        if (is_array($response)) {
            $response = implode(", ", $response);
        }
        $stmt = $pdo->prepare("INSERT INTO Responses (survey_id, question_id, response_text, participant_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$survey_id, $q['id'], $response, $participant_id]);
    }
    echo '<div class="alert alert-success">Thank you for your response!</div>';
}
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($survey['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($survey['description'])); ?></p>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php foreach ($questions as $q): ?>
                        <div class="mb-4">
                            <label class="form-label"><strong><?php echo htmlspecialchars($q['question_text']); ?></strong><?php if ($q['required']) echo ' <span class="text-danger">*</span>'; ?></label>
                            <?php if ($q['question_type'] === 'text'): ?>
                                <input type="text" class="form-control" name="q_<?php echo $q['id']; ?>" <?php if ($q['required']) echo 'required'; ?>>
                            <?php elseif ($q['question_type'] === 'multiple_choice'): ?>
                                <?php foreach (json_decode($q['options'], true) as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="q_<?php echo $q['id']; ?>[]" value="<?php echo htmlspecialchars($opt); ?>">
                                        <label class="form-check-label"><?php echo htmlspecialchars($opt); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php elseif ($q['question_type'] === 'single_choice'): ?>
                                <?php foreach (json_decode($q['options'], true) as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo htmlspecialchars($opt); ?>" <?php if ($q['required']) echo 'required'; ?>>
                                        <label class="form-check-label"><?php echo htmlspecialchars($opt); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php elseif ($q['question_type'] === 'rating'): ?>
                                <input type="number" class="form-control" name="q_<?php echo $q['id']; ?>" min="1" max="5" <?php if ($q['required']) echo 'required'; ?> placeholder="Rate 1-5">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="submit_survey" class="btn btn-success">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div> 