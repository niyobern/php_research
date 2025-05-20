<?php
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
$resume_code = $_GET['resume'] ?? null;
$partial_answers = [];
if ($resume_code) {
    $stmt = $pdo->prepare("SELECT * FROM Responses WHERE survey_id = ? AND participant_id = ?");
    $stmt->execute([$survey_id, $resume_code]);
    while ($row = $stmt->fetch()) {
        $partial_answers[$row['question_id']] = $row['response_text'];
    }
}
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
$preview = isset($_GET['preview']) && $_GET['preview'] == 1;
// Enforce scheduling
if (!$preview) {
    $now = date('Y-m-d H:i:s');
    if (!empty($survey['open_date']) && $now < $survey['open_date']) {
        echo '<div class="alert alert-warning">This survey is not open yet. Opens at: ' . htmlspecialchars($survey['open_date']) . '</div>';
        return;
    }
    if (!empty($survey['close_date']) && $now > $survey['close_date']) {
        echo '<div class="alert alert-warning">This survey is now closed. Closed at: ' . htmlspecialchars($survey['close_date']) . '</div>';
        return;
    }
}
// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC, id ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_survey'])) {
    $participant_id = uniqid('anon_', true);
    // Build answers array for conditional logic
    $answers = [];
    foreach ($questions as $q) {
        // Check if this question should be shown
        $show = true;
        if (!empty($q['show_if_question_id'])) {
            $prev_id = $q['show_if_question_id'];
            $expected = $q['show_if_value'];
            $prev_val = isset($_POST['q_' . $prev_id]) ? $_POST['q_' . $prev_id] : null;
            if ($expected === '' && ($prev_val === null || $prev_val === '')) {
                $show = false;
            } elseif ($expected !== '' && $prev_val != $expected) {
                $show = false;
            }
        }
        if (!$show) continue;
        $response = $_POST['q_' . $q['id']] ?? null;
        if (is_array($response)) {
            $response = implode(", ", $response);
        }
        $stmt = $pdo->prepare("INSERT INTO Responses (survey_id, question_id, response_text, participant_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$survey_id, $q['id'], $response, $participant_id]);
        $answers['q_' . $q['id']] = $_POST['q_' . $q['id']] ?? null;
    }
    echo '<div class="alert alert-success">Thank you for your response!</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_progress'])) {
    $participant_id = $_POST['save_code'] ?: uniqid('resume_', true);
    foreach ($questions as $q) {
        // Check if this question should be shown
        $show = true;
        if (!empty($q['show_if_question_id'])) {
            $prev_id = $q['show_if_question_id'];
            $expected = $q['show_if_value'];
            $prev_val = isset($_POST['q_' . $prev_id]) ? $_POST['q_' . $prev_id] : null;
            if ($expected === '' && ($prev_val === null || $prev_val === '')) {
                $show = false;
            } elseif ($expected !== '' && $prev_val != $expected) {
                $show = false;
            }
        }
        if (!$show) continue;
        $response = $_POST['q_' . $q['id']] ?? null;
        if (is_array($response)) {
            $response = implode(", ", $response);
        }
        // Upsert partial response
        $stmt = $pdo->prepare("REPLACE INTO Responses (survey_id, question_id, response_text, participant_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$survey_id, $q['id'], $response, $participant_id]);
    }
    echo '<div class="alert alert-info">Your progress has been saved! Use this link to resume: <a href="index.php?page=public_survey&survey_id=' . $survey_id . '&resume=' . $participant_id . '">' . htmlspecialchars('index.php?page=public_survey&survey_id=' . $survey_id . '&resume=' . $participant_id) . '</a></div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_summary'])) {
    // Show summary page
    echo '<div class="card card-body mb-4"><h4>Review Your Answers</h4><ul class="list-group mb-3">';
    foreach ($questions as $q) {
        // Conditional logic
        $show = true;
        if (!empty($q['show_if_question_id'])) {
            $prev_id = $q['show_if_question_id'];
            $expected = $q['show_if_value'];
            $prev_val = $_POST['q_' . $prev_id] ?? null;
            if ($expected === '' && ($prev_val === null || $prev_val === '')) {
                $show = false;
            } elseif ($expected !== '' && $prev_val != $expected) {
                $show = false;
            }
        }
        if (!$show) continue;
        $answer = $_POST['q_' . $q['id']] ?? '';
        if (is_array($answer)) $answer = implode(', ', $answer);
        echo '<li class="list-group-item"><strong>' . htmlspecialchars($q['question_text']) . ':</strong> ' . htmlspecialchars($answer) . '</li>';
    }
    echo '</ul>';
    // Hidden fields to resubmit
    echo '<form method="POST">';
    foreach ($_POST as $k => $v) {
        if ($k === 'show_summary') continue;
        if (is_array($v)) {
            foreach ($v as $vv) {
                echo '<input type="hidden" name="' . htmlspecialchars($k) . '[]" value="' . htmlspecialchars($vv) . '">';
            }
        } else {
            echo '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '">';
        }
    }
    echo '<button type="submit" name="submit_survey" class="btn btn-success">Confirm and Submit</button> ';
    echo '<a href="#" onclick="history.back();return false;" class="btn btn-secondary">Back</a>';
    echo '</form></div>';
    return;
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
                    <?php if (!$resume_code): ?>
                        <input type="hidden" name="save_code" value="">
                    <?php else: ?>
                        <input type="hidden" name="save_code" value="<?php echo htmlspecialchars($resume_code); ?>">
                    <?php endif; ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="mb-4">
                            <label class="form-label"><strong><?php echo htmlspecialchars($q['question_text']); ?></strong><?php if ($q['required']) echo ' <span class="text-danger">*</span>'; ?></label>
                            <?php if ($q['question_type'] === 'text'): ?>
                                <input type="text" class="form-control" name="q_<?php echo $q['id']; ?>" value="<?php echo htmlspecialchars($partial_answers[$q['id']] ?? ''); ?>" <?php if ($q['required']) echo 'required'; ?>>
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
                            <?php elseif ($q['question_type'] === 'date'): ?>
                                <input type="date" class="form-control" name="q_<?php echo $q['id']; ?>" value="<?php echo htmlspecialchars($partial_answers[$q['id']] ?? ''); ?>" <?php if ($q['required']) echo 'required'; ?>>
                            <?php elseif ($q['question_type'] === 'likert'): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="q_<?php echo $q['id']; ?>" value="<?php echo $i; ?>" <?php if ($q['required']) echo 'required'; ?>>
                                            <label class="form-check-label"><?php echo $i; ?></label>
                                        </div>
                                    <?php endfor; ?>
                                    <span class="ms-2">(1 = Strongly Disagree, 5 = Strongly Agree)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="show_summary" class="btn btn-primary">Review Answers</button>
                    <button type="submit" name="save_progress" class="btn btn-secondary">Save and get link</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div> <!-- end .main-content --> 