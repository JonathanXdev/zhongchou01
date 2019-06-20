<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class System extends CI_Controller {
    public $w;
    public function __construct(){
        parent::__construct();
        checklogin();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        $this->load->model('system_model');
    }
	public function set(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $system=mysys(['about','phone','qq','refund','risk','statcode','syslogo','sysname','website','weixin','wxmsg','smscode','alismscfg','wxpay','cmt','cashpay']);

        $md=$this->input->get('mf');
	    $data=array(
	        'title'=>'系统设置|完美众筹',
            'description'=>'完美众筹',
            'keywords'=>'完美众筹',
            'w'=>$this->w,
            'pagename'=>'system_set',
            'mf'=>$md==''?'df':$md,
            'styles'=>array(array('value'=>'plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')),
            'scripts'=>array(array('value'=>'js/pages/system_set.js'),array('value'=>'plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js')),
            'system'=>$system
        );
        $this->load->view('framework.html',$data);
	}
	public function subsystem(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
	    $upnum=$this->system_model->editsys();
	    if($upnum>0){
            die(json_encode(array('id'=>1,'msg'=>'操作成功，'.$upnum.'条数据被修改')));
        }else{
            die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
        };
    }
    public function wxmsg(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        if($this->system_model->editwxmsg()){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        };
    }
    public function wxpay(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        if($this->system_model->editwxpay()){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        };
    }
    public function cmt(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        if($this->system_model->editcmt()){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        };
    }
    public function wxslide(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $var=$this->system_model->editface();
        if($var){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        }else{
            die(json_encode(array('id'=>-1,'msg'=>'没有任何修改')));
        }
    }
    public function smscode(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        if($this->system_model->editsmscode()){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        };
    }
    public function smscfg(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        if($this->system_model->editsmscfg()){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        };
    }
    public function notice(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $data=array(
            'title'=>'系统公告|完美众筹',
            'description'=>'完美众筹',
            'keywords'=>'完美众筹',
            'w'=>$this->w,
            'pagename'=>'system_notice',
            'styles'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css')),
            'scripts'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js'),array('value'=>'plugins/uniform/jquery.uniform.min.js'),array('value'=>'js/pages/system_notice.js')),
            'notice'=>$this->system_model->getnotice()
        );
        $this->load->view('framework.html',$data);
    }
    public function delnotice(){
        if(!checkrole('system','notice')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->get('id');
        if($id>0){
            if($this->system_model->delnotice($id)){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            };
        }
    }
    public function editnotice(){
        if(!checkrole('system','notice')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('pk');
        if($id>0){
            $this->system_model->editnotice();
        }else{
            $this->system_model->addnotice();
        }
    }
    public function delrule(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->get('id');
        if($id>0){
            if($this->system_model->delrule($id)){
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            };
        }
    }
    public function editrule(){
        if(!checkrole('system','set')){
            die(json_encode(array('id'=>-1,'msg'=>'无权限')));
        }
        $id=$this->input->post('pk');
        if($id>0){
            $this->system_model->editrule();
        }else{
            $this->system_model->addrule();
        }
    }
    public function rule(){
        if(!checkrole('system','set')){
            echo '无权限';
            exit;
        }
        $data=array(
            'title'=>'入口设置',
            'description'=>'完美众筹',
            'keywords'=>'完美众筹',
            'w'=>$this->w,
            'pagename'=>'system_rule',
            'styles'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css')),
            'scripts'=>array(array('value'=>'plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js'),array('value'=>'plugins/uniform/jquery.uniform.min.js'),array('value'=>'js/pages/system_rule.js')),
            'rule'=>$this->system_model->getrule()
        );
        $this->load->view('framework.html',$data);
    }
    public function rulecode(){
        $str=$this->input->get('str');
        if($str==''){
            echo '';
            exit;
        }
        $this->load->library('phpqrcode');
        $value = urldecode($str);                  //二维码内容
        $errorCorrectionLevel = 'L';    //容错级别
        $matrixPointSize = 5;           //生成图片大小
        $this->phpqrcode->png($value,false,$errorCorrectionLevel, $matrixPointSize, 2);

    }
    public function layout(){
        if(!checkrole()){
            echo '无权限';
            exit;
        }
        $this->load->model('crowd_model');
        $system=mysys(['face','homenav']);
        $md=$this->input->get('mf');
        $crowdcate=$this->crowd_model->getcategory();
        $data=array(
            'title'=>'个性化设置|完美众筹',
            'description'=>'完美众筹',
            'keywords'=>'完美众筹',
            'w'=>$this->w,
            'pagename'=>'system_layout',
            'mf'=>$md==''?'slide':$md,
            'styles'=>array(array('value'=>'plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')),
            'scripts'=>array(array('value'=>'js/pages/system_set.js'),array('value'=>'plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js')),
            'system'=>$system,
            'crowdcate'=>$crowdcate
        );
        $this->load->view('framework.html',$data);
    }
    public function rmhomenav(){
        global $_W;
        $keys=$this->input->get('keys',true);
        $homenav=mysys('homenav');
        $homenav=unserialize($homenav);
        if($keys!=''){
            unset($homenav[$keys]);
            pdo_delete('yhzc_system', array('uniacid' => $_W['uniacid'],'key'=>'homenav'));
        }
        $homenavvar=serialize($homenav);
        if(pdo_insert('yhzc_system', array('key'=>'homenav','value'=>$homenavvar,'uniacid'=>$_W['uniacid'],'ntype'=>1))){
            die(json_encode(array('id'=>1,'msg'=>'操作成功')));
        };
    }
    public function addhomenav(){
        global $_W;
        $homenav=mysys('homenav');
        $keys=$this->input->get('keys',true);
        $icon=$this->input->post('seticon',true);
        $link=$this->input->post('setlink',true);
        $title=$this->input->post('settitle',true);
        $sort=$this->input->post('setsort',true);
        if($sort==''){
            $sort=0;
        }
        if($icon=='' || $link=='' || $title==''){
            die(json_encode(array('id'=>-1,'msg'=>'请确保各项已填写')));
        }
        if(!empty($homenav)){
            $homenav=unserialize($homenav);
            if($keys!=''){
                unset($homenav[$keys]);
            }
            pdo_delete('yhzc_system', array('uniacid' => $_W['uniacid'],'key'=>'homenav'));
        }else{
            $homenav=array();
        }
        $homenav[]=array('icon'=>$icon,'link'=>$link,'title'=>$title,'sort'=>$sort);
        foreach ($homenav as $key => $data){
            $iconarr[$key] = $data['icon'];
            $linkarr[$key] = $data['link'];
            $titlearr[$key] = $data['title'];
            $sortarr[$key] = $data['sort'];
        }
        array_multisort($sortarr, SORT_DESC, SORT_NUMERIC , $homenav);
        $homenavvar=serialize($homenav);
        if(pdo_insert('yhzc_system', array('key'=>'homenav','value'=>$homenavvar,'uniacid'=>$_W['uniacid'],'ntype'=>1))){
            die(json_encode(array('id'=>1,'msg'=>'提交成功')));
        };
    }
    public function syslog(){

    }
    public function syslog_clean(){

    }
}
