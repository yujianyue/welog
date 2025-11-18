<?php
// 数据库操作类
require_once 'conn.php';

class DB {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // 执行查询
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    // 获取单条记录
    public function get_row($sql) {
        $result = $this->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // 获取多条记录
    public function get_all($sql) {
        $result = $this->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }
    
    // 获取总数
    public function get_count($sql) {
        $result = $this->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_row();
            return intval($row[0]);
        }
        return 0;
    }
    
    // 执行插入
    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_values($data);
        
        $keys_str = implode(',', array_map(function($k) {
            return "`{$k}`";
        }, $keys));
        
        $values_str = implode(',', array_map(function($v) {
            return "'" . $this->escape($v) . "'";
        }, $values));
        
        $sql = "INSERT INTO `{$table}` ({$keys_str}) VALUES ({$values_str})";
        if ($this->query($sql)) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // 执行更新
    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "`{$key}`='" . $this->escape($value) . "'";
        }
        $set_str = implode(',', $set);
        
        $sql = "UPDATE `{$table}` SET {$set_str} WHERE {$where}";
        return $this->query($sql);
    }
    
    // 执行删除
    public function delete($table, $where) {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return $this->query($sql);
    }
    
    // 转义字符串
    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }
    
    // 获取微博列表
    public function get_wlog_list($page = 1, $search = '', $show_hidden = false) {
        $where = '1=1';
        
        if (!$show_hidden) {
            $where .= ' AND is_hidden=0';
        }
        
        if ($search) {
            $search = $this->escape($search);
            $where .= " AND content LIKE '%{$search}%'";
        }
        
        $total_sql = "SELECT COUNT(*) FROM wlog WHERE {$where}";
        $total = $this->get_count($total_sql);
        
        $page_info = get_page_info($total, $page);
        
        $sql = "SELECT * FROM wlog WHERE {$where} ORDER BY is_top DESC, create_time DESC LIMIT {$page_info['offset']},{$page_info['page_size']}";
        $list = $this->get_all($sql);
        
        // 处理附件
        foreach ($list as &$item) {
            if ($item['files']) {
                $file_ids = json_decode($item['files'], true);
                if ($file_ids && is_array($file_ids)) {
                    $ids = implode(',', array_map('intval', $file_ids));
                    $item['file_list'] = $this->get_all("SELECT * FROM file WHERE id IN ({$ids}) ORDER BY FIELD(id,{$ids})");
                } else {
                    $item['file_list'] = [];
                }
            } else {
                $item['file_list'] = [];
            }
        }
        
        return [
            'list' => $list,
            'page_info' => $page_info
        ];
    }
    
    // 获取单条微博
    public function get_wlog($id) {
        $id = intval($id);
        $wlog = $this->get_row("SELECT * FROM wlog WHERE id={$id}");
        
        if ($wlog && $wlog['files']) {
            $file_ids = json_decode($wlog['files'], true);
            if ($file_ids && is_array($file_ids)) {
                $ids = implode(',', array_map('intval', $file_ids));
                $wlog['file_list'] = $this->get_all("SELECT * FROM file WHERE id IN ({$ids}) ORDER BY FIELD(id,{$ids})");
            } else {
                $wlog['file_list'] = [];
            }
        } else {
            $wlog['file_list'] = [];
        }
        
        return $wlog;
    }
    
    // 添加微博
    public function add_wlog($content, $file_ids = []) {
        $data = [
            'content' => $content,
            'files' => $file_ids ? json_encode($file_ids) : null,
            'create_time' => date('Y-m-d H:i:s')
        ];
        return $this->insert('wlog', $data);
    }
    
    // 删除微博
    public function delete_wlog($id) {
        $id = intval($id);
        
        // 获取附件
        $wlog = $this->get_row("SELECT files FROM wlog WHERE id={$id}");
        if ($wlog && $wlog['files']) {
            $file_ids = json_decode($wlog['files'], true);
            if ($file_ids && is_array($file_ids)) {
                foreach ($file_ids as $fid) {
                    $this->delete_file($fid);
                }
            }
        }
        
        return $this->delete('wlog', "id={$id}");
    }
    
    // 更新微博状态
    public function update_wlog_status($id, $field, $value) {
        $id = intval($id);
        $value = intval($value);
        
        // 如果是置顶操作，先取消其他置顶
        if ($field == 'is_top' && $value == 1) {
            $this->query("UPDATE wlog SET is_top=0 WHERE is_top=1");
        }
        
        return $this->update('wlog', [$field => $value], "id={$id}");
    }
    
    // 添加附件
    public function add_file($filename, $filepath, $filetype, $filesize) {
        $data = [
            'filename' => $filename,
            'filepath' => $filepath,
            'filetype' => $filetype,
            'filesize' => $filesize,
            'upload_time' => date('Y-m-d H:i:s')
        ];
        return $this->insert('file', $data);
    }
    
    // 获取附件
    public function get_file($id) {
        $id = intval($id);
        return $this->get_row("SELECT * FROM file WHERE id={$id}");
    }
    
    // 删除附件
    public function delete_file($id) {
        $id = intval($id);
        $file = $this->get_row("SELECT filepath FROM file WHERE id={$id}");
        
        if ($file) {
            delete_file(UPLOAD_PATH . $file['filepath']);
            return $this->delete('file', "id={$id}");
        }
        
        return false;
    }
}

// 创建数据库实例
$db = new DB($conn);
?>