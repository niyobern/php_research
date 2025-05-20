<?php
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
if (!$survey_id) {
    echo '<div class="alert alert-danger">No survey selected.</div>';
    return;
}
// Fetch survey info
$stmt = $pdo->prepare("SELECT s.*, p.title as project_title FROM Surveys s JOIN ResearchProjects p ON s.project_id = p.id WHERE s.id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();
if (!$survey) {
    echo '<div class="alert alert-danger">Survey not found or access denied.</div>';
    return;
}

// Handle adding a new question
if (isset($_POST['add_question'])) {
    $question_text = trim($_POST['question_text'] ?? '');
    $question_type = $_POST['question_type'] ?? 'text';
    $options = null;
    if (in_array($question_type, ['multiple_choice', 'single_choice'])) {
        $options = array_filter(array_map('trim', explode("\n", $_POST['options'] ?? '')));
        $options = json_encode($options);
    }
    $required = isset($_POST['required']) ? 1 : 0;
    $order_number = intval($_POST['order_number'] ?? 1);
    $errors = [];
    if ($question_text === '') {
        $errors[] = 'Question text is required.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO Questions (survey_id, question_text, question_type, options, required, order_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$survey_id, $question_text, $question_type, $options, $required, $order_number]);
        echo '<div class="alert alert-success">Question added successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}
// Fetch all questions for the survey
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC, id ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();
?>
<div class="row">
    <div class="col-md-7">
        <h2>Questions for Survey: <?php echo htmlspecialchars($survey['title']); ?></h2>
        <?php if (empty($questions)): ?>
            <div class="alert alert-info">No questions yet. Add your first question!</div>
        <?php else: ?>
            <ul class="list-group mb-4">
                <?php foreach ($questions as $q): ?>
                    <li class="list-group-item">
                        <strong>Q<?php echo $q['order_number']; ?>:</strong> <?php echo htmlspecialchars($q['question_text']); ?><br>
                        <small>Type: <?php echo htmlspecialchars($q['question_type']); ?><?php if ($q['required']) echo ' | Required'; ?></small>
                        <?php if ($q['options']): ?>
                            <br><em>Options:</em>
                            <ul>
                                <?php foreach (json_decode($q['options'], true) as $opt): ?>
                                    <li><?php echo htmlspecialchars($opt); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="col-md-5">
        <h2>Add Question</h2>
        <form method="POST" class="card card-body">
            <div class="mb-3">
                <label class="form-label">Question Text</label>
                <input type="text" class="form-control" name="question_text" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Question Type</label>
                <select class="form-select" name="question_type" id="question_type_select" required onchange="toggleOptionsField()">
                    <option value="text">Text Input</option>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="single_choice">Single Choice</option>
                    <option value="rating">Rating</option>
                </select>
            </div>
            <div class="mb-3" id="options_field" style="display:none;">
                <label class="form-label">Options (one per line)</label>
                <textarea class="form-control" name="options" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Order</label>
                <input type="number" class="form-control" name="order_number" min="1" value="<?php echo count($questions) + 1; ?>">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="required" id="required">
                <label class="form-check-label" for="required">Required</label>
            </div>
            <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
        </form>
    </div>
</div>
<script>
function toggleOptionsField() {
    var type = document.getElementById('question_type_select').value;
    document.getElementById('options_field').style.display = (type === 'multiple_choice' || type === 'single_choice') ? '' : 'none';
}
document.getElementById('question_type_select').addEventListener('change', toggleOptionsField);
</script> 