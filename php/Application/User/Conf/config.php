<?php
/**
 * UCenter客户端配置文件
 * 注意：该配置文件请使用常量方式定义
 */
define('UC_APP_ID', 1); //应用ID
define('UC_API_TYPE', 'Model'); //可选值 Model / Service
define('UC_AUTH_KEY', '&17@:iY$0?(twB]kru)46J^!9l;.,Z5oE[bI_QmA'); //加密KEY
define('UC_DB_DSN', 'mysqli://xmwx1806030:HuBsQovazylCtbVnf5TV@rm-uf6p1mr3qpip1f1g4o.mysql.rds.aliyuncs.com:3306/xmwx1806030'); // 数据库连接，使用Model方式调用API必须配置此项
define('UC_TABLE_PREFIX', 'duoduo_'); // 数据表前缀，使用Model方式调用API必须配置此项
