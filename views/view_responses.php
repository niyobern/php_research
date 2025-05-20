<?php
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
if (!$survey_id) {
    echo '<div class="alert alert-danger">No survey selected.</div>';
    return;
}
// Fetch survey info
$stmt = $pdo->prepare("SELECT * FROM Surveys WHERE id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();
if (!$survey) {
    echo '<div class="alert alert-danger">Survey not found.</div>';
    return;
}
// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC, id ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch responses grouped by participant
$stmt = $pdo->prepare("SELECT participant_id, GROUP_CONCAT(CONCAT(question_id, ':', response_text) ORDER BY question_id) as responses FROM Responses WHERE survey_id = ? GROUP BY participant_id");
$stmt->execute([$survey_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="row">
    <div class="col-md-12">
        <h2>Responses for Survey: <?php echo htmlspecialchars($survey['title']); ?></h2>
        <a href="index.php?page=export_csv&survey_id=<?php echo $survey_id; ?>" class="btn btn-outline-primary mb-3">Export CSV</a>
        <?php if (empty($responses)): ?>
            <div class="alert alert-info">No responses yet.</div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <?php foreach ($questions as $q): ?>
                            <th><?php echo htmlspecialchars($q['question_text']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($responses as $resp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resp['participant_id']); ?></td>
                            <?php
                            $answers = [];
                            foreach (explode(',', $resp['responses']) as $pair) {
                                if (strpos($pair, ':') !== false) {
                                    list($qid, $answer) = explode(':', $pair, 2);
                                    $answers[$qid] = $answer;
                                }
                            }
                            foreach ($questions as $q) {
                                echo '<td>' . htmlspecialchars($answers[$q['id']] ?? '') . '</td>';
                            }
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div> 