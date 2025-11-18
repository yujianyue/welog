<?php
require_once 'inc/conn.php';
require_once 'inc/pubs.php';
require_once 'inc/sqls.php';

// 检查是否已安装
/*
if (!file_exists('install.lock')) {
    header('Location: install.php');
    exit;
}
*/
$is_login = isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1;

// 处理Ajax请求
if (isset($_GET['act'])) {
    $act = $_GET['act'];
    
    switch ($act) {
        // 登录
        case 'login':
            $username = isset($_POST['username']) ? safe_input($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            if (!$username || !$password) {
                json_error('用户名和密码不能为空');
            }
            
            if ($username === $site_config['username'] && md5($password) === $site_config['password']) {
                $_SESSION['user_id'] = 1;
                json_success('登录成功');
            } else {
                json_error('用户名或密码错误');
            }
            break;
            
        // 注册
        case 'register':
            json_error('单用户系统，无需注册');
            break;
            
        // 退出
        case 'logout':
            session_destroy();
            json_success('退出成功');
            break;
            
        // 获取微博列表
        case 'get_list':
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $search = isset($_GET['search']) ? safe_input($_GET['search']) : '';
            
            $result = $db->get_wlog_list($page, $search, $is_login);
            json_success('获取成功', $result);
            break;
            
        // 发布微博
        case 'publish':
            check_login();
            
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $file_ids = isset($_POST['file_ids']) ? $_POST['file_ids'] : '';
            
            if (!$content) {
                json_error('微博内容不能为空');
            }
            
            if (mb_strlen($content, 'UTF-8') > 300) {
                json_error('微博内容不能超过300字');
            }
            
            $file_ids_arr = $file_ids ? explode(',', $file_ids) : [];
            
            $id = $db->add_wlog($content, $file_ids_arr);
            if ($id) {
                json_success('发布成功', ['id' => $id]);
            } else {
                json_error('发布失败');
            }
            break;
            
        // 删除微博
        case 'delete':
            check_login();
            
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                json_error('参数错误');
            }
            
            if ($db->delete_wlog($id)) {
                json_success('删除成功');
            } else {
                json_error('删除失败');
            }
            break;
            
        // 隐藏/显示微博
        case 'toggle_hidden':
            check_login();
            
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $hidden = isset($_POST['hidden']) ? intval($_POST['hidden']) : 0;
            
            if (!$id) {
                json_error('参数错误');
            }
            
            if ($db->update_wlog_status($id, 'is_hidden', $hidden)) {
                json_success($hidden ? '已隐藏' : '已显示');
            } else {
                json_error('操作失败');
            }
            break;
            
        // 置顶/取消置顶
        case 'toggle_top':
            check_login();
            
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $top = isset($_POST['top']) ? intval($_POST['top']) : 0;
            
            if (!$id) {
                json_error('参数错误');
            }
            
            if ($db->update_wlog_status($id, 'is_top', $top)) {
                json_success($top ? '已置顶' : '已取消置顶');
            } else {
                json_error('操作失败');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($site_config['title']); ?></title>
    <link rel="stylesheet" href="inc/index.css<?php echo "?d=".JSCSS;?>">
    <script><?php echo "var jsc = '".UPLOAD_PATH."';";?></script>
</head>
<body>
    <!-- 头部 -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="site-title"><?php echo htmlspecialchars($site_config['title']); ?></h1>
                    <p class="site-subtitle"><?php echo htmlspecialchars($site_config['subtitle']); ?></p>
                </div>
                <div class="header-right">
                    <?php if ($is_login): ?>
                        <div class="user-info">
                            <div class="user-avatar"><?php echo mb_substr($site_config['nickname'], 0, 1, 'UTF-8'); ?></div>
                            <span class="user-nickname"><?php echo htmlspecialchars($site_config['nickname']); ?></span>
                            <button class="btn-logout" onclick="logout()">退出</button>
                            <button class="btn-setting" onclick="location.href='setting.php'">设置</button>
                        </div>
                    <?php else: ?>
                        <button class="btn-login" onclick="show_login()">登录</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- 主体内容 -->
    <main class="main">
        <div class="container">
            <?php if ($is_login): ?>
                <!-- 搜索框 -->
                <div class="search-box">
                    <input type="text" id="search_input" placeholder="搜索微博内容..." onkeypress="if(event.keyCode==13)search_wlog()">
                    <button onclick="search_wlog()">搜索</button>
                    <button onclick="reset_search()">重置</button>
                </div>

                <!-- 发布框 -->
                <div class="publish-box">
                    <textarea id="publish_content" placeholder="分享新鲜事..." maxlength="300"></textarea>
                    <div class="publish-counter"><span id="content_length">0</span>/300</div>
                    <div class="publish-tools">
                        <label class="tool-btn" title="图片">
                            <input type="file" accept="image/*" multiple onchange="upload_images(this.files)" style="display:none">
                            📷 图片
                        </label>
                        <label class="tool-btn" title="视频">
                            <input type="file" accept="video/*" onchange="upload_video(this.files[0])" style="display:none">
                            🎬 视频
                        </label>
                        <label class="tool-btn" title="音乐">
                            <input type="file" accept="audio/*" onchange="upload_audio(this.files[0])" style="display:none">
                            🎵 音乐
                        </label>
                        <label class="tool-btn" title="文件">
                            <input type="file" onchange="upload_file(this.files[0])" style="display:none">
                            📎 文件
                        </label>
                    </div>
                    <div id="preview_files" class="preview-files"></div>
                    <button class="btn-publish" onclick="publish_wlog()">发布</button>
                </div>
            <?php endif; ?>

            <!-- 微博列表 -->
            <div id="wlog_list" class="wlog-list"></div>

            <!-- 分页 -->
            <div id="pagination" class="pagination"></div>
        </div>
    </main>

    <script src="inc/js.js<?php echo "?d=".JSCSS;?>"></script>
    <script src="inc/index.js<?php echo "?d=".JSCSS;?>"></script>
</body>
</html>