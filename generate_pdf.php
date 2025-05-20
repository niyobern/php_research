<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    die('Please login to access this page.');
}

$survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
if (!$survey_id) {
    die('No survey selected.');
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
    die('Survey not found.');
}

// Fetch questions and responses
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE survey_id = ? ORDER BY order_number ASC");
$stmt->execute([$survey_id]);
$questions = $stmt->fetchAll();

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

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Research Survey System');
$pdf->SetTitle($survey['title'] . ' - Survey Report');

// Set default header data
$pdf->SetHeaderData('', 0, $survey['title'] . ' - Survey Report', 'Generated on ' . date('Y-m-d H:i:s'));

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Survey Information
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Survey Information', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Project: ' . $survey['project_title'], 0, 1, 'L');
$pdf->Cell(0, 10, 'Status: ' . ucfirst($survey['status']), 0, 1, 'L');
$pdf->Cell(0, 10, 'Total Responses: ' . count($participant_responses), 0, 1, 'L');
$pdf->Cell(0, 10, 'Created: ' . date('M d, Y', strtotime($survey['created_at'])), 0, 1, 'L');
$pdf->Ln(10);

// Question Summary
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Question Summary', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

foreach ($questions as $question) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, $question['question_text'], 0, 1, 'L');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Type: ' . ucfirst(str_replace('_', ' ', $question['question_type'])), 0, 1, 'L');
    
    // Get responses for this question
    $question_responses = array_filter($responses, function($r) use ($question) {
        return $r['question_id'] == $question['id'];
    });
    
    if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'single_choice') {
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
        
        foreach ($option_counts as $option => $count) {
            $pdf->Cell(0, 10, $option . ': ' . $count, 0, 1, 'L');
        }
    } elseif ($question['question_type'] === 'rating') {
        $ratings = array_map(function($r) { return intval($r['response_text']); }, $question_responses);
        $avg_rating = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
        $pdf->Cell(0, 10, 'Average Rating: ' . number_format($avg_rating, 1), 0, 1, 'L');
    } else {
        $pdf->Cell(0, 10, 'Total Responses: ' . count($question_responses), 0, 1, 'L');
    }
    
    $pdf->Ln(5);
}

// Individual Responses
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Individual Responses', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);

foreach ($participant_responses as $participant_id => $participant_data) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Response #' . $participant_id, 0, 1, 'L');
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Submitted: ' . date('M d, Y H:i', strtotime($participant_data[0]['created_at'])), 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 12);
    
    foreach ($participant_data as $response) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, $response['question_text'], 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        if ($response['question_type'] === 'multiple_choice') {
            $answers = json_decode($response['response_text'], true);
            $pdf->Cell(0, 10, implode(', ', $answers), 0, 1, 'L');
        } else {
            $pdf->Cell(0, 10, $response['response_text'], 0, 1, 'L');
        }
    }
    
    $pdf->Ln(5);
}

// Output the PDF
$pdf->Output($survey['title'] . '_report.pdf', 'D'); 