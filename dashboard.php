<?php
require_once 'config.php';
requireLogin();

// Get user's panels
$stmt = $pdo->prepare("
    SELECT p.* FROM panels p
    INNER JOIN user_panels up ON p.id = up.panel_id
    WHERE up.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$panels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - داشبورد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .main-content {
            padding: 20px;
        }
        .panel-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h3><?php echo APP_NAME; ?></h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> داشبورد
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="panels.php">
                            <i class="bi bi-grid"></i> مدیریت پنل‌ها
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> مدیریت کاربران
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> پروفایل
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> خروج
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>داشبورد</h2>
                    <div>
                        <span class="me-2">خوش آمدید، <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($panels as $panel): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card panel-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($panel['alias']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted"><?php echo htmlspecialchars($panel['panel_url']); ?></small>
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="panel.php?id=<?php echo $panel['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> مشاهده پنل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($panels)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            هیچ پنلی به شما اختصاص داده نشده است.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 