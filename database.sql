-- 微博系统数据库结构

-- 微博表
CREATE TABLE IF NOT EXISTS `wlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '微博ID',
  `content` text NOT NULL COMMENT '微博内容(必填)',
  `files` text COMMENT '附件ID集合(JSON格式)',
  `is_hidden` tinyint(1) DEFAULT 0 COMMENT '是否隐藏(0:否 1:是)',
  `is_top` tinyint(1) DEFAULT 0 COMMENT '是否置顶(0:否 1:是)',
  `create_time` datetime NOT NULL COMMENT '创建时间(必填)',
  PRIMARY KEY (`id`),
  KEY `is_top` (`is_top`),
  KEY `is_hidden` (`is_hidden`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微博表';

-- 附件表
CREATE TABLE IF NOT EXISTS `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `filename` varchar(255) NOT NULL COMMENT '原始文件名(必填)',
  `filepath` varchar(255) NOT NULL COMMENT '存储路径(必填)',
  `filetype` varchar(50) NOT NULL COMMENT '文件类型(image/video/audio/file)(必填)',
  `filesize` bigint(20) NOT NULL COMMENT '文件大小(字节)(必填)',
  `upload_time` datetime NOT NULL COMMENT '上传时间(必填)',
  PRIMARY KEY (`id`),
  KEY `filetype` (`filetype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='附件表';

-- 演示数据
INSERT INTO `wlog` (`content`, `files`, `is_hidden`, `is_top`, `create_time`) VALUES
('欢迎使用单用户微博系统！这是一条置顶微博。访问 https://www.example.com 了解更多信息。', NULL, 0, 1, NOW()),
('今天天气真好，适合出去走走 😊', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('分享一张美图', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('刚刚完成了一个重要的项目，感觉很有成就感！#工作日常', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('推荐一首好听的音乐，循环了一整天', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('周末计划：1. 看电影 2. 逛公园 3. 读书', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('人生就像一场旅行，不在乎目的地，在乎的是沿途的风景。', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('今天学到了一个新技能，记录一下以免忘记', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('美食分享：自己做的晚餐，味道还不错', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('早安！新的一天，新的开始 ☀️', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('读书笔记：《活着》是一本值得反复阅读的好书', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('健身打卡第30天！坚持就是胜利 💪', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('下雨天，适合在家听歌看书', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('突然想起一句话：生活不止眼前的苟且，还有诗和远方', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('今天的晚霞特别美，可惜没拍下来', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('周末去爬山了，风景真不错！', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('推荐一部电影：《肖申克的救赎》永远的经典', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('咖啡时光 ☕ 享受片刻宁静', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('今天遇到一个很有趣的人，聊了很多', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('春天来了，万物复苏，心情也变好了', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 8 DAY)),
('晚安，好梦 🌙', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 8 DAY)),
('旅行计划：下个月去海边度假', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 9 DAY)),
('今天做了一个重要的决定，希望一切顺利', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 9 DAY)),
('音乐是生活的调味剂 🎵', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 10 DAY)),
('感恩生活中遇到的每一个人', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 10 DAY)),
('今天的心情：平静而美好', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 11 DAY)),
('阅读是一种享受，让人沉浸其中', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 11 DAY)),
('夜深了，该休息了。晚安世界 🌟', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 12 DAY)),
('新的一周开始了，加油！', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 12 DAY)),
('生活就是要活在当下，珍惜每一刻', NULL, 0, 0, DATE_SUB(NOW(), INTERVAL 13 DAY));