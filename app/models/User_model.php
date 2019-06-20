<?php
class User_model extends CI_Model {
    public function __construct(){
        //$this->load->database();
    }
    public function get_user(){
        global $_W;
        return pdo_get('mc_members', array('uid' => $_W['member']['uid'],'uniacid' => $_W['uniacid']));
    }
    public function edit_user($user){
        global $_W;
        $name=$this->input->post('name');
        $phone=$this->input->post('phone');
        $avatar=$this->input->post('avatar');
        $data=array();
        if($name!=''){
            $data['name']=$name;
        }
        if($avatar!=''){
            $data['avatar']=$avatar;
        }
        if($phone!=''){
            $code=$this->input->post('code',true);
            if($code==''){
                die(json_encode(array('id'=>-1,'msg'=>'请正确输入验证码')));
            }
            $coderow=pdo_get('yhzc_smslog',array('code'=>$code,'uniacid'=>$_W['uniacid'],'uid'=>$_W['member']['uid'],'nfrom'=>0));
            if(empty($coderow)){
                die(json_encode(array('id'=>-1,'msg'=>'请正确输入验证码')));
            }else{
                if(time()-$coderow['addtime']>300){
                    die(json_encode(array('id'=>-1,'msg'=>'验证码已超时，请重新获取')));
                }
                if($user['phone']!=$phone){
                    die(json_encode(array('id'=>-1,'msg'=>'请使用新输入的手机号接收验证码')));
                }
            }
            if($user['phoneak']=='1'){
                $data['phoneak']=0;
            }else{
                $data['phoneak']=1;
            }
        }
        $result = pdo_update('yhzc_user', $data, array('uid' =>$_W['member']['uid'],'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            return true;
        }else{
            return false;
        }
    }
    public function get_address_all(){
        global $_W;
        return pdo_getall('yhzc_user_address', array('uniacid' => $_W['uniacid'],'user_id'=>$_W['member']['uid']));
    }
    public function get_address($id){
        global $_W;
        return pdo_get('yhzc_user_address', array('id' => $id,'user_id'=>$_W['member']['uid'],'uniacid' => $_W['uniacid']));
    }
    public function edit_address($address_id,$name,$phone,$address,$zipcode,$other){
        global $_W;
        $data = array(
            'name'=>$name,
            'phone'=>$phone,
            'country'=>'中国',
            'province'=>$address['province'],
            'city'=>$address['city'],
            'district'=>$address['district'],
            'other'=>$other,
            'zipcode'=>$zipcode,
        );
        $result = pdo_update('yhzc_user_address', $data, array('id' =>$address_id,'user_id'=>$_W['member']['uid']));
        if (!empty($result)) {
            return $address_id;
        }else{
            return false;
        }
    }
    public function add_address($name,$phone,$address,$zipcode,$other){
        global $_W;
        $data = array(
            'uniacid' => $_W['uniacid'],
            'user_id'=>$_W['member']['uid'],
            'name'=>$name,
            'phone'=>$phone,
            'country'=>'中国',
            'province'=>$address['province'],
            'city'=>$address['city'],
            'district'=>$address['district'],
            'other'=>$other,
            'zipcode'=>$zipcode
        );
        $result = pdo_insert('yhzc_user_address', $data);

        if (!empty($result)) {
            $id = pdo_insertid();
            return $id;
        }else{
            return false;
        }
    }
    public function del_address($id){
        global $_W;
        $result = pdo_delete('yhzc_user_address', array('id' => $id,'uniacid'=>$_W['uniacid'],'user_id'=>$_W['member']['uid']));
        if (!empty($result)) {
            return true;
        }else{
            return false;
        }
    }
    public function getreport_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where r.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetchall("SELECT r.*,from_unixtime(r.addtime) as uaddtime,from_unixtime(r.paytime) as upaytime,c.title as ctitle,u.nickname as unickname FROM ".tablename('yhzc_crowd_report')." as r LEFT JOIN ".tablename('yhzc_crowd')." as c ON c.id=r.crowd_id LEFT JOIN ".tablename('yhzc_crowd_union')." as u ON u.id=r.union_id {$where} order by r.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getreport_count($where='',$wheres=''){
        global $_W;
        if($where!=''){
            $where="where r.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetch("SELECT count(r.id) as count FROM ".tablename('yhzc_crowd_report')." as r ".$where,$wheres);
    }
    public function getcollect_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where uniacid={$_W['uniacid']} and {$where}";
        }else{
            exit;
        }
        return pdo_fetchall("SELECT *,from_unixtime(addtime) as uaddtime FROM ".tablename('yhzc_user_collect')." {$where} order by addtime desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getmsg_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where uniacid={$_W['uniacid']} and {$where}";
        }else{
            exit;
        }
        return pdo_fetchall("SELECT *,from_unixtime(addtime) as uaddtime FROM ".tablename('yhzc_msg')." {$where} order by addtime desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
}