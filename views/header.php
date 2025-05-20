<?php
// No whitespace before this line
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Survey Management Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Research Survey Tool</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isAuthenticated()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=projects">Research Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=surveys">Surveys</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isAuthenticated()): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=logout">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($flash['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <div class="container mt-4">
        <?php
        // Breadcrumbs logic
        $page = $_GET['page'] ?? 'home';
        $crumbs = [['Home', 'index.php']];
        if ($page === 'projects') {
            $crumbs[] = ['Projects', 'index.php?page=projects'];
        } elseif ($page === 'surveys' && isset($_GET['project_id'])) {
            $crumbs[] = ['Projects', 'index.php?page=projects'];
            $crumbs[] = ['Surveys', 'index.php?page=surveys&project_id=' . intval($_GET['project_id'])];
        } elseif ($page === 'create_survey' && isset($_GET['survey_id'])) {
            $crumbs[] = ['Projects', 'index.php?page=projects'];
            $crumbs[] = ['Surveys', 'index.php?page=surveys'];
            $crumbs[] = ['Edit Survey', '#'];
        } elseif ($page === 'view_responses' && isset($_GET['survey_id'])) {
            $crumbs[] = ['Projects', 'index.php?page=projects'];
            $crumbs[] = ['Surveys', 'index.php?page=surveys'];
            $crumbs[] = ['Responses', '#'];
        } elseif ($page === 'public_survey' && isset($_GET['survey_id'])) {
            $crumbs[] = ['Public Survey', '#'];
        }
        ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php foreach ($crumbs as $i => $c): ?>
                    <li class="breadcrumb-item<?php if ($i === count($crumbs) - 1) echo ' active'; ?>"<?php if ($i === count($crumbs) - 1) echo ' aria-current="page"'; ?>>
                        <?php if ($i !== count($crumbs) - 1): ?>
                            <a href="<?php echo $c[1]; ?>"><?php echo htmlspecialchars($c[0]); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($c[0]); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div> 