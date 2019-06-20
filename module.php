<?php
/**
 * 一点众筹模块定义
 *
 * @author Mob891602868494
 * @url http://www.88x4.com
 */
defined('IN_IA') or exit('Access Denied');

class Yh_zhongchouModule extends WeModule {
    public function welcomeDisplay() {
        header("Location:".url('site/entry/r', array('m' =>'yh_zhongchou')));
        exit;
    }

}