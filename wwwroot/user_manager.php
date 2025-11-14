<?php
/**
 * User Management Page for PHP File Manager
 * Allows admin to add, edit, and delete users
 */

session_start();

// Simple authentication check
$auth_users_file = __DIR__ . '/.auth_users';

// Redirect to install if .auth_users doesn't exist
if (!file_exists($auth_users_file)) {
    header('Location: install.php');
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: index.php');
    exit;
}

$current_user = $_SESSION['username'] ?? '';
$message = '';
$message_type = '';

// Load users
function load_users() {
    global $auth_users_file;
    if (file_exists($auth_users_file)) {
        $content = file_get_contents($auth_users_file);
        return json_decode($content, true) ?: array();
    }
    return array();
}

// Save users
function save_users($users) {
    global $auth_users_file;
    $json_data = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($auth_users_file, $json_data);
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'];
    $users = load_users();

    if ($action === 'list') {
        // Return user list (without passwords)
        $user_list = array();
        foreach ($users as $username => $hash) {
            $user_list[] = array('username' => $username);
        }
        echo json_encode(array('success' => true, 'users' => $user_list));
        exit;
    }

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(array('success' => false, 'message' => '用户名和密码不能为空'));
            exit;
        }

        if (strlen($username) < 3) {
            echo json_encode(array('success' => false, 'message' => '用户名至少需要 3 个字符'));
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(array('success' => false, 'message' => '密码至少需要 6 个字符'));
            exit;
        }

        if (isset($users[$username])) {
            echo json_encode(array('success' => false, 'message' => '用户名已存在'));
            exit;
        }

        $users[$username] = password_hash($password, PASSWORD_DEFAULT);

        if (save_users($users)) {
            echo json_encode(array('success' => true, 'message' => '用户添加成功'));
        } else {
            echo json_encode(array('success' => false, 'message' => '保存失败'));
        }
        exit;
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');

        if ($username === $current_user) {
            echo json_encode(array('success' => false, 'message' => '不能删除当前登录的用户'));
            exit;
        }

        if (!isset($users[$username])) {
            echo json_encode(array('success' => false, 'message' => '用户不存在'));
            exit;
        }

        if (count($users) <= 1) {
            echo json_encode(array('success' => false, 'message' => '至少需要保留一个用户'));
            exit;
        }

        unset($users[$username]);

        if (save_users($users)) {
            echo json_encode(array('success' => true, 'message' => '用户删除成功'));
        } else {
            echo json_encode(array('success' => false, 'message' => '保存失败'));
        }
        exit;
    }

    if ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(array('success' => false, 'message' => '用户名和密码不能为空'));
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(array('success' => false, 'message' => '密码至少需要 6 个字符'));
            exit;
        }

        if (!isset($users[$username])) {
            echo json_encode(array('success' => false, 'message' => '用户不存在'));
            exit;
        }

        $users[$username] = password_hash($password, PASSWORD_DEFAULT);

        if (save_users($users)) {
            echo json_encode(array('success' => true, 'message' => '密码修改成功'));
        } else {
            echo json_encode(array('success' => false, 'message' => '保存失败'));
        }
        exit;
    }

    echo json_encode(array('success' => false, 'message' => '未知操作'));
    exit;
}

