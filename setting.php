<?php
require_once 'inc/conn.php';
require_once 'inc/pubs.php';

// 检查登录
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit;
}

// 处理Ajax请求
if (isset($_GET['act'])) {
    $act = $_GET['act'];
    
    switch ($act) {
        case 'save':
            $title = isset($_POST['title']) ? safe_input($_POST['title']) : '';
            $subtitle = isset($_POST['subtitle']) ? safe_input($_POST['subtitle']) : '';
            $nickname = isset($_POST['nickname']) ? safe_input($_POST['nickname']) : '';
            $username = isset($_POST['username']) ? safe_input($_POST['username']) : '';
            $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
            $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
            
            if (!$title || !$nickname || !$username) {
                json_error('标题、昵称和用户名不能为空');
            }
            
            $config = load_config();
            
            // 修改密码
            if ($new_password) {
                if (!$old_password) {
                    json_error('请输入原密码');
                }
                
                if (md5($old_password) !== $config['password']) {
                    json_error('原密码错误');
                }
                
                if (strlen($new_password) < 6) {
                    json_error('新密码不能少于6位');
                }
                
                $config['password'] = md5($new_password);
            }
            
            $config['title'] = $title;
            $config['subtitle'] = $subtitle;
            $config['nickname'] = $nickname;
            $config['username'] = $username;
            
            if (save_config($config)) {
                json_success('保存成功');
            } else {
                json_error('保存失败');
            }
            break;
            
        default:
            json_error('无效的操作');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - <?php echo htmlspecialchars($site_config['title']); ?></title>
    <link rel="stylesheet" href="inc/setting.css<?php echo "?d=".JSCSS;?>">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>系统设置</h1>
            <button onclick="location.href='index.php'">返回首页</button>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="setting-box">
                <div class="form-group">
                    <label>网站标题</label>
                    <input type="text" id="title" value="<?php echo htmlspecialchars($site_config['title']); ?>" placeholder="请输入网站标题">
                </div>

                <div class="form-group">
                    <label>网站副标题</label>
                    <input type="text" id="subtitle" value="<?php echo htmlspecialchars($site_config['subtitle']); ?>" placeholder="请输入网站副标题">
                </div>

                <div class="form-group">
                    <label>昵称</label>
                    <input type="text" id="nickname" value="<?php echo htmlspecialchars($site_config['nickname']); ?>" placeholder="请输入昵称">
                </div>

                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($site_config['username']); ?>" placeholder="请输入用户名">
                </div>

                <div class="form-group">
                    <label>原密码（修改密码时必填）</label>
                    <input type="password" id="old_password" placeholder="不修改密码请留空">
                </div>

                <div class="form-group">
                    <label>新密码（不少于6位）</label>
                    <input type="password" id="new_password" placeholder="不修改密码请留空">
                </div>

                <button class="btn-save" onclick="save_setting()">保存设置</button>
            </div>
        </div>
    </main>

    <script src="inc/js.js<?php echo "?d=".JSCSS;?>"></script>
    <script src="inc/setting.js<?php echo "?d=".JSCSS;?>"></script>
</body>
</html>