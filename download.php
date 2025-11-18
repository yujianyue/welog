<?php
require_once 'inc/conn.php';
require_once 'inc/pubs.php';
require_once 'inc/sqls.php';

// 获取文件ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    die('参数错误');
}

// 获取文件信息
$file = $db->get_file($id);

if (!$file) {
    die('文件不存在');
}

$filepath = UPLOAD_PATH . $file['filepath'];

if (!file_exists($filepath)) {
    die('文件已被删除');
}

// 设置下载头
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . urlencode($file['filename']) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// 输出文件
readfile($filepath);
exit;
?>