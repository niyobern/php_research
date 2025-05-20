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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-clipboard-data me-2"></i>Research Survey Tool
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isAuthenticated()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=home">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=projects">
                                <i class="bi bi-folder me-1"></i>Research Projects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=reports">
                                <i class="bi bi-file-earmark-text me-1"></i>Reports
                            </a>
                        </li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=admin">
                                <i class="bi bi-gear me-1"></i>Admin Panel
                            </a>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isAuthenticated()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?page=profile">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="index.php?page=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php $flash = get_flash(); if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flash['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php
    // Always define $page and $crumbs before using them
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
    <div class="container">
        <?php if ($page !== 'login' && $page !== 'register'): ?>
        <nav aria-label="breadcrumb" class="mt-4">
            <ol class="breadcrumb">
                <?php foreach ($crumbs as $i => $c): ?>
                    <li class="breadcrumb-item<?php if ($i === count($crumbs) - 1) echo ' active'; ?>"<?php if ($i === count($crumbs) - 1) echo ' aria-current=\"page\"'; ?>>
                        <?php if ($i !== count($crumbs) - 1): ?>
                            <a href="<?php echo $c[1]; ?>"><?php echo htmlspecialchars($c[0]); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($c[0]); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
        <div class="main-content<?php echo ($page === 'login' || $page === 'register') ? ' mt-5' : ''; ?>">
    </div> 