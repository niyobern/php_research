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
// Fetch responses grouped by participant, with filters
$filter_sql = "SELECT participant_id, GROUP_CONCAT(CONCAT(question_id, ':', response_text) ORDER BY question_id) as responses, MIN(created_at) as min_date FROM Responses WHERE survey_id = ?";
$params = [$survey_id];
if (!empty($_GET['participant'])) {
    $filter_sql .= " AND participant_id = ?";
    $params[] = $_GET['participant'];
}
$filter_sql .= " GROUP BY participant_id";
$stmt = $pdo->prepare($filter_sql);
$stmt->execute($params);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Date range filter (after grouping)
if (!empty($_GET['from']) || !empty($_GET['to'])) {
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $responses = array_filter($responses, function($r) use ($from, $to) {
        $date = substr($r['min_date'], 0, 10);
        if ($from && $date < $from) return false;
        if ($to && $date > $to) return false;
        return true;
    });
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-clipboard-data me-2"></i>
                Responses for Survey: <?php echo htmlspecialchars($survey['title']); ?>
            </h2>
            <div>
                <a href="index.php?page=export_csv&survey_id=<?php echo $survey_id; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3" method="get" action="">
                    <input type="hidden" name="page" value="view_responses">
                    <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">
                    <div class="col-md-3">
                        <label class="form-label">Participant ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" name="participant" placeholder="Search by ID" value="<?php echo htmlspecialchars($_GET['participant'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                            <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="index.php?page=view_responses&survey_id=<?php echo $survey_id; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($responses)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No responses found matching your criteria.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($resp['participant_id']); ?>
                                            </span>
                                        </td>
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
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($responses)): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <h3 class="mb-4">
            <i class="bi bi-bar-chart me-2"></i>
            Response Analytics
        </h3>
        <?php
        // Prepare data for charts
        $question_stats = [];
        foreach ($questions as $q) {
            if (in_array($q['question_type'], ['single_choice', 'multiple_choice', 'likert', 'rating'])) {
                $question_stats[$q['id']] = [];
                foreach ($responses as $resp) {
                    $answers = [];
                    foreach (explode(',', $resp['responses']) as $pair) {
                        if (strpos($pair, ':') !== false) {
                            list($qid, $answer) = explode(':', $pair, 2);
                            $answers[$qid] = $answer;
                        }
                    }
                    $val = $answers[$q['id']] ?? null;
                    if ($val !== null) {
                        if ($q['question_type'] === 'multiple_choice') {
                            foreach (explode(', ', $val) as $v) {
                                $question_stats[$q['id']][$v] = ($question_stats[$q['id']][$v] ?? 0) + 1;
                            }
                        } else {
                            $question_stats[$q['id']][$val] = ($question_stats[$q['id']][$val] ?? 0) + 1;
                        }
                    }
                }
            }
        }
        ?>
        <?php foreach ($questions as $q): ?>
            <?php if (isset($question_stats[$q['id']]) && count($question_stats[$q['id']]) > 0): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><?php echo htmlspecialchars($q['question_text']); ?></h5>
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="chart_<?php echo $q['id']; ?>"></canvas>
                        </div>
                    </div>
                </div>
                <script>
                new Chart(document.getElementById('chart_<?php echo $q['id']; ?>').getContext('2d'), {
                    type: '<?php echo ($q['question_type'] === 'multiple_choice') ? 'bar' : 'pie'; ?>',
                    data: {
                        labels: <?php echo json_encode(array_keys($question_stats[$q['id']])); ?>,
                        datasets: [{
                            label: 'Responses',
                            data: <?php echo json_encode(array_values($question_stats[$q['id']])); ?>,
                            backgroundColor: [
                                '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545',
                                '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
                </script>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
</div> <!-- end .main-content --> 