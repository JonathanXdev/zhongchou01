<?php
class Manage_model extends CI_Model {
    public function __construct(){
        //$this->load->database();
    }
    public function getmanage_row($id){
        global $_W;
        return pdo_fetch("SELECT m.*,r.role_name,r.role,g.group_name FROM ".tablename('yhzc_manage')." as m LEFT JOIN ".tablename('yhzc_manage_role')." as r ON m.role_id=r.role_id LEFT JOIN ".tablename('yhzc_manage_group')." as g ON m.group_id=g.group_id WHERE m.id = :id and m.uniacid=:uniacid LIMIT 1", array(':id' => $id,':uniacid'=>$_W['uniacid']));
    }
    public function getmanage_all($page=0,$limit=10,$key=false){
        global $_W;
        $where="where m.uniacid=:uniacid";
        $wheres[':uniacid']=$_W['uniacid'];
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        if($key!=''){
            $where.=" and m.name like %:key%";
            $wheres[':key']=$key;
        }
        return pdo_fetchall("SELECT m.*,r.role_name,r.role,g.group_name FROM ".tablename('yhzc_manage')." as m LEFT JOIN ".tablename('yhzc_manage_role')." as r ON m.role_id=r.role_id LEFT JOIN ".tablename('yhzc_manage_group')." as g ON m.group_id=g.group_id ".$where." order by m.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getmanage_count($w='',$ws=''){
        global $_W;
        $where="where uniacid=:uniacid";
        if($ws!=''){
            $wheres=$ws;
        }
        if($w!=''){
            $where.=$w;
        }
        $wheres[':uniacid']=$_W['uniacid'];
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_manage')." ".$where,$wheres);
    }
    public function add_manage(){
        global $_W;
        $time=time();
        $id=$this->input->post('id');
        $role_id=$this->input->post('role_id');
        $user_id=$this->input->post('user_id');
        $group_id=$this->input->post('group_id');
        $name=$this->input->post('name');
        $phone=$this->input->post('phone');
        $email=$this->input->post('email');
        $qq=$this->input->post('qq');
        if($role_id=='' || $user_id=='' || $group_id==''|| $name==''){
            die(json_encode(array('id'=>-1,'msg'=>'请完整提交信息')));
        }
        $managerow=array();
        if($id>0){
            $managerow=$this->getmanage_row($id);
        }
        if(empty($managerow)){
            $rptrow=pdo_fetch("SELECT id FROM ".tablename('yhzc_manage')." WHERE user_id = :user_id and uniacid=:uniacid LIMIT 1", array(':user_id' => $user_id,':uniacid'=>$_W['uniacid']));
            if(!empty($rptrow)){
                die(json_encode(array('id'=>-1,'msg'=>'此用户以存在于管理员列表')));
            }
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'role_id'=>$role_id,
            'group_id'=>$group_id,
            'user_id'=>$user_id,
            'name'=>$name,
            'phone'=>$phone,
            'email'=>$email,
            'qq'=>$qq,
            'addtime'=>$time,
            'lasttime'=>$time
        );
        if(!empty($managerow)){
            $result = pdo_update('yhzc_manage', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
            if (!empty($result)) {
                return $id;
            }
        }else{
            $result = pdo_insert('yhzc_manage', $data);
            if (!empty($result)) {
                $id = pdo_insertid();
                return $id;
            }
        }
    }
    public function getrole(){
        global $_W;
        return pdo_getall('yhzc_manage_role', array('uniacid' => $_W['uniacid']),array() , '');
    }
    public function getgroup(){
        global $_W;
        return pdo_getall('yhzc_manage_group', array('uniacid' => $_W['uniacid']),array() , '');
    }
    public function getrole_row($where=false){
        global $_W;
        $where['uniacid']=$_W['uniacid'];
        return pdo_get('yhzc_manage_role', $where);
    }
    public function getgroup_row($where=false){
        global $_W;
        $where['uniacid']=$_W['uniacid'];
        return pdo_get('yhzc_manage_group', $where);
    }
	public function editrole(){
        global $_W;
	    $name=$this->input->post('name');
        $value=$this->input->post('value');
        if($name=='role'){
            $rolestr='';
            foreach($value as $k=>$r){
                if($k==0){
                    $rolestr.=$r;
                }else{
                    $rolestr.=','.$r;
                }
            }
            $value=$rolestr;
        }
        $id=$this->input->post('pk');
        $data = array(
            $name => $value,
            'uniacid'=>$_W['uniacid']
        );
        $result = pdo_update('yhzc_manage_role', $data, array('role_id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function editgroup(){
        global $_W;
        $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $data = array(
            $name => $value,
            'uniacid'=>$_W['uniacid']
        );
        $result = pdo_update('yhzc_manage_group', $data, array('group_id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addrole(){
        global $_W;
        $role_name=$this->input->post('role_name');
        $role_info=$this->input->post('role_info');
        $role=$this->input->post('role');
        if($role_name==''){
            die(json_encode(array('id'=>-1,'msg'=>'角色名不能为空')));
        }
        if($role==''){
            die(json_encode(array('id'=>-1,'msg'=>'请至少选择一个权限')));
        }
        $rptrow=$this->getrole_row(array('role_name'=>$role_name));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'该角色名称已经存在')));
        }
        $rolestr='';
        foreach($role as $k=>$r){
            if($k==0){
                $rolestr.=$r;
            }else{
                $rolestr.=','.$r;
            }
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'role_name' => $role_name,
            'role' =>$rolestr,
            'role_info' =>$role_info,
        );
        $result = pdo_insert('yhzc_manage_role', $data);
        if (!empty($result)) {
           // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加角色成功')));
        }
    }
    public function addgroup(){
        global $_W;
        $group_name=$this->input->post('group_name');
        $group_info=$this->input->post('group_info');
        if($group_name==''){
            die(json_encode(array('id'=>-1,'msg'=>'部门名不能为空')));
        }
        $rptrow=$this->getgroup_row(array('group_name'=>$group_name));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'该部门名称已经存在')));
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'group_name' => $group_name,
            'group_info' =>$group_info,
        );
        $result = pdo_insert('yhzc_manage_group', $data);
        if (!empty($result)) {
            // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加部门成功')));
        }
    }
    public function delmanage($id=0){
        global $_W;
        $result = pdo_delete('yhzc_manage', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
    public function delrole($id=0){
        global $_W;
        $result = pdo_delete('yhzc_manage_role', array('role_id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
    public function delgroup($id=0){
        global $_W;
        $result = pdo_delete('yhzc_manage_group', array('group_id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }

}