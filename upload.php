<?php
require_once 'inc/conn.php';
require_once 'inc/pubs.php';
require_once 'inc/sqls.php';

check_login();

$act = isset($_GET['act']) ? $_GET['act'] : '';

switch ($act) {
    // 普通上传（小文件）
    case 'upload':
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        
        if (!isset($_FILES['file'])) {
            json_error('请选择文件');
        }
        
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            json_error('文件上传失败');
        }
        
        // 验证文件类型
        $allowed = [];
        switch ($type) {
            case 'image':
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                break;
            case 'video':
                $allowed = ['mp4', 'avi', 'mov', 'wmv', 'flv'];
                break;
            case 'audio':
                $allowed = ['mp3', 'wav', 'ogg', 'aac', 'm4a'];
                break;
            default:
                $type = 'file';
                $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
        }
        
        if (!validate_file_type($file['name'], $allowed)) {
            json_error('不支持的文件类型');
        }
        
        // 生成保存路径
        $save_dir = UPLOAD_PATH . date('Ymd') . '/';
        create_dir($save_dir);
        
        $filename = generate_filename($file['name']);
        $filepath = UPLOAD_PATH . $filename;
        
        // 移动文件
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            json_error('文件保存失败');
        }
        
        // 保存到数据库
        $file_id = $db->add_file(
            $file['name'],
            $filename,
            $type,
            $file['size']
        );
        
        if ($file_id) {
            json_success('上传成功', [
                'id' => $file_id,
                'filepath' => $filepath,
                'filename' => $file['name']
            ]);
        } else {
            delete_file($filepath);
            json_error('保存失败');
        }
        break;
        
    // 分片上传
    case 'chunk_upload':
        $chunk = isset($_POST['chunk']) ? intval($_POST['chunk']) : 0;
        $chunks = isset($_POST['chunks']) ? intval($_POST['chunks']) : 1;
        $fileId = isset($_POST['fileId']) ? $_POST['fileId'] : '';
        $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
        $filesize = isset($_POST['filesize']) ? intval($_POST['filesize']) : 0;
        $type = isset($_GET['type']) ? $_GET['type'] : 'file';
        
        if (!isset($_FILES['file']) || !$fileId) {
            json_error('参数错误');
        }
        
        // 创建临时目录
        $temp_dir = UPLOAD_PATH . 'temp/';
        create_dir($temp_dir);
        
        // 保存分片
        $chunk_file = $temp_dir . $fileId . '_' . $chunk;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $chunk_file)) {
            json_error('分片保存失败');
        }
        
        // 检查是否所有分片都上传完成
        $uploaded_chunks = 0;
        for ($i = 0; $i < $chunks; $i++) {
            if (file_exists($temp_dir . $fileId . '_' . $i)) {
                $uploaded_chunks++;
            }
        }
        
        // 如果所有分片都上传完成，合并文件
        if ($uploaded_chunks === $chunks) {
            // 验证文件类型
            $allowed = [];
            switch ($type) {
                case 'video':
                    $allowed = ['mp4', 'avi', 'mov', 'wmv', 'flv'];
                    break;
                case 'audio':
                    $allowed = ['mp3', 'wav', 'ogg', 'aac', 'm4a'];
                    break;
                default:
                    $type = 'file';
                    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'mp4', 'mp3'];
            }
            
            if (!validate_file_type($filename, $allowed)) {
                // 清理分片
                for ($i = 0; $i < $chunks; $i++) {
                    @unlink($temp_dir . $fileId . '_' . $i);
                }
                json_error('不支持的文件类型');
            }
            
            // 生成保存路径
            $save_dir = UPLOAD_PATH . date('Ymd') . '/';
            create_dir($save_dir);
            
            $save_filename = generate_filename($filename);
            $filepath = UPLOAD_PATH . $save_filename;
            
            // 合并分片
            $fp = fopen($filepath, 'wb');
            for ($i = 0; $i < $chunks; $i++) {
                $chunk_file = $temp_dir . $fileId . '_' . $i;
                $chunk_content = file_get_contents($chunk_file);
                fwrite($fp, $chunk_content);
                @unlink($chunk_file);
            }
            fclose($fp);
            
            // 保存到数据库
            $file_id = $db->add_file(
                $filename,
                $save_filename,
                $type,
                $filesize
            );
            
            if ($file_id) {
                json_success('上传成功', [
                    'id' => $file_id,
                    'filepath' => $filepath,
                    'filename' => $filename
                ]);
            } else {
                delete_file($filepath);
                json_error('保存失败');
            }
        } else {
            json_success('分片上传成功', ['chunk' => $chunk, 'uploaded' => $uploaded_chunks]);
        }
        break;
        
    default:
        json_error('无效的操作');
}
?>