<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Manage extends CI_Controller {
    public $w;
    public function __construct(){
        parent::__construct();
        checklogin();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        $this->load->model('manage_model');
    }
	public function mlist(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $page=$this->input->get('page');
        $limit=$this->input->get('limit');
        $key=$this->input->get('key');
        $where='';
        $wheres=array();
        if($key!=''){
            $where=" name like %:key%";
            $wheres[':key']=$key;
        }
	    $data=array(
	        'title'=>'管理员列表',
            'description'=>'管理员列表',
            'keywords'=>'管理员列表',
            'w'=>$this->w,
            'pagename'=>'manage_list',
            'styles'=>array(array('value'=>'plugins/datatables/css/jquery.datatables.min.css')),
            'scripts'=>array(array('value'=>'plugins/datatables/js/jquery.datatables.min.js'),array('value'=>'js/pages/manage.js')),
            'manage'=>$this->manage_model->getmanage_all($page,$limit,$key),
            'pagination'=>[$this->manage_model->getmanage_count($where,$wheres)['count'],$page?$page:1,$limit?$limit:10],
            'role'=>$this->manage_model->getrole(),
            'group'=>$this->manage_model->getgroup()
        );
	    //print_r(pdo_debug());
        $this->load->view('framework.html',$data);
	}
    public function submanage(){
        if(!checkrole()){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('id');
        $manage_id=$this->manage_model->add_manage();
        if($manage_id>0){
            $arr=array('id'=>1,'msg'=>'成功');
            die(json_encode($arr));
        }
    }
    public function delmanage(){
        if(!checkrole('manage','submanage')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('id');
        if($id>0){
            if($this->manage_model->delmanage($id)){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            };
        }
    }
    public function searchuser(){
        //借权检查及继承权限
        if(!checkrole('manage','submanage')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $keys=$this->input->post('key');
        global $_W;
        $user=pdo_fetch("SELECT u.uid,u.username,g.name FROM ".tablename('users')." as u LEFT JOIN ".tablename('users_group')." as g ON u.groupid=g.id WHERE u.uid = :keys or u.username=:keys LIMIT 1", array(':keys' => $keys));
        if(!empty($user)){
            $arr=array('id'=>1,'msg'=>'成功','data'=>$user);
        }else{
            $arr=array('id'=>-1,'msg'=>'没有找到管理员');
        }
        die(json_encode($arr));
    }
    public function role(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $data=array(
            'title'=>'角色管理',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'w'=>$this->w,
            'pagename'=>'manage_role',
            'styles'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css')),
            'scripts'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js'),array('value'=>'plugins/uniform/jquery.uniform.min.js'),array('value'=>'js/pages/manage_role.js')),
            'role'=>$this->manage_model->getrole(),
            'role_menu'=>getrole()
        );
        $this->load->view('framework.html',$data);
    }
    public function group(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $data=array(
            'title'=>'部门管理',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'w'=>$this->w,
            'pagename'=>'manage_group',
            'styles'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css')),
            'scripts'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js'),array('value'=>'plugins/uniform/jquery.uniform.min.js'),array('value'=>'js/pages/manage_group.js')),
            'group'=>$this->manage_model->getgroup()
        );
        $this->load->view('framework.html',$data);
    }
    public function delrole(){
        //借权检查及继承权限
        if(!checkrole('manage','role')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->get('id');
        if($id>0){
            if($this->manage_model->delrole($id)){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            };
        }
    }
    public function delgroup(){
        //借权检查及继承权限
        if(!checkrole('manage','group')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->get('id');
        if($id>0){
            if($this->manage_model->delgroup($id)){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            };
        }
    }
    public function editrole(){
        //借权检查及继承权限
        if(!checkrole('manage','role')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('pk');
        if($id>0){
            $this->manage_model->editrole();
        }else{
            $this->manage_model->addrole();
        }
    }
    public function editgroup(){
        //借权检查及继承权限
        if(!checkrole('manage','group')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('pk');
        if($id>0){
            $this->manage_model->editgroup();
        }else{
            $this->manage_model->addgroup();
        }
    }
}
