<?php
// 数据库连接配置文件
session_start();

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'welog_chalide');
define('DB_PASS', 'EYTY86hfpRRhF53R');
define('DB_NAME', 'welog_chalide');
define('DB_CHARSET', 'utf8mb4');

// 系统配置
define('UPLOAD_PATH', 'uploads/'); // 上传文件存储路径
define('PAGE_SIZE', 10); // 每页显示数量
define('CHUNK_SIZE', 1024 * 1024); // 分片大小 1MB
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 最大文件50MB
define('MAX_VIDEO_DURATION', 600); // 最大视频时长600秒
define('MAX_AUDIO_DURATION', 600); // 最大音频时长600秒
define('IMAGE_MAX_WIDTH', 1000); // 图片最大宽度
define('JSCSS', '2025102406'); // jscss缓存参数

// 系统配置文件路径
define('CONFIG_FILE', 'config.json.php');

// 加载系统配置
function load_config() {
    if (file_exists(CONFIG_FILE)) {
        $config = json_decode(file_get_contents(CONFIG_FILE), true);
        return $config ? $config : get_default_config();
    }
  $config = get_default_config();
    save_config($config);
    return $config;
}

// 默认配置
function get_default_config() {
    return [
        'phper' => '<?php exit(); ?>',
        'title' => '我的微博',
        'subtitle' => '记录生活点滴',
        'username' => 'admin',
        'password' => md5('admin888'),
        'nickname' => '博主',
        'avatar' => ''
    ];
}

// 保存配置
function save_config($config) {
    return file_put_contents(CONFIG_FILE, json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// 获取当前配置
$site_config = load_config();

// 数据库连接
$conn = null;
//if (file_exists('install.lock')) {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('数据库连接失败：' . $conn->connect_error);
    }
    $conn->set_charset(DB_CHARSET);
//}
?>