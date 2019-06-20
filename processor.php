<?php
defined('IN_IA') or exit('Access Denied');
class yh_zhongchouModuleProcessor extends WeModuleProcessor {
    public function respond() {
        global $_W;
        $content = $this->message['content'];
        $sysrule=pdo_get('yhzc_sys_rule', array('key' => $content,'uniacid'=>$_W['uniacid']));
        return $this->respNews(array(
            'Title' => $sysrule['name'],
            'Description' =>$sysrule['content']==''?'点击进入':$sysrule['content'],
            'PicUrl' =>'',
            'Url' => $sysrule['link']
        ));
        //return $this->respText($content);
    }
}