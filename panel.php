<?php
require_once 'config.php';
requireLogin();

$panel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if user has access to this panel
$stmt = $pdo->prepare("
    SELECT p.* FROM panels p
    INNER JOIN user_panels up ON p.id = up.panel_id
    WHERE up.user_id = ? AND p.id = ?
");
$stmt->execute([$_SESSION['user_id'], $panel_id]);
$panel = $stmt->fetch();

if (!$panel) {
    redirect('/dashboard.php');
}

// Get panel users
$stmt = $pdo->prepare("
    SELECT u.*, 
           COALESCE(u.used_traffic, 0) as used_traffic,
           COALESCE(u.expire_time, 0) as expire_time
    FROM users u
    WHERE u.panel_id = ?
    ORDER BY u.created_at DESC
");
$stmt->execute([$panel_id]);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo htmlspecialchars($panel['alias']); ?></title>
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
                        <a class="nav-link" href="dashboard.php">
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
                    <h2><?php echo htmlspecialchars($panel['alias']); ?></h2>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-lg"></i> افزودن کاربر
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>نام کاربری</th>
                                        <th>ترافیک مصرفی</th>
                                        <th>زمان انقضا</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo formatTraffic($user['used_traffic']); ?></td>
                                        <td><?php echo formatExpireTime($user['expire_time']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" onclick="showUserDetails(<?php echo $user['id']; ?>)">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن کاربر جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="expire_time" class="form-label">زمان انقضا (روز)</label>
                            <input type="number" class="form-control" id="expire_time" name="expire_time" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" onclick="addUser()">افزودن</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function addUser() {
            const formData = new FormData(document.getElementById('addUserForm'));
            formData.append('panel_id', <?php echo $panel_id; ?>);

            $.ajax({
                url: 'api/add_user.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.error);
                    }
                },
                error: function() {
                    alert('خطا در ارتباط با سرور');
                }
            });
        }

        function showUserDetails(userId) {
            // Implement user details view
        }

        function editUser(userId) {
            // Implement user edit
        }

        function deleteUser(userId) {
            if (confirm('آیا از حذف این کاربر اطمینان دارید؟')) {
                $.ajax({
                    url: 'api/delete_user.php',
                    type: 'POST',
                    data: { user_id: userId },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.error);
                        }
                    },
                    error: function() {
                        alert('خطا در ارتباط با سرور');
                    }
                });
            }
        }
    </script>
</body>
</html> 