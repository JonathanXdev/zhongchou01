<?php
    pdo_query("
DROP TABLE IF EXISTS  ".tablename('yhzc_crowd').";
DROP TABLE IF EXISTS  ".tablename('yhzc_crowd_category').";
DROP TABLE IF EXISTS  ".tablename('yhzc_crowd_march').";
DROP TABLE IF EXISTS  ".tablename('yhzc_crowd_report').";
DROP TABLE IF EXISTS  ".tablename('yhzc_crowd_reward').";
DROP TABLE IF EXISTS  ".tablename('yhzc_crowd_union').";
DROP TABLE IF EXISTS  ".tablename('yhzc_help').";
DROP TABLE IF EXISTS  ".tablename('yhzc_help_category').";
DROP TABLE IF EXISTS  ".tablename('yhzc_manage').";
DROP TABLE IF EXISTS  ".tablename('yhzc_manage_group').";
DROP TABLE IF EXISTS  ".tablename('yhzc_manage_role').";
DROP TABLE IF EXISTS  ".tablename('yhzc_money_log').";
DROP TABLE IF EXISTS  ".tablename('yhzc_paylog').";
DROP TABLE IF EXISTS  ".tablename('yhzc_role').";
DROP TABLE IF EXISTS  ".tablename('yhzc_statistics').";
DROP TABLE IF EXISTS  ".tablename('yhzc_sys_notice').";
DROP TABLE IF EXISTS  ".tablename('yhzc_sys_rule').";
DROP TABLE IF EXISTS  ".tablename('yhzc_system').";
DROP TABLE IF EXISTS  ".tablename('yhzc_user_collect').";
DROP TABLE IF EXISTS  ".tablename('yhzc_user_address').";
DROP TABLE IF EXISTS  ".tablename('yhzc_user').";
DROP TABLE IF EXISTS  ".tablename('yhzc_area').";
DROP TABLE IF EXISTS  ".tablename('yhzc_comments').";
");