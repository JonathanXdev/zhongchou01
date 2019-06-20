<?php
class Help_model extends CI_Model {
    public function __construct(){
        //$this->load->database();
    }
    public function gethelp_row($id){
        global $_W;
        return pdo_fetch("SELECT h.*,c.name FROM ".tablename('yhzc_help')." as h LEFT JOIN ".tablename('yhzc_help_category')." as c ON h.cid=c.id WHERE h.id = :id and h.uniacid=:uniacid LIMIT 1", array(':id' => $id,':uniacid'=>$_W['uniacid']));
    }
    public function gethelp_all($page=0,$limit=10,$key=false){
        global $_W;
        $where="where h.uniacid=:uniacid";
        $wheres[':uniacid']=$_W['uniacid'];
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        if($key!=''){
            $where.=" and h.title like :key";
            $wheres[':key']='%'.$key.'%';
        }
        return pdo_fetchall("SELECT h.*,c.name FROM ".tablename('yhzc_help')." as h LEFT JOIN ".tablename('yhzc_help_category')." as c ON c.id=h.cid ".$where." order by h.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function gethelp_count($w='',$ws=''){
        global $_W;
        $where="where uniacid=:uniacid";
        if($ws!=''){
            $wheres=$ws;
        }
        if($w!=''){
            $where.=$w;
        }
        $wheres[':uniacid']=$_W['uniacid'];
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_help')." ".$where,$wheres);
    }
    public function help_edit($id){
        global $_W;
        $time=time();
        $cid=$this->input->post('cid');
        $title=$this->input->post('title');
        $content=$this->input->post('content');

        if($title=='' || $content=='' || $cid==''){
            die(json_encode(array('id'=>-1,'msg'=>'请完整提交信息')));
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'cid'=>$cid,
            'title'=>$title,
            'content'=>$content,
            'lasttime'=>$time
        );
        if($id==0){
            $data['addtime']=$time;

            $result = pdo_insert('yhzc_help', $data);
            if (!empty($result)) {
                return pdo_insertid();
            }
        }else{
            $result = pdo_update('yhzc_help', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
            if (!empty($result)) {
                return $id;
            }
        }

    }
    public function getcategory(){
        global $_W;
        return pdo_getall('yhzc_help_category', array('uniacid' => $_W['uniacid']),array() , '',array('sort desc'));
    }
    public function getcategory_row($where=false){
        global $_W;
        $where['uniacid']=$_W['uniacid'];
        return pdo_get('yhzc_help_category', $where);
    }
    public function editcategory(){
        global $_W;
        $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $data = array(
            $name => $value
        );
        $result = pdo_update('yhzc_help_category', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addcategory(){
        global $_W;
        $name=$this->input->post('name');
        $ndesc=$this->input->post('ndesc');
        $sort=$this->input->post('sort');
        if($name==''){
            die(json_encode(array('id'=>-1,'msg'=>'分类名称不能为空')));
        }
        $rptrow=$this->getcategory_row(array('name'=>$name));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'该帮助分类已经存在')));
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'name' => $name,
            'ndesc' =>$ndesc,
            'sort'=>$sort
        );
        $result = pdo_insert('yhzc_help_category', $data);
        if (!empty($result)) {
            // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加分类成功')));
        }
    }
    public function delhelp($id=0){
        global $_W;
        $result = pdo_delete('yhzc_help', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
    public function delcategory($id=0){
        global $_W;
        $result = pdo_delete('yhzc_help_category', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }

}