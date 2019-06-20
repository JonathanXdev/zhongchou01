<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends CI_Controller {
    public $w;
    public function __construct(){
        parent::__construct();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        //$this->load->model('goodluck_model');
        checkauth();
    }
	public function index()
	{
        $this->load->model('system_model');
        $sys=mysys(['homenav','face','syslogo','sysname']);
	    global $_W;
	    $data=array(
            'w'=>$this->w,
	        'title'=>'众筹首页',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'styles'=>['pages/home'],
            'navarr'=>$sys['homenav']?unserialize($sys['homenav']):'',
            'facearr'=>$sys['face']?json_decode($sys['face'],true):'',
            'scripts'=>[],
            '_share'=>array(
                'title'=>@$sys['sysname']==''?'众筹首页':$sys['sysname'],
                'imgUrl'=>@$sys['syslogo']==''?'':tomedia($sys['syslogo']),
                'desc'=>'',
                'link'=>''
            ),
            'notice'=>$this->system_model->getnotice(1,6)
        );
        $this->load->view('home.html',$data);
	}
	public function getnotice(){
        global $_W;
	   $id=$this->input->get('id');
	   $notice=pdo_get('yhzc_sys_notice', array('id' => $id,'uniacid'=>$_W['uniacid']),array('*','from_unixtime(addtime) as uaddtime'));
	   if(!empty($notice)){
	       $arr['id']=1;
           $arr['data']=$notice;
       }else{
           $arr['id']=-1;
       }
       die(json_encode($arr));
    }
    public function about(){
        global $_W;
        $data=array(
            'w'=>$this->w,
            'title'=>'关于我们',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'styles'=>['pages/about'],
            'scripts'=>[],
            '_share'=>array(
                'title'=>'众筹首页',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            )
        );
        $this->load->view('about.html',$data);
    }
}
