<?php
class System_model extends CI_Model {
    public function __construct(){
        //$this->load->database();
    }
    public function editsys(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $sysrow = $this->getsystem_all();
        $upnum=0;
        foreach($_POST as $k=>$p){
            foreach($sysrow as $s){
                if($s['key']==$k && $s['value']==$p){
                    continue 2;
                    break;
                }
            }
            if($p!=''){
                if($k=='about'){
                    $ntype=1;
                }else{
                    $ntype=0;
                }
                if($this->getsystem($k)!=''){
                    $result = pdo_update('yhzc_system', array('value'=>$p,'ntype'=>$ntype), array('uniacid'=>$_W['uniacid'],'key'=>$k));
                    if (!empty($result)) {
                        $upnum++;
                    }
                }else{
                    if(pdo_insert('yhzc_system', array('key'=>$k,'value'=>$p,'uniacid'=>$_W['uniacid'],'ntype'=>$ntype))){
                        $upnum++;
                    };
                }
            }
        }
        return $upnum;
    }
    public function editwxmsg(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $crowdmsg=$this->input->post('crowd');
        if($this->getsystem('wxmsg')!=''){
            $result = pdo_update('yhzc_system', array('value'=>serialize($crowdmsg)), array('uniacid'=>$_W['uniacid'],'key'=>'wxmsg'));
            if (!empty($result)) {
                return true;
            }
        }else{
            if(pdo_insert('yhzc_system', array('key'=>'wxmsg','value'=>serialize($crowdmsg),'uniacid'=>$_W['uniacid']))){
                return true;
            };
        }
        die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
    }
    public function editwxpay(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $wxpay=$this->input->post('wxpay');
        $hisr=unserialize(mysys('wxpay'));
        foreach($wxpay as $k=>$v){
            if($v!=''){
                $hisr[$k]=$v;
            }
        }
        if($this->getsystem('wxpay')!=''){
            $result = pdo_update('yhzc_system', array('value'=>serialize($hisr),'ntype'=>1), array('uniacid'=>$_W['uniacid'],'key'=>'wxpay'));
            if (!empty($result)) {
                return true;
            }
        }else{
            if(pdo_insert('yhzc_system', array('key'=>'wxpay','value'=>serialize($hisr),'uniacid'=>$_W['uniacid'],'ntype'=>1))){
                return true;
            };
        }
        die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
    }
    public function editcmt(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $cmt=$this->input->post('cmt');
        $hisr=array();
        foreach($cmt as $k=>$v){
            if($k=='words' || $k=='verify'){
                $hisr[$k]=$v;
            }
        }
        if($this->getsystem('cmt')!=''){
            $result = pdo_update('yhzc_system', array('value'=>serialize($hisr),'ntype'=>1), array('uniacid'=>$_W['uniacid'],'key'=>'cmt'));
            if (!empty($result)) {
                return true;
            }
        }else{
            if(pdo_insert('yhzc_system', array('key'=>'cmt','value'=>serialize($hisr),'uniacid'=>$_W['uniacid'],'ntype'=>1))){
                return true;
            };
        }
        die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
    }
    public function editsmscode(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $smscode=$this->input->post('smsarr');
        if($this->getsystem('smscode')!=''){
            $result = pdo_update('yhzc_system', array('value'=>serialize($smscode),'ntype'=>1), array('uniacid'=>$_W['uniacid'],'key'=>'smscode'));
            if (!empty($result)) {
                return true;
            }
        }else{
            if(pdo_insert('yhzc_system', array('key'=>'smscode','value'=>serialize($smscode),'uniacid'=>$_W['uniacid'],'ntype'=>1))){
                return true;
            };
        }
        die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
    }
    public function editsmscfg(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $ff=$this->input->get('ff');
        if($ff==''){
            $ff=='ali';
        }
        $smscfg=$this->input->post($ff.'smscfg');
        if($this->getsystem($ff.'smscfg')!=''){
            $result = pdo_update('yhzc_system', array('value'=>serialize($smscfg),'ntype'=>1), array('uniacid'=>$_W['uniacid'],'key'=>$ff.'smscfg'));
            if (!empty($result)) {
                return true;
            }
        }else{
            if(pdo_insert('yhzc_system', array('key'=>$ff.'smscfg','value'=>serialize($smscfg),'uniacid'=>$_W['uniacid'],'ntype'=>1))){
                return true;
            };
        }
        die(json_encode(array('id'=>-1,'msg'=>'没有修改')));
    }
    public function editface(){
        global $_W;
        if(!$_POST){
            exit;
        }
        $facearr=array();
        $imgs=$this->input->post('img');
        $links=$this->input->post('link');
        foreach($imgs as $k=>$img){
            if($img!='') {
                $facearr[] = array(
                    'img' => $img,
                    'link' => $links[$k]
                );
            }
        }
        if(empty($facearr)){
            return false;
        }
        if($this->getsystem('face')!=''){
            $result = pdo_update('yhzc_system', array('value'=>json_encode($facearr),'ntype'=>1), array('uniacid'=>$_W['uniacid'],'key'=>'face'));
            if (!empty($result)) {
                return true;
            }
        }else{
            if(pdo_insert('yhzc_system', array('key'=>'face','value'=>json_encode($facearr),'uniacid'=>$_W['uniacid'],'ntype'=>1))){
                return true;
            };
        }
    }
    public function getsystem_all(){
        global $_W;
        return pdo_getall('yhzc_system', array('uniacid' => $_W['uniacid']), array() , '');
    }
    public function getsystem($key=''){
        global $_W;
        return pdo_get('yhzc_system', array('uniacid' => $_W['uniacid'],'key'=>$key));
    }
    public function getnotice(){
        global $_W;
        return pdo_getall('yhzc_sys_notice', array('uniacid' => $_W['uniacid']),array() , '' , array('addtime desc'));
    }
    public function getnotice_row($where=false){
        global $_W;
        $where['uniacid']=$_W['uniacid'];
        return pdo_get('yhzc_sys_notice', $where);
    }
	public function editnotice(){
        global $_W;
	    $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $data = array(
            $name => $value,
            'uniacid'=>$_W['uniacid']
        );
        $result = pdo_update('yhzc_sys_notice', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addnotice(){
        global $_W;
        $title=$this->input->post('title');
        $content=$this->input->post('content');
        $addtime=time();
        if($title==''){
            die(json_encode(array('id'=>-1,'msg'=>'公告标题不能为空')));
        }
        if($content==''){
            die(json_encode(array('id'=>-1,'msg'=>'公告内容不能为空')));
        }
        $rptrow=$this->getnotice_row(array('title'=>$title));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'存在类似公告，请删除后重新发布')));
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'title' => $title,
            'content' => $content,
            'addtime' =>$addtime,
        );
        $result = pdo_insert('yhzc_sys_notice', $data);
        if (!empty($result)) {
           // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加分类成功')));
        }
    }
    public function delnotice($id=0){
        global $_W;
        $result = pdo_delete('yhzc_sys_notice', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
    //入口设置
    public function getrule(){
        global $_W;
        return pdo_getall('yhzc_sys_rule', array('uniacid' =>$_W['uniacid']),array() , '');
    }
    public function editrule(){
        global $_W;
        $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $sysrule=pdo_get('yhzc_sys_rule', array('id' => $id,'uniacid'=>$_W['uniacid']));
        $data = array(
            $name => $value,
            'uniacid'=>$_W['uniacid']
        );
        if($name=='key'){
            if($sysrule['key']==$value){
                die(json_encode(array('id'=>-1,'msg'=>'没有任何修改')));
            }
            $w7row=pdo_get('rule_keyword', array('content' => $value));
            if(!empty($w7row)){
                die(json_encode(array('id'=>-1,'msg'=>'关键词已被占用')));
            }else{
                pdo_update('rule_keyword',array('content'=>$value), array('content' =>$sysrule['key'],'module'=>'yh_zhongchou'));
            }
        }
        if($name=='status'){
            pdo_update('rule_keyword',array('status'=>$value), array('content' =>$sysrule['key'],'module'=>'yh_zhongchou'));
        }
        $result = pdo_update('yhzc_sys_rule', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));

        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addrule(){
        global $_W;
        $name=$this->input->post('name');
        $key=$this->input->post('key');
        $link=$this->input->post('link');
        $content=$this->input->post('content');
        if($name==''){
            die(json_encode(array('id'=>-1,'msg'=>'入口名称为内部表示，方便识别，请填写')));
        }
        if($key==''){
            die(json_encode(array('id'=>-1,'msg'=>'关键词必须填写')));
        }
        if($link==''){
            die(json_encode(array('id'=>-1,'msg'=>'入口链接必须填写')));
        }
        $w7row=pdo_get('rule_keyword', array('content' => $key));
        if(!empty($w7row)){
            die(json_encode(array('id'=>-1,'msg'=>'关键词已被占用')));
        }
        $w7group=pdo_get('rule', array('module' =>'yh_zhongchou','name'=>'一点众筹入口设置'));
        if(empty($w7group)){
            pdo_insert('rule', array('uniacid'=>$_W['uniacid'],'name'=>'一点众筹入口设置','module'=>'yh_zhongchou','displayorder'=>255,'status'=>1));
            $gid = pdo_insertid();
        }else{
            $gid=$w7group['id'];
        }
        pdo_insert('rule_keyword', array('rid'=>$gid,'uniacid'=>$_W['uniacid'],'module'=>'yh_zhongchou','content'=>$key,'type'=>1,'displayorder'=>255,'status'=>1));
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'name' => $name,
            'key' => $key,
            'link' =>$link,
            'content'=>$content,
            'status'=>1
        );
        $result = pdo_insert('yhzc_sys_rule', $data);
        if (!empty($result)) {
            // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加入口成功')));
        }
    }
    public function delrule($id=0){
        global $_W;
        $sysrule=pdo_get('yhzc_sys_rule', array('id' => $id,'uniacid'=>$_W['uniacid']));
        pdo_delete('rule_keyword', array('uniacid' => $_W['uniacid'],'module'=>'yh_zhongchou','content'=>$sysrule['key']));
        $result = pdo_delete('yhzc_sys_rule', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
}