$users = load_users();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - PHP File Manager</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome.min.css">
    <style>
        body {
            background: #f5f6fa;
            padding: 20px 0;
        }
        .container {
            max-width: 1000px;
        }
        .page-header {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .page-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #e1e8ed;
            transition: background 0.2s;
        }
        .user-item:last-child {
            border-bottom: none;
        }
        .user-item:hover {
            background: #f8f9fa;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        .user-name {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
        }
        .current-user-badge {
            background: #28a745;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        .user-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #667eea;
            border: none;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
        }
        .btn-warning {
            background: #ffc107;
            border: none;
            color: #333;
        }
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-1px);
        }
        .btn-danger {
            background: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-control {
            border-radius: 6px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #95a5a6;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <span><i class="fa fa-users"></i> 用户管理</span>
                <div>
                    <button class="btn btn-primary" onclick="showAddUserModal()">
                        <i class="fa fa-plus"></i> 添加用户
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> 返回文件管理
                    </a>
                </div>
            </h1>
        </div>

        <div class="user-card">
            <div id="message-container"></div>
            <ul class="user-list" id="userList">
                <li class="empty-state">
                    <i class="fa fa-spinner fa-spin"></i>
                    <p>加载中...</p>
                </li>
            </ul>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-user-plus"></i> 添加新用户</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="newUsername" class="form-label">用户名</label>
                            <input type="text" class="form-control" id="newUsername" required>
                            <small class="text-muted">至少 3 个字符</small>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">密码</label>
                            <input type="password" class="form-control" id="newPassword" required>
                            <small class="text-muted">至少 6 个字符</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="addUser()">添加</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-key"></i> 修改密码</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <input type="hidden" id="changePasswordUsername">
                        <div class="mb-3">
                            <label class="form-label">用户名</label>
                            <input type="text" class="form-control" id="displayUsername" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="changePassword" class="form-label">新密码</label>
                            <input type="password" class="form-control" id="changePassword" required>
                            <small class="text-muted">至少 6 个字符</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-warning" onclick="changePassword()">修改密码</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/jquery-3.6.1.min.js"></script>
    <script src="./js/bootstrap.bundle.min.js"></script>
    <script>
        const currentUser = '<?php echo htmlspecialchars($current_user); ?>';
        let addUserModal, changePasswordModal;

        document.addEventListener('DOMContentLoaded', function() {
            addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            loadUsers();
        });

        function showMessage(message, type = 'success') {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const messageHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.getElementById('message-container').innerHTML = messageHtml;

            setTimeout(() => {
                $('.alert').fadeOut();
            }, 3000);
        }

        function loadUsers() {
            fetch('?action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderUsers(data.users);
                    } else {
                        showMessage('加载用户列表失败', 'error');
                    }
                })
                .catch(error => {
                    showMessage('网络错误: ' + error.message, 'error');
                });
        }

        function renderUsers(users) {
            const userList = document.getElementById('userList');

            if (users.length === 0) {
                userList.innerHTML = `
                    <li class="empty-state">
                        <i class="fa fa-users"></i>
                        <p>暂无用户</p>
                    </li>
                `;
                return;
            }

            userList.innerHTML = users.map(user => {
                const isCurrentUser = user.username === currentUser;
                const avatar = user.username.charAt(0).toUpperCase();

                return `
                    <li class="user-item">
                        <div class="user-info">
                            <div class="user-avatar">${avatar}</div>
                            <div>
                                <div class="user-name">
                                    ${user.username}
                                    ${isCurrentUser ? '<span class="current-user-badge">当前用户</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="user-actions">
                            <button class="btn btn-sm btn-warning" onclick="showChangePasswordModal('${user.username}')">
                                <i class="fa fa-key"></i> 改密码
                            </button>
                            ${!isCurrentUser ? `
                                <button class="btn btn-sm btn-danger" onclick="deleteUser('${user.username}')">
                                    <i class="fa fa-trash"></i> 删除
                                </button>
                            ` : ''}
                        </div>
                    </li>
                `;
            }).join('');
        }

        function showAddUserModal() {
            document.getElementById('addUserForm').reset();
            addUserModal.show();
        }

        function addUser() {
            const username = document.getElementById('newUsername').value.trim();
            const password = document.getElementById('newPassword').value;

            if (!username || !password) {
                showMessage('请填写完整信息', 'error');
                return;
            }

            fetch('?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    addUserModal.hide();
                    loadUsers();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('网络错误: ' + error.message, 'error');
            });
        }

        function showChangePasswordModal(username) {
            document.getElementById('changePasswordUsername').value = username;
            document.getElementById('displayUsername').value = username;
            document.getElementById('changePassword').value = '';
            changePasswordModal.show();
        }

        function changePassword() {
            const username = document.getElementById('changePasswordUsername').value;
            const password = document.getElementById('changePassword').value;

            if (!password) {
                showMessage('请输入新密码', 'error');
                return;
            }

            fetch('?action=change_password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    changePasswordModal.hide();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('网络错误: ' + error.message, 'error');
            });
        }

        function deleteUser(username) {
            if (!confirm(`确定要删除用户 "${username}" 吗？此操作不可恢复！`)) {
                return;
            }

            fetch('?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message);
                    loadUsers();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('网络错误: ' + error.message, 'error');
            });
        }
    </script>
</body>
</html>
