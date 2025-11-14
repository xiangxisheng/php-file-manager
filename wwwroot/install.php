<?php
/**
 * Installation Page for PHP File Manager
 * Creates initial .auth_users file with admin account
 */

$auth_users_file = __DIR__ . '/.auth_users';
$error = '';
$success = '';

// If .auth_users already exists, redirect to index
if (file_exists($auth_users_file)) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if (empty($username)) {
        $error = '用户名不能为空';
    } elseif (strlen($username) < 3) {
        $error = '用户名至少需要 3 个字符';
    } elseif (empty($password)) {
        $error = '密码不能为空';
    } elseif (strlen($password) < 6) {
        $error = '密码至少需要 6 个字符';
    } elseif ($password !== $password_confirm) {
        $error = '两次输入的密码不一致';
    } else {
        // Create .auth_users file
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $auth_data = array($username => $password_hash);
        $json_data = json_encode($auth_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($auth_users_file, $json_data)) {
            // Set file permissions (read/write for owner only)
            @chmod($auth_users_file, 0600);
            $success = '安装成功！正在跳转到登录页面...';
            header('Refresh: 2; URL=index.php');
        } else {
            $error = '无法创建用户文件，请检查目录权限';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装 - PHP File Manager</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .install-header h1 {
            color: #667eea;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .install-header p {
            color: #666;
            font-size: 14px;
        }
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .info-box ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }
        .info-box li {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <h1>欢迎使用 PHP File Manager</h1>
                <p>首次使用，请创建管理员账户</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>错误：</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <strong>成功：</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <strong>安全提示：</strong>
                <ul>
                    <li>用户名至少 3 个字符</li>
                    <li>密码至少 6 个字符，建议使用字母、数字和符号组合</li>
                    <li>安装完成后，您可以在用户管理中添加更多用户</li>
                </ul>
            </div>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">管理员用户名</label>
                    <input type="text"
                           class="form-control"
                           id="username"
                           name="username"
                           placeholder="请输入用户名"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required
                           autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">密码</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="请输入密码"
                           required>
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">确认密码</label>
                    <input type="password"
                           class="form-control"
                           id="password_confirm"
                           name="password_confirm"
                           placeholder="请再次输入密码"
                           required>
                </div>

                <button type="submit" class="btn btn-install">开始安装</button>
            </form>
        </div>
    </div>

    <script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>
