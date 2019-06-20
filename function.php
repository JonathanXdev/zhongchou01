<?php
function appurl($mc='home',$mm='index',$other=''){
    global $_W;
    $url=$_W['siteroot']."app/index.php?i={$_W['uniacid']}&c=entry&do=r&m=yh_zhongchou&mc={$mc}&mm={$mm}";
    if(is_array($other)){
        foreach($other as $k=>$o){
            $url.='&'.$k.'='.$o;
        }
    }else if($other!=''){
        $url.='&'.$other;
    }
    return $url;
}
function weburl($mc='home',$mm='index',$other=''){
    global $_W;
    $url=$_W['siteroot']."web/index.php?c=site&a=entry&do=r&m=yh_zhongchou&mc={$mc}&mm={$mm}";
    if(is_array($other)){
        foreach($other as $k=>$o){
            $url.='&'.$k.'='.$o;
        }
    }else if($other!=''){
        $url.='&'.$other;
    }
    return $url;
}
function appweburl($mc='home',$mm='index',$other=''){
    global $_W;
    $url=$_W['siteroot']."app/index.php?i={$_W['uniacid']}&c=entry&do=web&m=yh_zhongchou&mc={$mc}&mm={$mm}";
    if(is_array($other)){
        foreach($other as $k=>$o){
            $url.='&'.$k.'='.$o;
        }
    }else if($other!=''){
        $url.='&'.$other;
    }
    return $url;
}
function add_para($url, $key, $value) {
    $url=preg_replace('/(.*)(?|&)'.$key.'=[^&]+?(&)(.*)/i','$1$2$4',$url.'&');
    $url=substr($url,0,-1);
    if(strpos($url,'?') === false){
        return ($url.'?'.$key.'='.$value);
    } else {
        return ($url.'&'.$key.'='.$value);
    }
}
function mysys($fd=false){
    global $_W;
    if($fd!='' && !is_array($fd)){
        $sys = pdo_get('yhzc_system', array('uniacid' => $_W['uniacid'],'key'=>$fd));
        if(!empty($sys)){
            return $sys['value'];
        }else{
            return false;
        }
    }else if(is_array($fd)){
        $fdvar='';
        if(is_array($fd)){
            foreach($fd as $k=>$f){
                if($k==0){
                    $fdvar.="`key`='{$f}'";
                }else{
                    $fdvar.=" or `key`='{$f}'";
                }
            }
        }else{
            $fdvar.="`key`={$fd}";
        }
        $sysarr = pdo_fetchall("SELECT * FROM ".tablename('yhzc_system')." WHERE uniacid ={$_W['uniacid']} and ($fdvar)");
    }else{
        $where=array(
            'uniacid' => $_W['uniacid'],
            'ntype'=>0
        );
        $sysarr=pdo_getall('yhzc_system',$where, array() , '');
        $system=array();
        if(!empty($sysarr)){
            foreach($sysarr as $s){
                $system[$s['key']]=$s['value'];
            }
        }
        return $system;
    }
    $system=array();
    if(!empty($sysarr)){
        foreach($sysarr as $s){
            $system[$s['key']]=$s['value'];
        }
        return $system;
    }else{
        return false;
    }

}
function wechatrefund($order,$money=0,$info=''){
    global $_W;
    $onrefund=mysys('refund');
    if(!is_array($order)){
        $order=pdo_get('yhzc_crowd_report', array('id' =>$order));
        if(empty($order)){
            return false;
        }
    }
    if($money>0){
        $order['money']=$money;
    }
    if($order['paytype']=='0'){
        $u_data = array(
            'status' => '-2',
        );
        $result = pdo_update('yhzc_crowd_report', $u_data, array('id' => $order['id']));
        if (!empty($result)) {
            if(mc_credit_update($order['user_id'], 'credit2', $order['money'], array(0, '众筹支持退款','yh_zhongchou'))=='true'){
                return true;
            }else{
                return false;
            }
        }else{
            $errarr[]=$order['id'];
        }
    }else if($onrefund==1 && $order['paytype']=='1'){
        //微信接口退款
        load()->model('refund');
        $refund_id = refund_create_order($order['id'],'yh_zhongchou', $order['money'], '众筹退款，众筹ID：'.$order['crowd_id']);
        if (is_error($refund_id)) {
            return false;
        }else{
            load()->classs('pay');
            $refundlog = pdo_get('core_refundlog', array('id' => $refund_id));
            $paylog = pdo_get('core_paylog', array('uniacid' => $order['uniacid'], 'uniontid' => $refundlog['uniontid']));
            $refund_param = reufnd_wechat_build($refund_id);
            $wechat = Pay::create('wechat');
            $response = $wechat->refund($refund_param);
            unlink(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_refund_all.pem');
            if (is_error($response)) {
                pdo_update('core_refundlog', array('status' => '-1'), array('id' => $refund_id));
            }
            $rsp=$response;
            if($rsp['return_code']=='SUCCESS'){
                $u_data = array(
                    'status' => '-2',
                );
                $result = pdo_update('yhzc_crowd_report', $u_data, array('id' => $order['id']));
                if (!empty($result)) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }else{
        $u_data = array(
            'status' => '-2',
        );
        $result = pdo_update('yhzc_crowd_report', $u_data, array('id' => $order['id']));
        if (!empty($result)) {
            return true;
        }else{
            return false;
        }
    }
}
if (!function_exists('putmsgs')) {
    function putmsgs($uid=0,$title,$content){
        global $_W;
        if($title=='' || $content==''){
            return false;
        }
        $data = array(
            'uniacid' =>$_W['uniacid'],
            'uid' => $uid,
            'title'=>$title,
            'msg'=>$content,
            'addtime'=>time(),
            'status'=>0
        );
        $result = pdo_insert('yhzc_msg', $data);
        if (!empty($result)) {
            return true;
        }else{
            return false;
        }
    }
}
if (!function_exists('tongji')) {
    function tongji($key = array(), $uniacid = '')
    {
        global $_W;
        if($uniacid==''){
            $uniacid= $_W['uniacid'];
        }
        $time=strtotime(date('Y-m-d'));
        $where=array('uniacid'=>$_W['uniacid'],'addtime'=>$time);
        $strow = pdo_get('yhzc_statistics', $where, array('id'));
        if (!empty($strow)) {
            if(is_array($key)){
                pdo_update('yhzc_statistics', $key, $where);
            }else{
                pdo_update('yhzc_statistics', array($key.' +=' => 1), $where);
            }
        } else {
            $data=array();
            if(!is_array($key)){
                $data[$key]=1;
            }else{
                $data=$key;
            }
            $data['uniacid'] = $uniacid;
            $data['addtime'] = $time;
            $result = pdo_insert('yhzc_statistics', $data);
        }
    }
}
function getSignature($params, $secret='dj39vg612xpxmd'){
    $str = '';
    ksort($params);
    foreach ($params as $k => $v) {
        if($v!=''){
            if($str!=''){
                $str .= '&';
            }
            $str .= "$k=$v";
        }
    }
    if($str!=''){
        $str .= '&';
    }
    $str .= 'key='.$secret;
    return strtoupper(md5($str));
}
?>