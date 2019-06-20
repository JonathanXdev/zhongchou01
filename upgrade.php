<?php
if (!pdo_tableexists('yhzc_task')) {
    pdo_query("CREATE TABLE ".tablename('yhzc_task')." (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uniacid` int(11) NULL,
	`tskid` varchar(32) NOT NULL COMMENT '唯一标识',
	`uid` int(11) NULL COMMENT '管理员ID',
	`ntype` smallint(3) NULL COMMENT '任务类型',
	`taskinfo` varchar(200) NULL,
	`status` tinyint(2) NULL COMMENT '任务状态0：等待，1：进行中，-1：已关闭',
	`info` varchar(32) NULL,
	`level` smallint(2) DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8;
    ");
}
if (!pdo_tableexists('yhzc_crowd')) {
    pdo_query("ALTER TABLE ".tablename('yhzc_crowd')." 
	ADD COLUMN `taskst` tinyint(2) NULL COMMENT '任务蜘蛛是否来过' AFTER `cjtype`");
}


?>