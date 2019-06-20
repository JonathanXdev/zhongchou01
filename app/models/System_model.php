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
                if(pdo_insert('yhzc_system', array('key'=>$k,'value'=>$p,'uniacid'=>$_W['uniacid']), true)){
                    $upnum++;
                };
            }
        }
        return $upnum;
    }
    public function getsystem_all(){
        global $_W;
        return pdo_getall('yhzc_system', array('uniacid' => $_W['uniacid']), array() , '');
    }
    public function getsystem($key=''){
        global $_W;
        return pdo_get('yhzc_system', array('uniacid' => $_W['uniacid'],'key'=>$key));
    }
    public function getnotice($page=1,$limit=20){
        global $_W;
        return pdo_getall('yhzc_sys_notice', array('uniacid' => $_W['uniacid']),array() , '' , array('addtime desc'),array($page,$limit));
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
        $result = pdo_update('yhzc_sys_notice', $data, array('id' =>$id));
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
        $result = pdo_delete('yhzc_sys_notice', array('id' => $id));
        if(!empty($result)) {
            return true;
        }
    }
}