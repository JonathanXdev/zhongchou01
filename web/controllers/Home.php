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

    }
	public function index()
	{
	    global $_W;
        checklogin();
        $ttime=strtotime(date('Y-m-d'));
        $time=time();
	    $data=array(
            'w'=>$this->w,
	        'title'=>'一点众筹',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'home',
            'styles'=>array(array('value'=>'plugins/switchery/switchery.css')),
            'scripts'=>array(array('value'=>'js/pages/dashboard.js')),
            'alltongji'=>@pdo_getall('yhzc_statistics', array('uniacid'=>$_W['uniacid']), array() , '' , array('addtime desc') , array(0,7)),
            'crowd_num'=>@pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yhzc_crowd')." where starttime<{$time} and endtime>={$time} and status=1 and uniacid={$_W['uniacid']}")
        );

	    if(!empty($data['alltongji'])){
	        $data['alltongji']=array_reverse($data['alltongji']);
        }else{
            $data['alltongji']=array();
        }
        foreach($data['alltongji'] as $t){
	        if($t['addtime']==strtotime('yesterday')){
	            $data['ydtongji']=$t;
            }else if($t['addtime']==$ttime){
	            $data['tdtongji']=$t;
            }
        }
        $this->parser->parse('framework.html',$data);
	}
}
