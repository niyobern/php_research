<?php
$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
if (!$survey_id) {
    die('No survey selected.');
}
require_once 'config/database.php';
// Fetch survey info
$stmt = $pdo->prepare("SELECT * FROM Surveys WHERE id = ?");
$stmt->execute([$survey_id]);
$survey = $stmt->fetch();
if (!$survey) {
    die('Survey not found.');
}
// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC, id ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch responses grouped by participant
$stmt = $pdo->prepare("SELECT participant_id, GROUP_CONCAT(CONCAT(question_id, ':', response_text) ORDER BY question_id) as responses FROM Responses WHERE survey_id = ? GROUP BY participant_id");
$stmt->execute([$survey_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="survey_' . $survey_id . '_responses.csv"');
$output = fopen('php://output', 'w');
// Header row
$header = array_merge(['Participant'], array_map(function($q) { return $q['question_text']; }, $questions));
fputcsv($output, $header);
// Data rows
foreach ($responses as $resp) {
    $row = [$resp['participant_id']];
    $answers = [];
    foreach (explode(',', $resp['responses']) as $pair) {
        list($qid, $answer) = explode(':', $pair, 2);
        $answers[$qid] = $answer;
    }
    foreach ($questions as $q) {
        $row[] = $answers[$q['id']] ?? '';
    }
    fputcsv($output, $row);
}
fclose($output);
exit; 