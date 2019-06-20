<?php
/**
 * 完美众筹模块微站定义
 *
 * @author Mob891602868494
 * @url http://www.88x4.com
 */
defined('IN_IA') or exit('Access Denied');
include_once 'function.php';
class Yh_zhongchouModuleSite extends WeModuleSite {
    public function doMobileWeb(){
        global $_GPC;
        if($_GPC['mc']=='task' && $_GPC['mm']=='auto'){
            $this->doWebR();
        }else{
            exit;
        }
    }
    public function doWebR(){
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
switch (ENVIRONMENT)
{
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>='))
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        }
        else
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;
    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1);
}
	$system_path = MODULE_ROOT.'/system';
	$application_folder = MODULE_ROOT.'/web';
	define("APP_PATH",$application_folder);
	$view_folder ='';
	if (defined('STDIN'))
    {
        chdir(dirname(__FILE__));
    }

	if (($_temp = realpath($system_path)) !== FALSE)
    {
        $system_path = $_temp.DIRECTORY_SEPARATOR;
    }
    else
    {
        $system_path = strtr(
                rtrim($system_path, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            ).DIRECTORY_SEPARATOR;
    }
	if ( ! is_dir($system_path))
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
        exit(3); // EXIT_CONFIG
    }
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
	define('BASEPATH', $system_path);
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('SYSDIR', basename(BASEPATH));
	if (is_dir($application_folder))
    {
        if (($_temp = realpath($application_folder)) !== FALSE)
        {
            $application_folder = $_temp;
        }
        else
        {
            $application_folder = strtr(
                rtrim($application_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
        }
    }
    elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
    {
        $application_folder = BASEPATH.strtr(
                trim($application_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
    }
    else
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3);
    }

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
    {
        $view_folder = APPPATH.'views';
    }
    elseif (is_dir($view_folder))
    {
        if (($_temp = realpath($view_folder)) !== FALSE)
        {
            $view_folder = $_temp;
        }
        else
        {
            $view_folder = strtr(
                rtrim($view_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
        }
    }
    elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
    {
        $view_folder = APPPATH.strtr(
                trim($view_folder, '/\\'),
                '/\\',
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
            );
    }
    else
    {
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
        exit(3);
    }
define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);
require_once BASEPATH.'core/CodeIgniter.php';

    }
    public function doMobileR(){
        //echo $this->createMobileUrl('home');
        define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');
        switch (ENVIRONMENT)
        {
            case 'development':
                error_reporting(-1);
                ini_set('display_errors', 1);
                break;

            case 'testing':
            case 'production':
                ini_set('display_errors', 0);
                if (version_compare(PHP_VERSION, '5.3', '>='))
                {
                    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
                }
                else
                {
                    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
                }
                break;
            default:
                header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
                echo 'The application environment is not set correctly.';
                exit(1);
        }
        $system_path = MODULE_ROOT.'/system';
        $application_folder = MODULE_ROOT.'/app';
        define("APP_PATH",$application_folder);
        $view_folder ='';
        if (defined('STDIN'))
        {
            chdir(dirname(__FILE__));
        }

        if (($_temp = realpath($system_path)) !== FALSE)
        {
            $system_path = $_temp.DIRECTORY_SEPARATOR;
        }
        else
        {
            $system_path = strtr(
                    rtrim($system_path, '/\\'),
                    '/\\',
                    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
                ).DIRECTORY_SEPARATOR;
        }
        if ( ! is_dir($system_path))
        {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
            exit(3); // EXIT_CONFIG
        }
        define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
        define('BASEPATH', $system_path);
        define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
        define('SYSDIR', basename(BASEPATH));
        if (is_dir($application_folder))
        {
            if (($_temp = realpath($application_folder)) !== FALSE)
            {
                $application_folder = $_temp;
            }
            else
            {
                $application_folder = strtr(
                    rtrim($application_folder, '/\\'),
                    '/\\',
                    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
                );
            }
        }
        elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
        {
            $application_folder = BASEPATH.strtr(
                    trim($application_folder, '/\\'),
                    '/\\',
                    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
                );
        }
        else
        {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
            exit(3);
        }

        define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);
        if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
        {
            $view_folder = APPPATH.'views';
        }
        elseif (is_dir($view_folder))
        {
            if (($_temp = realpath($view_folder)) !== FALSE)
            {
                $view_folder = $_temp;
            }
            else
            {
                $view_folder = strtr(
                    rtrim($view_folder, '/\\'),
                    '/\\',
                    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
                );
            }
        }
        elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
        {
            $view_folder = APPPATH.strtr(
                    trim($view_folder, '/\\'),
                    '/\\',
                    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
                );
        }
        else
        {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
            exit(3);
        }
        define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);
        require_once BASEPATH.'core/CodeIgniter.php';
    }
    public function doMobilePay() {
        global $_W,$_GPC;
        $reportrow=pdo_get('yhzc_crowd_report', array('id' => $_GPC['oid']));
        if(empty($reportrow)){
            message('支付订单不存在');
        }
        if($reportrow['user_id']!=$_W['member']['uid'] || $reportrow['uniacid']!=$_W['uniacid'] || $reportrow['status']!=0){
            message('支付订单不合法');
        }
        $fee = floatval($reportrow['money']);
        $params=array();
        if($fee <= 0) {
            message('支付错误, 金额小于0');
        }
        $params['tid'] = $reportrow['id'];
        $params['ordersn'] = 'O'.date('YmdHis',$reportrow['addtime']).$reportrow['id'];
        $params['fee'] = $fee;
        $params['user'] =  $_W['member']['uid'];
        //调用pay方法
        $this->pay($params);
    }
    public function doMobileP(){
        global $_W,$_GPC;
        $reportrow=pdo_get('yhzc_crowd_report', array('id' => $_GPC['oid']));
        if(empty($reportrow)){
            message('支付订单不存在');
        }
        if($reportrow['user_id']!=$_W['member']['uid'] || $reportrow['uniacid']!=$_W['uniacid'] || $reportrow['status']!=0 || $reportrow['paytype']!='0'){
            message('支付订单不合法');
        }
        $fee = floatval($reportrow['money']);
        $params=array();
        if($fee <= 0) {
            message('支付错误, 金额小于或等于0');
        }
        $params['tid'] = $reportrow['id'];
        $params['uniacid']=$_W['uniacid'];
        $params['ordersn'] = 'O'.date('YmdHis',$reportrow['addtime']).$reportrow['id'];
        $params['fee'] = $fee;
        $params['user'] =  $_W['member']['uid'];
        load()->model('mc');
        if($_W['member']['credit2']<$params['fee']){
            die(json_encode(array('id'=>-1,'msg'=>'支付失败，余额不足')));
        }else if(mc_credit_update($params['user'], 'credit2', -$params['fee'], array($_W['member']['uid'], '众筹支持使用余额支付','yh_zhongchou'))=='true'){
            $params['result'] = 'success';
            $params['paytime'] =time();
            $params['paytype'] =$reportrow['paytype'];
            $this->payResult($params);
        }else{
            die(json_encode(array('id'=>-1,'msg'=>'支付失败，余额可能不足')));
        };

    }
    public function payResult($params) {
        global $_W;
        if ($params['result'] == 'success') {
            $rptdata=array(
                'paytime'=>$params['paytime'],
                'wx_orderid'=>$params['uniontid']
            );
            pdo_update('yhzc_crowd_report',$rptdata, array('id' => $params['tid']));
            pdo_begin();
            try{
                $logdata=array(
                    'uniacid'=>$params['uniacid'],
                    'result'=>$params['result'],
                    'fee'=>$params['fee'],
                    'uniontid'=>$params['uniontid'],
                    'trade_type'=>$params['trade_type'],
                    'user'=>$params['user'],
                    'follow'=>$params['follow'],
                    'paytime'=>$params['paytime'],
                    'tid'=>$params['tid'],
                    'nfrom'=>'crowd',
                    'allinfo'=>serialize($params)
                );
                pdo_insert('yhzc_paylog',$logdata);
                $reportrow=pdo_get('yhzc_crowd_report', array('id' => $params['tid']));
                if($reportrow['money']!=$params['fee']){
                    throw new Exception('系统异常，请联系管理员');
                }
                if(!empty($reportrow)){
                    $upcrowd=pdo_update('yhzc_crowd',array('ppnum +='=>1,'cjmoney +='=>$params['fee']), array('id' => $reportrow['crowd_id']));
                    $crowdrow=pdo_get('yhzc_crowd', array('uniacid' => $reportrow['uniacid'],'id'=>$reportrow['crowd_id']));
                    $rptdata=array(
                        'status'=>1,
                    );
                    $upreport=pdo_update('yhzc_crowd_report',$rptdata, array('id' => $params['tid']));
                    $wxmsg=pdo_get('yhzc_system', array('key' =>'wxmsg','uniacid'=>$params['uniacid']));
                    if(!empty($wxmsg)){
                        $tpl=unserialize($wxmsg['value']);
                    }
                    if($reportrow['reward_id']>0){
                        $upreward=pdo_update('yhzc_crowd_reward',array('reward_count +='=>1), array('id' =>$reportrow['reward_id']));
                        if(empty($upreward)){
                            throw new Exception('系统异常，请联系管理员');
                        }
                        $reward=pdo_get('yhzc_crowd_reward', array('id' => $reportrow['reward_id']));
                        if($reward['reward_count']>$reward['reward_limit'] && $reward['reward_limit']!=0){
                            throw new Exception('此回报项次数不足');
                        }
                    }
                    if($reportrow['union_id']>0){
                        $uniondata=array('ppnum +='=>1,'cjmoney +='=>$params['fee']);
                        $upunion=pdo_update('yhzc_crowd_union',$uniondata, array('id' => $reportrow['union_id']));
                        $union=pdo_fetch("SELECT u.*,m.openid as openid,c.title as title,c.starttime as cstarttime,c.endtime as cendtime FROM ".tablename('yhzc_crowd_union')." as u LEFT JOIN ".tablename('mc_mapping_fans')." as m ON m.uid=u.user_id LEFT JOIN ".tablename('yhzc_crowd')." as c ON c.id=u.crowd_id WHERE u.id = :id and u.uniacid=:uniacid LIMIT 1", array(':id' => $reportrow['union_id'],':uniacid'=>$params['uniacid']));
                        if($union['cjmoney']>$crowdrow['money']){
                            throw new Exception('支持金额超限');
                        }else if($union['cjmoney']==$crowdrow['money']){
                            pdo_update('yhzc_crowd_union',array('endtime'=>time()), array('id' => $reportrow['union_id']));
                            if(isset($tpl['success'])) {
                                $tpldata = array(
                                    'first' => array(
                                        'value' => '您报名的众筹项目已筹款完成',
                                        'color' => '#173177'
                                    ),
                                    'keyword1' => array(
                                        'value' => $union['title']
                                    ),
                                    'keyword2' => array(
                                        'value' => date('Y-m-d H:i:s', $union['cstarttime'])
                                    ),
                                    'keyword3' => array(
                                        'value' => date('Y-m-d H:i:s', $union['cendtime'])
                                    ),
                                    'remark' => array(
                                        'value' => '点击查看详情'
                                    ),
                                );
                                $acc = mc_notice_init();
                                $acc->sendTplNotice($union['openid'], $tpl['success'], $tpldata, $url = $_W['siteroot'] . "app/index.php?i={$params['uniacid']}&c=entry&do=r&m=yh_zhongchou&mc=crowd&mm=uniondetail&union_id={$reportrow['union_id']}", $topcolor = '#FF683F');
                                putmsgs($union['user_id'], '您报名的众筹项目已完成筹款', '<p>众筹标题：' . $union['title'] . '</p>');
                            }
                        }
                        if(isset($tpl['report'])){
                            if($reportrow['nickname']==''){
                                $rnickname='好友';
                            }else{
                                $rnickname=$reportrow['nickname'];
                            }
                            $tpldata=array(
                                'first'=>array(
                                    'value'=>$rnickname.'支持了你参与的众筹项目',
                                    'color'=>'#173177'
                                ),
                                'keyword1'=>array(
                                    'value'=>$union['title']
                                ),
                                'keyword2'=>array(
                                    'value'=>$rnickname
                                ),
                                'keyword3'=>array(
                                    'value'=>$params['fee']
                                ),
                                'keyword4'=>array(
                                    'value'=>date('Y-m-d H:i:s',$reportrow['addtime'])
                                ),
                                'remark'=>array(
                                    'value'=>'点击查看详情'
                                ),
                            );
                            $acc = mc_notice_init();
                            $acc->sendTplNotice($union['openid'], $tpl['report'], $tpldata, $url = $_W['siteroot']."app/index.php?i={$params['uniacid']}&c=entry&do=r&m=yh_zhongchou&mc=crowd&mm=uniondetail&union_id={$reportrow['union_id']}", $topcolor = '#FF683F');
                            putmsgs($union['user_id'],'有人支持了你参与的众筹项目','<p>'.$rnickname.'支持了您参与的众筹项目！</p><p>众筹标题：'.$union['title'].'</p>');
                        }
                    }else{
                        if($crowdrow['cjmoney']>$crowdrow['money'] && $crowdrow['cjtype']==0 && $crowdrow['ntype']=='0'){
                            throw new Exception('支持金额超限');
                        }
                    }
                }
            }catch(Exception $e){
                $msg=$e->getMessage();
                pdo_rollback();
                if(wechatrefund($reportrow)){
                    die(json_encode(array('id'=>-1,'msg'=>$msg.'资金已原路退回')));
                }else{
                    die(json_encode(array('id'=>-1,'msg'=>$msg)));
                }
            }
            pdo_commit();
            tongji(array('report +='=>1,'cjmoney +='=>$params['fee']),$params['uniacid']);
            if($crowdrow['u_id']>0){
                putmsgs($crowdrow['u_id'],'有人支持了您发布的众筹项目','<p>'.$reportrow['nickname'].'支持了您发布的众筹项目！</p><p>众筹标题：'.$crowdrow['title'].'</p>');
            }
            if ($params['from'] == 'return') {
                message('支付成功！',$this->createMobileUrl('r',array('mc'=>'crowd','mm'=>$reportrow['union_id']>0?'uniondetail':'detail',$reportrow['union_id']>0?'union_id':'id'=>$reportrow['union_id']>0?$reportrow['union_id']:$reportrow['crowd_id'])), 'success');
            }else{
                die(json_encode(array('id'=>1,'msg'=>'支付成功')));
            }
        }
    }
}
