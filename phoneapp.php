<?php
/**
 * yh_zhongchou模块APP接口定义
 *
 * @author Mob891602868494
 * @url http://www.88x4.com
 */
defined('IN_IA') or exit('Access Denied');

class Yh_zhongchouModulePhoneapp extends WeModulePhoneapp {
	public function doPageTest(){
		global $_GPC, $_W;
		$errno = 0;
		$message = '返回消息';
		$data = array();
		return $this->result($errno, $message, $data);
	}
	
	
}