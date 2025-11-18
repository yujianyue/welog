<?php
// 公共函数库

// JSON返回函数
function json_return($code, $msg, $data = null) {
    $result = ['code' => $code, 'msg' => $msg];
    if ($data !== null) {
        $result['data'] = $data;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

// 成功返回
function json_success($msg = '操作成功', $data = null) {
    json_return(1, $msg, $data);
}

// 失败返回
function json_error($msg = '操作失败', $data = null) {
    json_return(0, $msg, $data);
}

// 安全过滤函数
function safe_input($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// 检查登录状态
function check_login() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
        json_error('请先登录');
    }
}

// 生成随机字符串
function random_string($length = 16) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

// 格式化文件大小
function format_size($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $index = 0;
    while ($size >= 1024 && $index < count($units) - 1) {
        $size /= 1024;
        $index++;
    }
    return round($size, 2) . $units[$index];
}

// 格式化时间
function format_time($time) {
    $timestamp = is_numeric($time) ? $time : strtotime($time);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return '刚刚';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '分钟前';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '小时前';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . '天前';
    } else {
        return date('Y-m-d H:i', $timestamp);
    }
}

// 自动链接网址
function auto_link($text) {
    $pattern = '/(https?:\/\/[^\s<]+)/i';
    return preg_replace($pattern, '<a href="$1" target="_blank">$1</a>', $text);
}

// 验证文件类型
function validate_file_type($filename, $allowed_types) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed_types);
}

// 获取文件扩展名
function get_file_ext($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// 创建目录
function create_dir($path) {
    if (!file_exists($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

// 删除文件
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return true;
}

// 分页计算
function get_page_info($total, $page = 1, $page_size = PAGE_SIZE) {
    $page = max(1, intval($page));
    $total_page = max(1, ceil($total / $page_size));
    $page = min($page, $total_page);
    $offset = ($page - 1) * $page_size;
    
    return [
        'total' => $total,
        'page' => $page,
        'page_size' => $page_size,
        'total_page' => $total_page,
        'offset' => $offset
    ];
}

// 生成唯一文件名
function generate_filename($original_name) {
    $ext = get_file_ext($original_name);
    return date('Ymd') . '/' . date('His') . '_' . random_string(8) . '.' . $ext;
}

// 检查文件MIME类型
function check_mime_type($filepath, $allowed_mimes) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $filepath);
    finfo_close($finfo);
    return in_array($mime, $allowed_mimes);
}
?>