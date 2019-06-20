<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Notice extends CI_Controller {
    public $w;
    public function __construct(){
        parent::__construct();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        $this->load->model('system_model');
        checkauth();
    }

	public function view(){
        global $_W;
	   $id=$this->input->get('id');
	   $notice=$this->system_model->getnotice_row(array('id'=>$id));
	   if(!empty($notice)){
           $data=array(
               'w'=>$this->w,
               'title'=>$notice['title'],
               'description'=>'',
               'keywords'=>'',
               'styles'=>[],
               'scripts'=>[],
               'notice'=>$notice,
               '_share'=>array(
                   'title'=>'',
                   'imgUrl'=>'',
                   'desc'=>'',
                   'link'=>''
               )
           );
           $this->load->view('notice.html',$data);
       }else{
           exit;
       }
    }

}
