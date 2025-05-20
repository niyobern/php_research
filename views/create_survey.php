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

// Handle question deletion
if (isset($_GET['delete_question'])) {
    $delete_id = intval($_GET['delete_question']);
    $stmt = $pdo->prepare("DELETE FROM Questions WHERE id = ? AND survey_id = ?");
    $stmt->execute([$delete_id, intval($_GET['survey_id'])]);
    set_flash('Question deleted successfully!', 'success');
    header('Location: index.php?page=create_survey&survey_id=' . intval($_GET['survey_id']));
    exit();
}
// Handle question editing
if (isset($_POST['edit_question'])) {
    $edit_id = intval($_POST['edit_id']);
    $question_text = trim($_POST['edit_question_text'] ?? '');
    $question_type = $_POST['edit_question_type'] ?? 'text';
    $options = null;
    if (in_array($question_type, ['multiple_choice', 'single_choice'])) {
        $options = array_filter(array_map('trim', explode("\n", $_POST['edit_options'] ?? '')));
        $options = json_encode($options);
    }
    $required = isset($_POST['edit_required']) ? 1 : 0;
    $order_number = intval($_POST['edit_order_number'] ?? 1);
    $stmt = $pdo->prepare("UPDATE Questions SET question_text = ?, question_type = ?, options = ?, required = ?, order_number = ? WHERE id = ? AND survey_id = ?");
    $stmt->execute([$question_text, $question_type, $options, $required, $order_number, $edit_id, intval($_GET['survey_id'])]);
    set_flash('Question updated successfully!', 'success');
    header('Location: index.php?page=create_survey&survey_id=' . intval($_GET['survey_id']));
    exit();
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
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="showEditQuestion(<?php echo $q['id']; ?>, '<?php echo htmlspecialchars(addslashes($q['question_text'])); ?>', '<?php echo $q['question_type']; ?>', '<?php echo htmlspecialchars(addslashes($q['options'])); ?>', <?php echo $q['required']; ?>, <?php echo $q['order_number']; ?>)">Edit</button>
                            <a href="index.php?page=create_survey&survey_id=<?php echo $survey_id; ?>&delete_question=<?php echo $q['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                        </div>
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
<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_id">
          <div class="mb-3">
            <label class="form-label">Question Text</label>
            <input type="text" class="form-control" id="edit_question_text" name="edit_question_text" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Question Type</label>
            <select class="form-select" name="edit_question_type" id="edit_question_type_select" required onchange="toggleEditOptionsField()">
              <option value="text">Text Input</option>
              <option value="multiple_choice">Multiple Choice</option>
              <option value="single_choice">Single Choice</option>
              <option value="rating">Rating</option>
            </select>
          </div>
          <div class="mb-3" id="edit_options_field" style="display:none;">
            <label class="form-label">Options (one per line)</label>
            <textarea class="form-control" id="edit_options" name="edit_options" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Order</label>
            <input type="number" class="form-control" id="edit_order_number" name="edit_order_number" min="1">
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="edit_required" id="edit_required">
            <label class="form-check-label" for="edit_required">Required</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_question" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function showEditQuestion(id, text, type, options, required, order) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_question_text').value = text;
    document.getElementById('edit_question_type_select').value = type;
    document.getElementById('edit_order_number').value = order;
    document.getElementById('edit_required').checked = !!required;
    if (type === 'multiple_choice' || type === 'single_choice') {
        document.getElementById('edit_options_field').style.display = '';
        let opts = '';
        try {
            let arr = JSON.parse(options);
            if (Array.isArray(arr)) opts = arr.join('\n');
        } catch (e) {}
        document.getElementById('edit_options').value = opts;
    } else {
        document.getElementById('edit_options_field').style.display = 'none';
        document.getElementById('edit_options').value = '';
    }
    var modal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
    modal.show();
}
function toggleOptionsField() {
    var type = document.getElementById('question_type_select').value;
    document.getElementById('options_field').style.display = (type === 'multiple_choice' || type === 'single_choice') ? '' : 'none';
}
function toggleEditOptionsField() {
    var type = document.getElementById('edit_question_type_select').value;
    document.getElementById('edit_options_field').style.display = (type === 'multiple_choice' || type === 'single_choice') ? '' : 'none';
}
document.getElementById('question_type_select').addEventListener('change', toggleOptionsField);
document.getElementById('edit_question_type_select').addEventListener('change', toggleEditOptionsField);
</script>
</div> <!-- end .main-content --> 