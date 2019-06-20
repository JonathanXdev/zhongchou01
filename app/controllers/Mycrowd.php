<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mycrowd extends CI_Controller {
    public $w;
    public $user;
    public function __construct(){
        parent::__construct();
        global $_W;
        $this->w=$_W;
        //$this->load->library('pagination');
        $this->load->library('parser');
        $this->load->helper(array('url_helper','file','form'));
        $this->load->model('crowd_model');
        checkauth();
        $this->user=getuser();
    }
	public function start(){
	    global $_W;
	    $step=$this->input->get('step');
        $step=$step==''?1:$step;
        $id=$this->input->get('id');
        $crowdcache=[];
        if($this->user['phoneak']!='1'){
            $data=array(
                'title'=>'提示',
                'description'=>'完美众筹',
                'keywords'=>'完美众筹',
                'pagename'=>'start',
                'msg'=>'请点击确定，完善资料后再来提交！',
                'url'=>appurl('user','useredit','ff=vali&fromurl='.urlencode(curpageurl())),
                'level'=>'error '
            );
            $this->load->view('message.html',$data);
            return;
        }
	    if($id>0){
            $crowdcache= pdo_get('yhzc_crowd_cache', array('id' => $id,'uniacid'=>$_W['uniacid'],'u_id'=>$_W['member']['uid']));
            if(!empty($crowdcache)) {
                if($crowdcache['crowd_id']!=0){
                    $crowd = $this->crowd_model->getcrowd_row($crowdcache['crowd_id']);
                    if (!empty($crowd)) {
                        echo '众筹已审核通过，如需修改，请联合客服！';
                        exit;
                    }
                }
                if($crowdcache['status']>0){
                    echo '众筹正在审核状态，无法编辑，请撤回后再编辑！';
                    exit;
                }
                if ($crowdcache['default_info'] == '' && $step>1) {
                    $step = 1;
                } else if ($crowdcache['crowd_info'] == '' && $step>2) {
                    $step = 2;
                } else if ($crowdcache['crowd_content'] == '' && $step>4) {
                    $step = 4;
                } else if ($crowdcache['crowd_reward'] == '' && $crowdcache['ntype'] == 0 && $step>3) {
                    $step = 3;
                }
            }
        }
	    $data=array(
	        'title'=>'发起众筹',
            'description'=>'',
            'keywords'=>'',
            'pagename'=>'start',
            'styles'=>['mui.picker.min','pages/my_crowd'],
            'scripts'=>['plugin/mui.picker.min','plugin/editor/Eleditor'],
            'crowd'=>$crowdcache,
            'step'=>$step,
            '_share'=>array(
                'title'=>'我的众筹',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            )
        );
        if($step==1){
            $catearr=$this->crowd_model->getcategory();
            $ckv=[];
            $category='[';
            if(!empty($catearr)){
                foreach($catearr as $kt=>$ct){
                    $ckv[$ct['id']]=$ct['name'];
                    if($kt==0){
                        $category.='{value:\''.$ct['id'].'\',text:\''.$ct['name'].'\'}';
                    }else{
                        $category.=',{value:\''.$ct['id'].'\',text:\''.$ct['name'].'\'}';
                    }
                };
            }
            $category.=']';
            $data['category']=$category;
            $data['ckv']=$ckv;
        }
        $this->load->view('crowd_start.html',$data);
	}
	public function sub(){
	    global $_W;
	    if($this->user['phoneak']!='1' || $this->user['name']=='' ){
            die(json_encode(array('id'=>-1,'msg'=>'请先进入用户中心完善个人信息')));
        }
	    $step=$this->input->get('step');
        $id=$this->input->get('id');
        if($id>0){
            $crowdcache= pdo_get('yhzc_crowd_cache', array('id' => $id,'uniacid'=>$_W['uniacid'],'u_id'=>$_W['member']['uid']));
            if(!empty($crowdcache)){
                if($crowdcache['status']>0){
                    die(json_encode(array('id'=>-1,'msg'=>'众筹正在审核或已审核通过，无法编辑')));
                }
                if($crowdcache['status']==-1){
                    die(json_encode(array('id'=>-1,'msg'=>'此众筹已审核失败，无法编辑')));
                }
            }
        }
        switch ($step)
        {
            case 1:
                $cid=$this->input->post('cid',true);
                $ntype=$this->input->post('ntype',true);
                $nfrom=$this->input->post('nfrom',true);
                $authname=$this->input->post('authname',true);
                $authimg=$this->input->post('authimg',true);
                $city=$this->input->post('city',true);
                if($cid=='' || $ntype=='' || $nfrom=='' || empty($authname) || $city==''){
                    $arr['id']=-1;
                    if($cid==''){
                        $arr['msg']='请选择类目';
                    }else if($ntype==''){
                        $arr['msg']='请选择类型';
                    }else if($nfrom==''){
                        $arr['msg']='请选择身份';
                    }else if($city==''){
                        $arr['msg']='请选择城市';
                    }else{
                        $arr['msg']='请上传资质或证明';
                    }
                    die(json_encode($arr));
                }
                $autharr=[];
                foreach($authname as $kn=>$an){
                    if($an==''){
                        die(json_encode(array('id'=>-1,'msg'=>'请确保各资质证明名称已填写')));
                    }
                    if($authimg[$kn]==''){
                        die(json_encode(array('id'=>-1,'msg'=>'请确保各资质证明图片已上传')));
                    }
                    $autharr[]=array(
                        'name'=>$an,
                        'img'=>$authimg[$kn],
                        'status'=>0
                    );
                }
                $datas=array(
                    'cid'=>$cid,
                    'ntype'=>$ntype,
                    'nfrom'=>$nfrom,
                    'city'=>$city,
                    'auth'=>json_encode($autharr,JSON_UNESCAPED_UNICODE)
                );
                $data=array(
                    'u_id'=>$_W['member']['uid'],
                    'uniacid'=>$_W['uniacid'],
                    'ntype'=>$ntype,
                    'status'=>0,
                    'default_info'=>json_encode($datas),
                    'addtime'=>time(),
                    'lasttime'=>time()
                );
                if($id>0){
                    $result = pdo_update('yhzc_crowd_cache', $data, array('id' => $id,'u_id'=>$_W['member']['uid'],'uniacid'=>$_W['uniacid']));
                }else{
                    $result = pdo_insert('yhzc_crowd_cache', $data);
                    $id = pdo_insertid();
                }
                if (!empty($result)) {
                    die(json_encode(array('id'=>1,'data'=>array('id'=>$id,'step'=>$step+1),'msg'=>'保存成功')));
                }
                break;
            case 2:
                if(empty($crowdcache)){
                    die(json_encode(array('id'=>-1,'msg'=>'系统错误')));
                }
                $starttime=$this->input->post('starttime',true);
                $endtime=$this->input->post('endtime',true);
                $coverimg=$this->input->post('coverimg',true);
                $title=$this->input->post('title',true);
                $money=$this->input->post('money',true);
                $summary=$this->input->post('summary',true);
                $tags=$this->input->post('tags',true);
                if($starttime=='' || $endtime=='' || empty($coverimg) || $title=='' || $money=='' || $summary=='' || empty($tags)){
                    die(json_encode(array('id'=>-1,'msg'=>'请确保各项内容已填写完整')));
                }
                $datas=array(
                    'starttime'=>strtotime($starttime),
                    'endtime'=>strtotime($endtime),
                    'coverimg'=>json_encode($coverimg,JSON_UNESCAPED_UNICODE),
                    'title'=>$title,
                    'money'=>$money,
                    'summary'=>$summary,
                    'tags'=>json_encode($tags,JSON_UNESCAPED_UNICODE),
                );
                if($datas['starttime']<time()){
                    die(json_encode(array('id'=>-1,'msg'=>'期望开始时间必须大于当前时间')));
                }
                if($datas['endtime']<$datas['starttime']){
                    die(json_encode(array('id'=>-1,'msg'=>'期望结束时间必须大于开始时间')));
                }
                $data=array(
                    'crowd_info'=>json_encode($datas),
                    'lasttime'=>time()
                );
                $result = pdo_update('yhzc_crowd_cache', $data, array('id' => $id,'u_id'=>$_W['member']['uid'],'uniacid'=>$_W['uniacid']));
                if (!empty($result)) {
                    if($crowdcache['ntype']=='1'){
                        $step=$step+2;
                    }else{
                        $step=$step+1;
                    }
                    die(json_encode(array('id'=>1,'data'=>array('id'=>$id,'step'=>$step),'msg'=>'保存成功')));
                }
                break;
            case 3:
                if(empty($crowdcache)){
                    die(json_encode(array('id'=>-1,'msg'=>'系统错误')));
                }
                $reward_type=$this->input->post('reward_type',true);
                $reward_money=$this->input->post('reward_money',true);
                $reward_title=$this->input->post('reward_title',true);
                $reward_content=$this->input->post('reward_content',true);
                $reward_limit=$this->input->post('reward_limit',true);
                $reward_marknum=$this->input->post('reward_marknum',true);
                $reward_day=$this->input->post('reward_day',true);
                $reward_fd=$this->input->post('reward_fd',true);
                $rewardarr=array();
                foreach($reward_type as $k=>$r){
                    if($reward_money[$k]=='' || $reward_title[$k]=='' || $reward_content[$k]=='' || $reward_limit[$k]=='' || $reward_marknum[$k]=='' || $reward_day[$k]==''){
                        die(json_encode(array('id'=>-1,'msg'=>'请完整填写各回报项条目')));
                    }
                    $fdvar='';
                    if (!empty($reward_fd)) {
                        $fdarr = array();
                        if (is_array(@$reward_fd[$k])) {
                            foreach ($reward_fd[$k] as $rfd) {
                                $fdarr[] = $rfd;
                            }
                            $fdvar = serialize($fdarr);
                        }
                    }
                    $rewardarr[]=array(
                        'reward_type'=>$reward_type[$k],
                        'reward_money'=>$reward_money[$k],
                        'reward_title'=>$reward_title[$k],
                        'reward_content'=>$reward_content[$k],
                        'reward_limit'=>$reward_limit[$k],
                        'reward_marknum'=>$reward_marknum[$k],
                        'reward_day'=>$reward_day[$k],
                        'reward_fd'=>$fdvar
                    );
                }
                $data=array(
                    'crowd_reward'=>json_encode($rewardarr,JSON_UNESCAPED_UNICODE),
                    'lasttime'=>time()
                );
                $result = pdo_update('yhzc_crowd_cache', $data, array('id' => $id,'u_id'=>$_W['member']['uid'],'uniacid'=>$_W['uniacid']));
                if (!empty($result)) {
                    $step=$step+1;
                    die(json_encode(array('id'=>1,'data'=>array('id'=>$id,'step'=>$step),'msg'=>'保存成功')));
                }
                break;
            case 4:
                if(empty($crowdcache)){
                    die(json_encode(array('id'=>-1,'msg'=>'系统错误')));
                }
                $crowd_content=$this->input->post('crowd_content');
                if($crowd_content!=''){
                    $data=array(
                        'crowd_content'=>$crowd_content,
                        'lasttime'=>time(),
                        'status'=>1
                    );
                    $result = pdo_update('yhzc_crowd_cache', $data, array('id' => $id,'u_id'=>$_W['member']['uid'],'uniacid'=>$_W['uniacid']));
                    if (!empty($result)) {
                        die(json_encode(array('id'=>1,'data'=>array('id'=>$id,'step'=>-1),'msg'=>'保存成功')));
                    }
                }else{
                    die(json_encode(array('id'=>-1,'msg'=>'请输入描述内容')));
                }
                break;
        }
    }
    public function index(){
        global $_W;
        $data=array(
            'title'=>'我发起的众筹',
            'description'=>'一点众筹',
            'keywords'=>'一点众筹',
            'pagename'=>'start',
            'styles'=>['pages/my_crowd'],
            'scripts'=>[],
            '_share'=>array(
                'title'=>'众筹分类',
                'imgUrl'=>'',
                'desc'=>'',
                'link'=>''
            )
        );
        $this->load->view('my_crowd.html',$data);
    }
    public function getmycrowd(){
        global $_W;
        $time=time();
        $page=$this->input->get('page',true);
        $limit=$this->input->get('limit',true);
        $status=$this->input->get('st',true);
        if($status==''){
            $status=0;
        }
        if($page<=0){
            $page=1;
        }
        if($limit==0){
            $limit=10;
        }
        if($status<2){
            if($status==0){
                $status="(status=0 or status=-1)";
            }else{
                $status="status=1";
            }
            $crowds = pdo_fetchall("SELECT id,ntype,addtime,lasttime,default_info,crowd_info,status FROM ".tablename('yhzc_crowd_cache')." where uniacid={$_W['uniacid']} and u_id={$_W['member']['uid']} and $status order by lasttime limit ".($page-1)*$limit.",".$limit);
            $crowd=array();
            foreach($crowds as $k=>$c){
                $crowd[$k]['id']=$c['id'];
                $crowd[$k]['lasttime']=date('Y-m-d H时i分',$c['lasttime']);
                $crowd[$k]['status']=$c['status'];
                $crowd[$k]['ntype']=$c['ntype'];
                $default_info=json_decode($c['default_info'],true);
                $crowd_info=json_decode($c['crowd_info'],true);
                $crowd[$k]['title']=@$crowd_info['title'];
                $crowd[$k]['coverimg']=@$crowd_info['coverimg'];
            }
        }else{
            if($status==2){
                $where="and (c.starttime>{$time} or (c.status=0 and c.endtime>{$time}))";
            }else if($status==3){
                $where="and (c.starttime<{$time} and c.endtime>{$time} and c.status=1)";
            }else if($status==4){
                $where="and (c.endtime<{$time} or c.status>=1)";
            }
            $crowd = pdo_fetchall("SELECT cc.id as id,cc.crowd_id as crowd_id,c.ntype as ntype,c.title,from_unixtime(c.starttime) as starttime,from_unixtime(c.endtime) as endtime,c.money,c.cjmoney,c.ppnum,c.bmnum,c.coverimg,c.status as status FROM ".tablename('yhzc_crowd_cache')." as cc left join ".tablename('yhzc_crowd')." as c on c.id=cc.crowd_id where cc.uniacid={$_W['uniacid']} and cc.u_id='{$_W['member']['uid']}' and cc.status=2 {$where} order by lasttime limit ".($page-1)*$limit.",".$limit);
        }
        if(empty($crowd)){
            die(json_encode(array('id'=>-1,'msg'=>'没有找到！')));
        }
        die(json_encode(array('id'=>1,'msg'=>'success','data'=>$crowd)));
    }
    public function reply(){
        global $_W;
        $id=$this->input->get('id',true);
        $crowdcache= $this->getcrowd_row($id);
        if(empty($crowdcache)){
            die(json_encode(array('id'=>-1,'msg'=>'没有找到')));
        }
        if($crowdcache['status']=='1'){
            $data = array(
                'status' => 0,
            );
            $result = pdo_update('yhzc_crowd_cache', $data, array('id' =>$id));
            if (!empty($result)) {
                die(json_encode(array('id'=>1,'msg'=>'撤回成功')));
            }
        }
    }
    public function del(){
        global $_W;
        $id=$this->input->get('id',true);
        $crowdcache= $this->getcrowd_row($id);
        if(empty($crowdcache)){
            die(json_encode(array('id'=>-1,'msg'=>'没有找到')));
        }
        if($crowdcache['status']==0){
            $result = pdo_delete('yhzc_crowd_cache', array('id' => $id));
            if (!empty($result)) {
                die(json_encode(array('id'=>1,'msg'=>'删除成功')));
            }
        }
    }
    private function getcrowd_row($id){
        global $_W;
        return pdo_get('yhzc_crowd_cache', array('id' => $id,'uniacid'=>$_W['uniacid'],'u_id'=>$_W['member']['uid']));
    }
    public function citydata(){
        header("Content-type: text/javascript; charset=utf-8");
        $citydata=get_area();
        if(!empty($citydata)){
            echo 'var cityData3 = '.json_encode($citydata);
        }else{
            echo 'var cityData3 =[]';
        }
    }
}
