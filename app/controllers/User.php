<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class User extends CI_Controller {
    public $w;
    public $user;
    public function __construct(){
        parent::__construct();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        $this->load->model('user_model');
        checkauth();
        $this->user=getuser();
    }
	public function index(){
	    global $_W;
	    $data=array(
	        'title'=>'用户中心',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'styles'=>['pages/user'],
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            ),
            'user'=>$this->user,
            'w'=>$_W
        );
        $this->load->view('user.html',$data);
	}
	public function useredit(){
        global $_W;
        load()->func('tpl');
        $ff=$this->input->get('ff');
        if($this->user['phoneak']=='1' && $this->user['name']!='' && $ff=='vali'){
            $data=array(
                'title'=>'提示',
                'description'=>'完美众筹',
                'keywords'=>'完美众筹',
                'pagename'=>'start',
                'msg'=>'信息已认证',
                'url'=>appurl('user','index'),
                'level'=>''
            );
            $this->load->view('message.html',$data);
            return;
        }
        $data=array(
            'title'=>'我的信息',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'ff'=>$ff==''?'df':$ff,
            'styles'=>['pages/user'],
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            ),
            'user'=>$this->user,
            'w'=>$_W
        );
        $fromurl=$this->input->get('fromurl');
        if($fromurl!=''){
            $data['fromurl']=urldecode($fromurl);
        }
        $this->load->view('user_edit.html',$data);
    }
    public function subedit(){
	    if(!$_POST){
	        exit;
        }
        if($this->user_model->edit_user($this->user)){
	        die(json_encode(array('id'=>1,'msg'=>'success')));
        }else{
            die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
        }
    }
    public function useraddress(){
        global $_W;
        load()->func('tpl');
        $data=array(
            'title'=>'收货地址',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'styles'=>['pages/user'],
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            ),
            'user'=>$this->user,
            'w'=>$_W
        );
        $this->load->view('user_address.html',$data);
    }
    public function order(){
        global $_W;
        $data=array(
            'title'=>'我的订单',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'styles'=>['pages/user_order'],
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            ),
            'rpt'=>pdo_get('yhzc_crowd_report', array('uniacid' =>$_W['uniacid'],'user_id'=>$_W['member']['uid'],'status'=>1), array('sum(money) as ttmoney','count(id) as ttcount')),
            'rptwait'=>pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yhzc_crowd_report')." where user_id={$_W['member']['uid']} and status=1 and reward_status=0")
        );
        $this->load->view('user_order.html',$data);
    }
	public function addresslistjson(){
	    $addlist=$this->user_model->get_address_all();
	    if(!empty($addlist)){
	        $arr['id']=1;
            $arr['data']=$addlist;
        }else{
            $arr['id']=-1;
            $arr['msg']='没有找到';
        }
        die(json_encode($arr));
    }
    public function addressjson(){
        $id=$this->input->get('id');
        $add=$this->user_model->get_address($id);
        if(!empty($add)){
            $arr['id']=1;
            $arr['data']=$add;
            die(json_encode($arr));
        }else{
            $arr['id']=-1;
            $arr['msg']='没有找到';
            die(json_encode($arr));
        }
    }
    public function addaddress(){
        $address_id=$this->input->post('address_id');
        $name=$this->input->post('name');
        $phone=$this->input->post('phone');
        $address=$this->input->post('address');
        $zipcode=$this->input->post('zipcode');
        $other=$this->input->post('other');
        if($name=='' || $phone=='' || $address=='' || $other==''){
            $arr['id']=-1;
            $arr['msg']='请完整填写各个字段';
            die(json_encode($arr));
        }
        if($address_id!=0){
            $var=$this->user_model->edit_address($address_id,$name,$phone,$address,$zipcode,$other);
        }else{
            $var=$this->user_model->add_address($name,$phone,$address,$zipcode,$other);
        }
        if($var){
            $arr['id']=1;
            $arr['msg']='添加成功';
            $arr['data']=array(
                'id'=>$var,
                'name'=>$name,
                'phone'=>$phone,
                'address'=>$address,
                'other'=>$other,
                'zipcode'=>$zipcode
            );
        }else{
            $arr['id']=-1;
            $arr['msg']='添加失败';
        }
        die(json_encode($arr));
    }
    public function deladdress(){
        $id=$this->input->get('id');
        if($this->user_model->del_address($id)){
            $arr['id']=1;
            $arr['msg']='删除成功';
        }else{
            $arr['id']=-1;
            $arr['msg']='删除失败';
        };
        die(json_encode($arr));
    }
    public function myreport(){
        global $_W;
        $where=" r.user_id={$_W['member']['uid']} and r.status<>0";
        $page=$this->input->get('page');
        $limit=$this->input->get('limit');
        if($page==''){
            $page=1;
        }
        if($limit==''){
            $limit=10;
        }
        $reportarr=$this->user_model->getreport_all($where,false,$page==''?1:$page,$limit==''?20:$limit);
        $count=$this->user_model->getreport_count($where,false)['count'];
        if(empty($reportarr)){
            $arr['id']=-1;
            $arr['msg']='没有找到更多啦！';
        }else{
            $arr['id']=1;
            $arr['msg']='success';
            $arr['data']=$reportarr;
            $arr['page']=[ceil($count/$limit),$count];
        }
        die(json_encode($arr));
    }
    public function collect(){
        $data=array(
            'title'=>'我的收藏',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            )
        );
        $this->load->view('user_collect.html',$data);
    }
    public function collectjson(){
        global $_W;
        $where=" u_id={$_W['member']['uid']}";
        $page=$this->input->get('page');
        $limit=$this->input->get('limit');
        if($page==''){
            $page=1;
        }
        if($limit==''){
            $limit=10;
        }
        $collectarr=$this->user_model->getcollect_all($where,array(),$page==''?1:$page,$limit==''?20:$limit);
        if(empty($collectarr)){
            $arr['id']=-1;
            $arr['msg']='没有找到更多啦！';
        }else{
            $arr['id']=1;
            $arr['msg']='success';
            $arr['data']=$collectarr;
        }
        die(json_encode($arr));
    }
    public function delcollect(){
        global $_W;
        $id=$this->input->get('id');
        $u_id=$_W['member']['uid'];
        $uniacid=$_W['uniacid'];
        $result = pdo_delete('yhzc_user_collect', array('uniacid'=>$_W['uniacid'],'u_id'=>$u_id,'id'=>$id));
        if($result){
            $arr['id']=1;
            $arr['msg']='success';
        }else{
            $arr['id']=-1;
        }
        die(json_encode($arr));
    }
    public function help(){
        global $_W;
        $data=array(
            'title'=>'帮助',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            ),
            'category'=>pdo_getall('yhzc_help_category', array('uniacid' => $_W['uniacid']),array() , '',array('sort desc'))
        );
        $this->load->view('user_help.html',$data);
    }
    public function helplistjson(){
        global $_W;
        $cid=$this->input->get('cid');
        $helps=pdo_getall('yhzc_help', array('uniacid' => $_W['uniacid'],'cid'=>$cid),array('id','title','content') , '',array('lasttime desc'),array(0,10));
        if(!empty($helps)){
            $arr['id']=1;
            $arr['data']=$helps;
        }else{
            $arr['id']=-1;
        }
        die(json_encode($arr));
    }
    public function helpjson(){
        $id=$this->input->get('id');
        $help=pdo_get('yhzc_help', array('id'=>$id));
        if(!empty($help)){
            $arr['id']=1;
            $arr['data']=$help;
        }else{
            $arr['id']=-1;
        }
        die(json_encode($arr));
    }
    public function sendsms(){
        global $_W;
        pdo_delete('yhzc_smslog', array('addtime <' =>time()-3600));
        $phone=$this->input->post('phone');
        if($phone=='' || strlen($phone)!=11){
            die(json_encode(array('id'=>-1,'msg'=>'请正确输入手机号码')));
        }
        $code=rand(100000,999999);
        $uid=$_W['member']['uid'];
        $data = array(
            'uniacid' => $_W['uniacid'],
            'uid' =>$uid,
            'code' =>$code,
            'addtime' =>time(),
            'nfrom' =>0
        );
        $hisrow=pdo_get('yhzc_smslog',array('uniacid' => $_W['uniacid'],'uid' =>$uid,'addtime >'=>time()-60));
        if(!empty($hisrow)){
            die(json_encode(array('id'=>-1,'msg'=>'请在'.(60-(time()-$hisrow['addtime'])).'秒后重试')));
        }
        $result = pdo_insert('yhzc_smslog', $data);
        if(!empty($result)){
            if($this->user['phone']=='' || $this->user['phoneak']=='0'){
                $rptuser = pdo_get('yhzc_user', array('uid <>'=>$uid,'phone' =>$phone,'phoneak'=>1,'uniacid'=>$_W['uniacid']));
                if(!empty($rptuser)){
                    die(json_encode(array('id'=>-1,'msg'=>'此号码已被其他人绑定了')));
                }
                $udata=array('phone'=>$phone,'phoneak'=>0);
                pdo_update('yhzc_user',$udata, array('uid' =>$uid,'uniacid'=>$_W['uniacid']));
            }
            $this->load->helper(array('sms_helper'));
            $ct=ali_sendSms($phone,array('code'=>$code),time().rand(100,999));
            if($ct->Message=='OK' && $ct->Code=='OK'){
                die(json_encode(array('id'=>1,'msg'=>'ok')));
            }
        }
        die(json_encode(array('id'=>-1,'msg'=>'验证码无法发送，请稍后再试')));
    }
    public function msg(){
        $data=array(
            'title'=>'消息列表',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'scripts'=>[],
            '_share'=>array(
                'title'=>'',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            )
        );
        $this->load->view('user_msg.html',$data);
    }
    public function msgjson(){
        global $_W;
        $where=" uid={$_W['member']['uid']}";
        $page=$this->input->get('page');
        $limit=$this->input->get('limit');
        if($page==''){
            $page=1;
        }
        if($limit==''){
            $limit=10;
        }
        $msgtarr=$this->user_model->getmsg_all($where,array(),$page==''?1:$page,$limit==''?20:$limit);
        if(empty($msgtarr)){
            $arr['id']=-1;
            $arr['msg']='没有找到更多啦！';
        }else{
            $arr['id']=1;
            $arr['msg']='success';
            $arr['data']=$msgtarr;
        }
        die(json_encode($arr));
    }
    public function getmsg(){
        global $_W;
        $id=$this->input->get('id');
        $data = array(
            'status' => 1,
        );
        $result = pdo_update('yhzc_msg', $data, array('id' =>$id,'uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']));
        if (!empty($result)) {
           die(json_encode(array('id'=>1)));
        }
    }
}
