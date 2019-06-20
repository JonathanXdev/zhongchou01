<?php
class Crowd_model extends CI_Model {
    public function __construct(){
        //$this->load->database();
    }
    public function getcrowd_row($id){
        global $_W;
        return pdo_fetch("SELECT c.*,cc.name as cname FROM ".tablename('yhzc_crowd')." as c LEFT JOIN ".tablename('yhzc_crowd_category')." as cc ON c.cid=cc.id WHERE c.id = :id and c.uniacid=:uniacid LIMIT 1", array(':id' => $id,':uniacid'=>$_W['uniacid']));
    }
    public function getcrowd_all($page=0,$limit=10,$key=false){
        global $_W;
        $where="where c.uniacid=:uniacid";
        $wheres[':uniacid']=$_W['uniacid'];
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        if($key!=''){
            $where.=" and (c.title like :key or u.name like :key or u.phone=:keys)";
            $wheres[':key']="%{$key}%";
            $wheres[':keys']=$key;
        }
        return pdo_fetchall("SELECT u.name as realname,u.phone,u.phoneak,c.*,cc.name as cname FROM ".tablename('yhzc_crowd')." c LEFT JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id LEFT join ".tablename('yhzc_user')." as u on u.uid=c.u_id ".$where." order by c.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getcrowd_union_row($id){
        global $_W;
        return pdo_fetch("SELECT u.*,c.title,c.money,cc.name as cname FROM ".tablename('yhzc_crowd_union')." u LEFT JOIN ".tablename('yhzc_crowd')." c ON u.crowd_id=c.id LEFT JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id  WHERE u.id = :id and u.uniacid=:uniacid LIMIT 1", array(':id' => $id,':uniacid'=>$_W['uniacid']));
    }
    public function getcrowd_union_all($page=0,$limit=10,$key=false){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        $team_id=$this->input->get('team_id');
        $f_uid=$this->input->get('f_uid');
        if($crowd_id==''){
            echo '错误';
            exit;
        }
        $where="where u.uniacid=:uniacid and u.crowd_id=:crowd_id";
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id']=$crowd_id;
        if($team_id>0){
            $where.=" and u.team_id=:team_id";
            $wheres[':team_id']=$team_id;
        }
        if($f_uid>0){
            $where.=" and u.f_uid=:f_uid";
            $wheres[':f_uid']=$f_uid;
        }
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        if($key!=''){
            $where.=" and u.nickname like :key or u.realname like :key or u.phone=:keys";
            $wheres[':key']="%{$key}%";
            $wheres[':keys']=$key;
        }
        $fst=$this->input->get('fst');
        $fed=$this->input->get('fed');
        $fg=$this->input->get('fg');
        if($fst!=''){
            $where.=" and u.starttime>=:fst";
            $wheres[':fst']=strtotime($fst);
        }
        if($fed!=''){
            $where.=" and u.starttime<=:fed";
            $wheres[':fed']=strtotime($fed);
        }
        if($fg=='1'){
            $where.=" and u.cjmoney>=c.money";
        }else if($fg=='0'){
            $where.=" and u.cjmoney<c.money";
        }

        $union=pdo_fetchall("SELECT u.*,c.title,c.money,cc.name as cname,t.name,t.name as tname,f.nickname as fnickname,f.realname as frealname,f.phone as fphone,f.avatar as favatar FROM ".tablename('yhzc_crowd_union')." u left join ".tablename('yhzc_crowd_union')." as f on f.user_id=u.f_uid and f.crowd_id=u.crowd_id LEFT JOIN ".tablename('yhzc_crowd')." c ON u.crowd_id=c.id LEFT JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id LEFT JOIN ".tablename('yhzc_crowd_team')." t ON t.id=u.team_id ".$where." order by u.endtime desc,u.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);

        return $union;
    }
    public function update_union($data,$where){
        global $_W;
        $where['uniacid']=$_W['uniacid'];
        return pdo_update('yhzc_crowd_union', $data, $where);
    }
    public function getrewards($crowd_id){
        global $_W;
        return pdo_getall('yhzc_crowd_reward', array('uniacid' => $_W['uniacid'],'crowd_id'=>$crowd_id));
    }
    public function getcrowd_count($w='',$ws=''){
        global $_W;
        $where="where c.uniacid=:uniacid";
        if($ws!=''){
            $wheres=$ws;
        }
        if($w!=''){
            $where.=$w;
        }
        $wheres[':uniacid']=$_W['uniacid'];
        return pdo_fetch("SELECT count(c.id) as count FROM ".tablename('yhzc_crowd')." c LEFT JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id LEFT join ".tablename('yhzc_user')." as u on u.uid=c.u_id ".$where,$wheres);
    }
    public function getcrowd_union_count($key=false){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            echo '错误';
            exit;
        }
        $where="where u.uniacid=:uniacid and u.crowd_id=:crowd_id";
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id']=$crowd_id;
        if($key!=''){
            $where.=" and u.nickname like %:key% or u.realname like %:key% or u.phone=:keys";
            $wheres[':key']=$key;
            $wheres[':keys']=$key;
        }
        $fst=$this->input->get('fst');
        $fed=$this->input->get('fed');
        $fg=$this->input->get('fg');
        if($fst!=''){
            $where.=" and u.starttime>=:fst";
            $wheres[':fst']=strtotime($fst);
        }
        if($fed!=''){
            $where.=" and u.starttime<=:fed";
            $wheres[':fed']=strtotime($fed);
        }
        if($fg=='1'){
            $where.=" and u.cjmoney>=c.money";
        }else if($fg=='0'){
            $where.=" and u.cjmoney<c.money";
        }
        return pdo_fetch("SELECT count(u.id) as count FROM ".tablename('yhzc_crowd_union')." u LEFT JOIN ".tablename('yhzc_crowd')." c ON u.crowd_id=c.id LEFT JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id ".$where,$wheres);
    }
    public function add_crowd(){
        global $_W;
        $time=time();
        $id=$this->input->post('id');
        $cid=$this->input->post('cid');
        $nfrom=$this->input->post('nfrom');
        $ntype=$this->input->post('ntype');
        $coverimg=$this->input->post('coverimg');
        $title=$this->input->post('title');
        $money=$this->input->post('money');
        $bmlimit=$this->input->post('bmlimit');
        $gear=$this->input->post('gear');
        $summary=$this->input->post('summary');
        $starttime=$this->input->post('starttime');
        $endtime=$this->input->post('endtime');
        $tags=$this->input->post('tags');
        $status=$this->input->post('status');
        $content=$this->input->post('content');
        $cjtype=$this->input->post('cjtype');
        //权限信息
        $authname=$this->input->post('authname');
        $authimg=$this->input->post('authimg');
        $authstatus=$this->input->post('authstatus');
        $city=$this->input->post('city');
        if($title=='' || $gear=='' || $summary==''|| $starttime==''|| $endtime==''|| $content==''){
            die(json_encode(array('id'=>-1,'msg'=>'请完整提交众筹信息')));
        }
        $crowdrow=array();
        if($id>0){
            $crowdrow=$this->getcrowd_row($id);
        }
        if(empty($crowdrow)){
            $rptrow=pdo_fetch("SELECT id FROM ".tablename('yhzc_crowd')." WHERE title = :title and starttime=:starttime and endtime=:endtime and uniacid=:uniacid LIMIT 1", array(':title' => $title,':starttime' => strtotime($starttime),':endtime' => strtotime($endtime),':uniacid'=>$_W['uniacid']));
            if(!empty($rptrow)){
                die(json_encode(array('id'=>-1,'msg'=>'请勿重复发布众筹项目')));
            }
        }
        $autharr=array();
        if(is_array($authname) && is_array($authimg) && is_array($authstatus)){
            foreach($authname as $k=>$v){
                $autharr[]=array(
                    'name'=>$v,
                    'img'=>$authimg[$k],
                    'status'=>$authstatus[$k]
                );
            }
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'cid'=>$cid,
            'nfrom'=>$nfrom,
            'ntype'=>$ntype,
            'title'=>$title,
            'money'=>$money,
            'coverimg'=>json_encode($coverimg),
            'gear'=>$gear,
            'summary'=>$summary,
            'addtime'=>$time,
            'starttime'=>strtotime($starttime),
            'endtime'=>strtotime($endtime),
            'tags'=>$tags,
            'bmlimit'=>$bmlimit==''?0:$bmlimit,
            'status'=>$status,
            'content'=>$content,
            'auth'=>json_encode($autharr),
            'city'=>$city,
            'cjtype'=>$cjtype==''?0:$cjtype
        );
        if($ntype=='1'){
            $team=$this->input->post('team');
            if($team==''){
                $team=0;
            }
            $data['team']=$team;
        }
        if(!empty($crowdrow)){
            $result = pdo_update('yhzc_crowd', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
            if (!empty($result)) {
                return $id;
            }
        }else{
            $data['taskst']=0;
            $result = pdo_insert('yhzc_crowd', $data);
            if (!empty($result)) {
                $id = pdo_insertid();
                return $id;
            }
        }
    }
    public function add_crowd_rewards($id){
        global $_W;
        $ids=$this->input->post('id');
        /**if($ids>0){
            pdo_delete('yhzc_crowd_reward', array('crowd_id' => $ids));
        }**/
        $reward_type=$this->input->post('reward_type');
        $reward_money=$this->input->post('reward_money');
        $reward_title=$this->input->post('reward_title');
        $reward_content=$this->input->post('reward_content');
        $reward_limit=$this->input->post('reward_limit');
        $reward_day=$this->input->post('reward_day');
        $reward_marknum=$this->input->post('reward_marknum');
        $reward_fd=$this->input->post('reward_fd');
        $reward_id=$this->input->post('reward_id');
        if(!empty($reward_fd)){

        }
        $rewarddata='';
        $knum=0;
        $upfail=0;
        $upid=array();
        if(is_array($reward_type) && is_array($reward_money) && is_array($reward_title)){
            foreach($reward_type as $k=>$v){
                if($reward_money[$k]=='' || $reward_title[$k]=='' || $reward_content[$k]==''){
                    continue;
                }
                $fdvar = '';
                if (!empty($reward_fd)) {
                    $fdarr = array();
                    if (is_array(@$reward_fd[$k])) {
                        foreach ($reward_fd[$k] as $rfd) {
                            $fdarr[] = $rfd;
                        }
                        $fdvar = serialize($fdarr);
                    }
                }
                if(@$reward_id[$k]>0){
                    $upid[]=trim($reward_id[$k]);
                    $udata=array(
                        'reward_type'=>trim($reward_type[$k]),
                        'reward_money'=>trim($reward_money[$k]),
                        'reward_title'=>trim($reward_title[$k]),
                        'reward_content'=>trim($reward_content[$k]),
                        'reward_limit'=>trim($reward_limit[$k]),
                        'reward_day'=>trim($reward_day[$k]),
                        'reward_fd'=>trim($fdvar),
                        'reward_marknum'=>trim($reward_marknum[$k])
                    );
                    $results = pdo_update('yhzc_crowd_reward', $udata, array('id' =>trim($reward_id[$k]),'uniacid'=>$_W['uniacid'],'crowd_id'=>trim($id)));
                    if(!empty($results)){

                    }else{
                        $upfail++;
                    }
                }else {
                    if ($knum > 0) {
                        $rewarddata .= ",";
                    }
                    $knum++;
                    $rewarddata .= "({$_W['uniacid']}," . trim($id) . "," . trim($reward_type[$k]) . "," . trim($reward_money[$k]) . ",'" . trim($reward_title[$k]) . "','" . trim($reward_content[$k]) . "'," . trim($reward_limit[$k]) . "," . trim($reward_day[$k]) . ",'" . trim($fdvar) . "'," . trim($reward_marknum[$k]) . ")";
                }
            }
        }else{
            if($ids>0){
                pdo_delete('yhzc_crowd_reward', array('crowd_id' => $ids));
            }
        }
        if(!empty($upid)){
            $uwhere='';
            foreach($upid as $uk=>$uv){
                if($uk==0){
                    $uwhere.="id<>{$uv}";
                }else{
                    $uwhere.=" and id<>{$uv}";
                }
            }
            $delrst = pdo_query("DELETE FROM ".tablename('yhzc_crowd_reward')." WHERE {$uwhere} and uniacid={$_W['uniacid']} and crowd_id=".trim($id));
        }
        if($knum>0){
        $result = pdo_query("INSERT INTO ".tablename('yhzc_crowd_reward')." (uniacid,crowd_id,reward_type,reward_money,reward_title,reward_content,reward_limit,reward_day,reward_fd,reward_marknum) VALUES {$rewarddata}");
            if (!empty($result) && $upfail<=0) {
                return true;
            }
        }else{
            return false;
        }
    }
    public function getcategory(){
        global $_W;
        return pdo_getall('yhzc_crowd_category', array('uniacid' => $_W['uniacid']),array() , '' , array('norder desc'));
    }
    public function getcategory_row($where=false){
        return pdo_get('yhzc_crowd_category', $where);
    }
	public function editcategory(){
        global $_W;
	    $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $data = array(
            $name => $value
        );
        $result = pdo_update('yhzc_crowd_category', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addcategory(){
        global $_W;
        $norder=$this->input->post('norder');
        $name=$this->input->post('name');
        $icon=$this->input->post('icon');
        $content=$this->input->post('content');
        if($name==''){
            die(json_encode(array('id'=>-1,'msg'=>'分类名称不能为空')));
        }
        $rptrow=$this->getcategory_row(array('name'=>$name));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'该分类已经存在')));
        }
        $data = array(
            'uniacid'=>$_W['uniacid'],
            'norder' => $norder,
            'name' => $name,
            'icon'=>$icon,
            'content' =>$content,
        );
        $result = pdo_insert('yhzc_crowd_category', $data);
        if (!empty($result)) {
           // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加分类成功')));
        }
    }
    public function delcategory($id=0){
        global $_W;
        $result = pdo_delete('yhzc_crowd_category', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
    public function getreport_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where r.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetchall("SELECT r.*,from_unixtime(r.addtime) as uaddtime,from_unixtime(r.paytime) as upaytime FROM ".tablename('yhzc_crowd_report')." as r left join ".tablename('yhzc_crowd_union')." as u on u.id=r.union_id {$where} group by r.id order by id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getreport_count($where='',$wheres=''){
        global $_W;
        if($where!=''){
            $where="where r.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetch("SELECT count(r.id) as count FROM ".tablename('yhzc_crowd_report')." as r left join ".tablename('yhzc_crowd_union')." as u on u.id=r.union_id ".$where,$wheres);
    }
    public function getreportreward_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where cr.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetchall("SELECT cr.*,from_unixtime(cr.addtime) as uaddtime,from_unixtime(cr.paytime) as upaytime,from_unixtime(cr.reward_time) as ureward_time,ua.name as uaname,ua.phone as uaphone,ua.country as country,ua.province as uaprovince,ua.city as uacity,ua.other as uaother,ua.zipcode as uazipcode,crd.reward_type as reward_type,crd.reward_money as reward_money,crd.reward_title as reward_title,crd.reward_content as reward_content,crd.reward_day as reward_day,crd.reward_fd as reward_fd FROM ".tablename('yhzc_crowd_report')." as cr LEFT JOIN ".tablename('yhzc_user_address')." as ua ON cr.reward_address_id=ua.id LEFT JOIN ".tablename('yhzc_crowd_reward')." as crd ON cr.reward_id=crd.id {$where} order by cr.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getreportreward_count($where='',$wheres=''){
        global $_W;
        if($where!=''){
            $where="where cr.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_crowd_report')." as cr ".$where,$wheres);
    }
    public function getmarch($crowd_id=0){
        global $_W;
        return pdo_getall('yhzc_crowd_march', array('uniacid' => $_W['uniacid'],'crowd_id'=>$crowd_id),array() , '' , array('id desc'));
    }
    public function getmarch_row($where=false){
        return pdo_get('yhzc_crowd_march', $where);
    }
    public function editmarch(){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            exit;
        }
        $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $data = array(
            $name => $value
        );
        $result = pdo_update('yhzc_crowd_march', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addmarch(){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            exit;
        }
        $nfrom=$this->input->post('nfrom');
        $title=$this->input->post('title');
        $content=$this->input->post('content');
        if($title==''){
            die(json_encode(array('id'=>-1,'msg'=>'标题不能为空')));
        }
        $rptrow=$this->getmarch_row(array('title'=>$title));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'标题与已有进展标题相同')));
        }
        $data = array(
            'crowd_id'=>$crowd_id,
            'uniacid'=>$_W['uniacid'],
            'nfrom' => $nfrom,
            'title' => $title,
            'addtime'=>time(),
            'ntype'=>0,
            'content' =>$content,
        );
        $result = pdo_insert('yhzc_crowd_march', $data);
        if (!empty($result)) {
            // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加进展成功')));
        }
    }
    public function delmarch($id=0){
        global $_W;
        $result = pdo_delete('yhzc_crowd_march', array('id' => $id,'uniacid'=>$_W['uniacid']));
        if(!empty($result)) {
            return true;
        }
    }
    public function addteam(){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            exit;
        }
        $name=$this->input->post('name');
        $summary=$this->input->post('summary');
        $status=$this->input->post('status');
        if($status==''){
            $status=0;
        }
        if($name==''){
            die(json_encode(array('id'=>-1,'msg'=>'队伍名称不能为空')));
        }
        $rptrow=pdo_get('yhzc_crowd_team',array('name'=>$name,'crowd_id'=>$crowd_id));
        if(!empty($rptrow)){
            die(json_encode(array('id'=>-1,'msg'=>'队伍名称已存在')));
        }
        $data = array(
            'crowd_id'=>$crowd_id,
            'uniacid'=>$_W['uniacid'],
            'name' => $name,
            'summary' => $summary,
            'uid'=>0,
            'addtime'=>time(),
            'status'=>$status
        );
        $result = pdo_insert('yhzc_crowd_team', $data);
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'增加队伍成功')));
        }
    }
    public function editteam(){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            exit;
        }
        $name=$this->input->post('name');
        $value=$this->input->post('value');
        $id=$this->input->post('pk');
        $data = array(
            $name => $value
        );
        $result = pdo_update('yhzc_crowd_team', $data, array('id' =>$id,'uniacid'=>$_W['uniacid']));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function get_crowd_cache_row($id){
        global $_W;
        return pdo_fetch("SELECT c.*,u.name,u.phone,u.phoneak FROM ".tablename('yhzc_crowd_cache')." as c left join ".tablename('yhzc_user')." as u on u.uid=c.u_id where c.uniacid=:uniacid and c.id=:id ",array(':uniacid'=>$_W['uniacid'],':id'=>$id));
    }
    public function get_crowd_cache_all($page=0,$limit=10,$w,$ws=array()){
        global $_W;
        $where="where c.uniacid=:uniacid and c.status=1";
        $wheres[':uniacid']=$_W['uniacid'];
        if($w!=''){
            $where.=" and ".$w;
        }
        if(!empty($ws)){
            foreach($ws as $k=>$s){
                $wheres[$k]=$s;
            }
        }
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        return pdo_fetchall("SELECT c.id,c.ntype,c.default_info,c.crowd_info,c.lasttime,u.uid,u.name,u.phone,u.phoneak FROM ".tablename('yhzc_crowd_cache')." as c left join ".tablename('yhzc_user')." as u on u.uid=c.u_id ".$where." order by id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function get_crowd_cache_count($w='',$ws=''){
        global $_W;
        $where="where c.uniacid=:uniacid and c.status=1";
        $wheres[':uniacid']=$_W['uniacid'];
        if($w!=''){
            $where.=" and ".$w;
        }
        if(!empty($ws)){
            foreach($ws as $k=>$s){
                $wheres[$k]=$s;
            }
        }
        $wheres[':uniacid']=$_W['uniacid'];
        return pdo_fetch("SELECT count(c.id) as count FROM ".tablename('yhzc_crowd_cache')." as c left join ".tablename('yhzc_user')." as u on u.uid=c.u_id ".$where,$wheres);
    }

}