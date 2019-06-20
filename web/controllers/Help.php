<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Help extends CI_Controller {
    public $w;
    public function __construct(){
        parent::__construct();
        checklogin();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        $this->load->model('help_model');
    }
	public function article(){
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
            $where=" title like %:key%";
            $wheres[':key']=$key;
        }
	    $data=array(
	        'title'=>'帮助文章列表',
            'description'=>'帮助文章列表',
            'keywords'=>'帮助文章列表',
            'w'=>$this->w,
            'pagename'=>'help_list',
            'styles'=>array(array('value'=>'plugins/datatables/css/jquery.datatables.min.css')),
            'scripts'=>array(array('value'=>'plugins/datatables/js/jquery.datatables.min.js'),array('value'=>'js/pages/help.js')),
            'help'=>$this->help_model->gethelp_all($page,$limit,$key),
            'pagination'=>[$this->help_model->gethelp_count($where,$wheres)['count'],$page?$page:1,$limit?$limit:10],
            'category'=>$this->help_model->getcategory(),
        );
	    //print_r(pdo_debug());
        $this->load->view('framework.html',$data);
	}
	public function edithelp(){
	    $id=$this->input->get('id');
	    if($_POST){
	        $vars=$this->help_model->help_edit($id);
	        if($vars){
	            die(json_encode(array('id'=>1,'msg'=>'编辑成功','data'=>$vars)));
            }
	        exit;
        }
        $data=array(
            'title'=>'帮助文章编辑',
            'description'=>'帮助文章编辑',
            'keywords'=>'帮助文章编辑',
            'w'=>$this->w,
            'pagename'=>'help_edit',
            'styles'=>array(),
            'scripts'=>array(array('value'=>'js/pages/help.js')),
            'help'=>$this->help_model->gethelp_row($id),
            'category'=>$this->help_model->getcategory(),
        );
        $this->load->view('framework.html',$data);
    }
    public function delhelp(){
        $id=$this->input->get('id');
        if($id>0){
            $vars=$this->help_model->delhelp($id);
            if($vars){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            }
        }
    }
    public function category(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $data=array(
            'title'=>'帮助分类',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'w'=>$this->w,
            'pagename'=>'help_category',
            'styles'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css')),
            'scripts'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js'),array('value'=>'plugins/uniform/jquery.uniform.min.js'),array('value'=>'js/pages/help_category.js')),
            'category'=>$this->help_model->getcategory()
        );
        $this->load->view('framework.html',$data);
    }
    public function delcategory(){
        //借权检查及继承权限
        if(!checkrole('help','category')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->get('id');
        if($id>0){
            if($this->help_model->delcategory($id)){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            };
        }
    }
    public function editcategory(){
        //借权检查及继承权限
        if(!checkrole('help','category')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('pk');
        if($id>0){
            $this->help_model->editcategory();
        }else{
            $this->help_model->addcategory();
        }
    }
}
