<?php
class Crowd_model extends CI_Model {
    public function __construct(){
        //$this->load->database();
    }
    public function getcrowd_row($id){
        global $_W;
        return pdo_fetch("SELECT c.*,cc.name as cname FROM ".tablename('yhzc_crowd')." as c LEFT JOIN ".tablename('yhzc_crowd_category')." as cc ON c.cid=cc.id WHERE c.id = :id and c.uniacid=:uniacid LIMIT 1", array(':id' => $id,':uniacid'=>$_W['uniacid']));
    }
    public function getcrowd_all($page=0,$limit=10){
        global $_W;
        $time=time();
        $where="where c.uniacid=:uniacid and status=1";
        $tt=$this->input->get('tt');
        if($tt==0 || $tt==''){
            //进行中
            $where.=" and endtime>{$time} and starttime<={$time}";
        }else if($tt=='1'){
            //未开始
            $where.=" and starttime>{$time}";
        }else if($tt=='2'){
            //已结束
            $where.=" and endtime<{$time}";
        }
        $wheres[':uniacid']=$_W['uniacid'];
        $page=$this->input->get('page');
        $limit=$this->input->get('limit');
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        $cid=$this->input->get('cid');
        $ntype=$this->input->get('tp');
        if($cid!=''){
            $where.=" and c.cid=:cid";
            $wheres[':cid']=$cid;
        }
        if($ntype!=''){
            $where.=" and c.ntype=:ntype";
            $wheres[':ntype']=$ntype;
        }
        $key=$this->input->get('key');
        if($key!=''){
            $where.=" and c.title like :key";
            $wheres[':key']="%".$key.'%';
        }
        $fl=$this->input->get('fl');
        if($fl!=''){
            $od=$this->input->get('od');
            if($fl=='viewnum'){
                $order="c.viewnum";
            }else if($fl=='ppnum'){
                $order="c.ppnum";
            }else if($fl=='money'){
                $order="c.money";
            }else{
                $order="c.id";
            }
            if($od=='0'){
                $order.=" desc";
            }else{
                $order.=" desc";
            }
        }else{
            $order="c.id desc";
        }
        return pdo_fetchall("SELECT c.*,from_unixtime(c.starttime) as cstarttime,cc.name as cname,count(r.id) as rcount FROM ".tablename('yhzc_crowd')." c left JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id LEFT JOIN ".tablename('yhzc_crowd_reward')." r ON r.crowd_id=c.id ".$where." group by c.id order by {$order} LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getcrowd_union_row($id){
        global $_W;
        return pdo_fetch("SELECT * FROM ".tablename('yhzc_crowd_union')." WHERE id = :id and uniacid=:uniacid LIMIT 1", array(':id' => $id,':uniacid'=>$_W['uniacid']));
    }

    /**
     * @notes: 查找众筹报名人数
     * @author tongwz
     * @date: 2019年6月29日10:53:10
     * @param int $page
     * @param int $limit
     * @param int $id
     * @return bool
     */
    public function getCrowdUnionList($page = 1, $limit = 10, $id = 0)
    {
        global $_W;
        if ($id == 0) return false;
        $where = " u.uniacid=:uniacid and u.crowd_id=:crowd_id and u.status=1";
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id'] = $id;
        $sql = "SELECT
                    u.*, u.id AS unionid,
                    from_unixtime(u.addtime, '%Y-%m-%d') AS uaddtime,
                    c.title,
                    c.money,
                    cc. NAME AS cname
                FROM
                    ims_yhzc_crowd_union AS u
                LEFT JOIN ims_yhzc_crowd c ON u.crowd_id = c.id
                LEFT JOIN ims_yhzc_crowd_category cc ON c.cid = cc.id
                WHERE
                    ". $where."
                GROUP BY
                    u.id
                ORDER BY
                    u.updatetime DESC
                LIMIT ".($page-1)* $limit.",
                 ". $limit;
        $res = pdo_fetchall($sql, $wheres);
        return $res;
    }

    // 获取光荣榜列表
    public function getCrowdHonourUnionList($page = 1, $limit = 10, $id = 0)
    {
        global $_W;
        if ($id == 0) return false;
        $where = " u.uniacid=:uniacid and u.crowd_id=:crowd_id and u.status=1 and u.cjmoney >= c.money";
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id'] = $id;
        $sql = "SELECT
                    u.*, u.id AS unionid,
                    from_unixtime(u.addtime, '%Y-%m-%d') AS uaddtime,
                    c.title,
                    c.money,
                    cc. NAME AS cname
                FROM
                    ims_yhzc_crowd_union AS u
                LEFT JOIN ims_yhzc_crowd c ON u.crowd_id = c.id
                LEFT JOIN ims_yhzc_crowd_category cc ON c.cid = cc.id
                WHERE
                    ". $where."
                GROUP BY
                    u.id
                ORDER BY
                    u.cjmoney DESC
                LIMIT ".($page-1)* $limit.",
                 ". $limit;
        $res = pdo_fetchall($sql, $wheres);
        return $res;
    }
    public function getcrowd_union_all($page=0,$limit=10,$key=false){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            return false;
        }
        $where="where u.uniacid=:uniacid and u.crowd_id=:crowd_id and u.status=1";
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id']=$crowd_id;
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=5;
        }
        pdo_query("SET SESSION group_concat_max_len=1024;");
        $row=pdo_fetchall("SELECT u.*,u.id as unionid,from_unixtime(u.addtime,'%Y-%m-%d') as uaddtime,c.title,c.money,cc.name as cname,(select substring_index(group_concat(distinct avatar), ',',8) FROM ".tablename('yhzc_crowd_report')." cr where cr.union_id=unionid and cr.status=1 order by cr.id desc limit 0,8 ) as ravatar FROM ".tablename('yhzc_crowd_union')." as u LEFT JOIN ".tablename('yhzc_crowd')." c ON u.crowd_id=c.id LEFT JOIN ".tablename('yhzc_crowd_category')." cc ON c.cid=cc.id ".$where." group by u.id order by u.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
        return $row;
    }
    public function getcrowd_union_count($key=false){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        $where="where uniacid=:uniacid and crowd_id=:crowd_id and status=1";
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id']=$crowd_id;
        $wheres[':uniacid']=$_W['uniacid'];
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_crowd_union')." ".$where,$wheres);
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
    public function getreward($id){
        global $_W;
        return pdo_get('yhzc_crowd_reward', array('id' => $id,'uniacid' => $_W['uniacid']));
    }
    public function getcrowd_count($w='',$ws=''){
        global $_W;
        $where="where uniacid=:uniacid";
        if($ws!=''){
            $wheres=$ws;
        }
        if($w!=''){
            $where.=$w;
        }
        $wheres[':uniacid']=$_W['uniacid'];
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_crowd')." ".$where,$wheres);
    }
    public function add_crowd(){
        global $_W;
        $time=time();
        $id=$this->input->post('id');
        $cid=$this->input->post('cid');
        $nfrom=$this->input->post('nfrom');
        $coverimg=$this->input->post('coverimg');
        $title=$this->input->post('title');
        $money=$this->input->post('money');
        $gear=$this->input->post('gear');
        $summary=$this->input->post('summary');
        $starttime=$this->input->post('starttime');
        $endtime=$this->input->post('endtime');
        $tags=$this->input->post('tags');
        $status=$this->input->post('status');
        $content=$this->input->post('content');
        //权限信息
        $authname=$this->input->post('authname');
        $authimg=$this->input->post('authimg');
        $authstatus=$this->input->post('authstatus');
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
            'title'=>$title,
            'money'=>$money,
            'coverimg'=>json_encode($coverimg),
            'gear'=>$gear,
            'summary'=>$summary,
            'addtime'=>$time,
            'starttime'=>strtotime($starttime),
            'endtime'=>strtotime($endtime),
            'tags'=>$tags,
            'status'=>$status,
            'content'=>$content,
            'auth'=>json_encode($autharr)
        );
        if(!empty($crowdrow)){
            $result = pdo_update('yhzc_crowd', $data, array('id' =>$id));
            if (!empty($result)) {
                return $id;
            }
        }else{
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
        if($ids>0){
            pdo_delete('yhzc_crowd_reward', array('crowd_id' => $ids));
        }
        $reward_type=$this->input->post('reward_type');
        $reward_money=$this->input->post('reward_money');
        $reward_title=$this->input->post('reward_title');
        $reward_content=$this->input->post('reward_content');
        $reward_limit=$this->input->post('reward_limit');
        $reward_day=$this->input->post('reward_day');
        $rewarddata='';
        $knum=0;
        if(is_array($reward_type) && is_array($reward_money) && is_array($reward_title)){
            foreach($reward_type as $k=>$v){
                if($reward_money[$k]=='' || $reward_title[$k]=='' || $reward_content[$k]==''){
                    continue;
                }
                if($knum>0){
                    $rewarddata.=",";
                }
                $knum++;
                $rewarddata.="({$_W['uniacid']},".trim($id).",".trim($reward_type[$k]).",".trim($reward_money[$k]).",'".trim($reward_title[$k])."','".trim($reward_content[$k])."',".trim($reward_limit[$k]).",".trim($reward_day[$k]).")";
            }
        }
        if($knum>0){
            $result = pdo_query("INSERT INTO ".tablename('yhzc_crowd_reward')." (uniacid,crowd_id,reward_type,reward_money,reward_title,reward_content,reward_limit,reward_day) VALUES {$rewarddata}");
            if (!empty($result)) {
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
            $name => $value,
            'uniacid'=>$_W['uniacid']
        );
        $result = pdo_update('yhzc_crowd_category', $data, array('id' =>$id));
        if (!empty($result)) {
            die(json_encode(array('id'=>1,'msg'=>'编辑成功')));
        }
    }
    public function addcategory(){
        global $_W;
        $norder=$this->input->post('norder');
        $name=$this->input->post('name');
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
            'content' =>$content,
        );
        $result = pdo_insert('yhzc_crowd_category', $data);
        if (!empty($result)) {
            // $id = pdo_insertid();
            die(json_encode(array('id'=>1,'msg'=>'增加分类成功')));
        }
    }
    public function delcategory($id=0){
        $result = pdo_delete('yhzc_crowd_category', array('id' => $id));
        if(!empty($result)) {
            return true;
        }
    }
    public function getreport_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetchall("SELECT *,from_unixtime(addtime) as uaddtime,from_unixtime(paytime) as upaytime FROM ".tablename('yhzc_crowd_report')." {$where} order by status desc,id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
    public function getreport_count($where='',$wheres=''){
        global $_W;
        if($where!=''){
            $where="where uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_crowd_report')." ".$where,$wheres);
    }
    public function getreportreward_all($where='',$wheres=array(),$page=1,$limit=10){
        global $_W;
        if($where!=''){
            $where="where cr.uniacid=:uniacid and {$where}";
            $wheres[':uniacid']=$_W['uniacid'];
        }else{
            exit;
        }
        return pdo_fetchall("SELECT cr.*,from_unixtime(cr.addtime) as uaddtime,from_unixtime(cr.paytime) as upaytime,from_unixtime(cr.reward_time) as ureward_time,ua.name as uaname,ua.phone as uaphone,ua.country as country,ua.province as uaprovince,ua.city as uacity,ua.other as uaother,ua.zipcode as uazipcode,crd.reward_type as reward_type,crd.reward_money as reward_money,crd.reward_title as reward_title,crd.reward_content as reward_content,crd.reward_day as reward_day FROM ".tablename('yhzc_crowd_report')." as cr LEFT JOIN ".tablename('yhzc_user_address')." as ua ON cr.reward_address_id=ua.id LEFT JOIN ".tablename('yhzc_crowd_reward')." as crd ON cr.reward_id=crd.id {$where} order by cr.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
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
    public function getmarch_all($crowd_id=0){
        global $_W;
        return pdo_getall('yhzc_crowd_march', array('uniacid' => $_W['uniacid'],'crowd_id'=>$crowd_id),array('*,from_unixtime(addtime) as uaddtime') , '' , array('id desc'));
    }
    public function add_report($crowd=false,$reward=false,$union=false){
        global $_W;
        if($crowd==false){
            return false;
        }
        $time=time();
        $money=$this->input->post('money');
        $message=$this->input->post('message');
        $paytype=$this->input->post('paytype');
        if($money<=0){
            die(json_encode(array('id'=>-1,'msg'=>'支持金额不能为小于等于0')));
        }
        if(mysys('cashpay')!='1' && $paytype=='0'){
            die(json_encode(array('id'=>-1,'msg'=>'不支持的支付类型')));
        }
        $rptwhere=array('uniacid' => $_W['uniacid'],'crowd_id' => $crowd['id'],'user_id' => $_W['member']['uid'],'status' =>0);
        if($crowd['ntype']=='1'){
            if($union['cjmoney']+$money>$crowd['money']){
                die(json_encode(array('id'=>-1,'msg'=>'最大支持金额为'.($crowd['money']-$union['cjmoney']).'元')));
            }
            $rptwhere['union_id']=$union['id'];
        }else if($crowd['ntype']=='0' && $crowd['cjtype']==0){
            if($crowd['cjmoney']+$money>$crowd['money']){
                die(json_encode(array('id'=>-1,'msg'=>'最大支持金额为'.($crowd['money']-$crowd['cjmoney']).'元')));
            }
        }
        pdo_delete('yhzc_crowd_report',array('uniacid' => $_W['uniacid'],'status' =>0,'addtime <='=>$time-3600));
        $data = array(
            'uniacid' => $_W['uniacid'],
            'crowd_id' => $crowd['id'],
            'union_id' => 0,
            'user_id' => $_W['member']['uid'],
            'realname' =>$_W['member']['realname'],
            'money' => $money,
            'wx_orderid' =>0,
            'message' => $message,
            'addtime' => $time,
            'paytime' =>0,
            'paytype' =>$paytype==''?1:$paytype,
            'status' =>0,
        );
        $union_id=$this->input->post('union_id');
        if($crowd['ntype']=='1'){
            $data['union_id']=$union['id'];
        }
        $data['nickname']=@$_W['fans']['tag']['nickname'];
        $data['avatar']=@$_W['fans']['tag']['avatar'];
        $data['openid']=@$_W['fans']['tag']['openid'];
        if(is_array($reward)){
            $data['reward_id']=$reward['id'];
            if($reward['reward_type']=='0'){
                $address_id=$this->input->post('address_id');
                if($address_id==''){
                    die(array('id'=>-1,'msg'=>'请选择或添加收货地址'));
                }
                $data['reward_address_id']=$address_id;
            }
            $fdarr=unserialize($reward['reward_fd']);
            if(@is_array($fdarr)){
                $reward_fd=$this->input->post('reward_fd');
                $fdarr_s=array();
                foreach($fdarr as $kd=>$fd){
                    if(!isset($reward_fd[$kd]) && @$reward_fd[$kd]==''){
                        die(json_encode(array('id'=>-1,'msg'=>'请填写必填字段后提交')));
                    }else{
                        $fdarr_s[]=$reward_fd[$kd];
                    }
                }
                $data['reward_other']=serialize($fdarr_s);
            }
            $data['reward_status']=0;
            $data['reward_time']=0;
            $reward_num=$this->input->post('reward_num');
            $data['reward_num']=$reward_num<=0?1:$reward_num;
            if($reward['reward_count']+$data['reward_num']>$reward['reward_limit'] && $reward['reward_limit']!=0){
                die(json_encode(array('id'=>-1,'msg'=>'此回报项次数不足啦！')));
            }
            $thisrptnum=pdo_get('yhzc_crowd_report', array('user_id' =>$_W['member']['uid'],'uniacid'=>$_W['uniacid'],'reward_id'=>$reward['id']), array('sum(reward_num) as tsum'));
            if($thisrptnum['tsum']+$data['reward_num']>$reward['reward_marknum'] && $thisrptnum['tsum']+$data['reward_num']>1){
                die(json_encode(array('id'=>-1,'msg'=>'您已支持'.$thisrptnum['tsum'].'次，剩余支持次数为'.(($reward['reward_marknum']==0?1:$reward['reward_marknum'])-$thisrptnum['tsum'].'次'))));
            }
            $reward_money=$reward['reward_money']*$data['reward_num'];
            if($data['money']!=$reward_money){
                die(json_encode(array('id'=>-1,'msg'=>'系统错误0025')));
            }
        }
        $result = pdo_insert('yhzc_crowd_report', $data);
        if (!empty($result)) {
            $id = pdo_insertid();
            return array('id'=>$id,'money'=>$data['money']);
        }else{
            return false;
        }
    }

    /**
     * @notes: 添加众筹信息
     * @author tongwz
     * @date: 2019年6月29日15:11:44
     * @param $crowd
     * @return mixed
     */
    public function add_union($crowd){
        global $_W;
        $time=time();
        $realname=$this->input->post('realname');
        $phone=$this->input->post('phone');
        $summary=$this->input->post('summary');
        $team_id=$this->input->post('team_id');
        $theme=$this->input->post('theme');
        $img = $this->input->post('img');
        $check = array_filter($img);
        if (empty($check)) die(json_encode(array('id'=>-1,'msg'=>'请上传您的作品')));
        $describe = $this->input->post('describe');
        $production = [];
        foreach ($img as $key => $val) {
            $production[] = [
                'img' => $val,
                'describe' => isset($describe[$key]) ? $describe[$key] : '',
            ];
        }

        $experience = $this->input->post('experience');
        if (!empty($experience)) {
            $experience = array_filter($experience);
            if (empty($experience)) die(json_encode(array('id'=>-1,'msg'=>'请写入您的经历')));
        } else {
            die(json_encode(array('id'=>-1,'msg'=>'请写入您的经历')));
        }
        if($realname==''){
            die(json_encode(array('id'=>-1,'msg'=>'请留下您的真实姓名')));
        }
        if($phone==''){
            die(json_encode(array('id'=>-1,'msg'=>'请留下您的手机号码')));
        }
        if($summary==''){
            die(json_encode(array('id'=>-1,'msg'=>'一个响亮的口号可以让您筹到更多哦')));
        }
        if(mb_strlen($summary,'UTF8')>20){
            die(json_encode(array('id'=>-1,'msg'=>'口号长度最大为20个字符')));
        }
        if (empty($production)) {
            die(json_encode(array('id'=>-1,'msg'=>'请上传您的作品')));
        }
        pdo_begin();
        try{
            $data = array(
                'uniacid' => $_W['uniacid'],
                'crowd_id' => $crowd['id'],
                'user_id' =>  $_W['member']['uid'],
                'nickname' => @$_W['fans']['tag']['nickname'],
                'realname' =>$realname,
                'avatar' =>@$_W['fans']['tag']['avatar'],
                'phone' => $phone,
                'summary' => $summary,
                'ppnum' =>0,
                'sharenum' =>0,
                'cjmoney' =>0,
                'addtime' => $time,
                'updatetime' => $time, // 增加更新时间
                'experience' => json_encode($experience,JSON_UNESCAPED_UNICODE), // 经历
                'production' => json_encode($production,JSON_UNESCAPED_UNICODE), // 作品
                'starttime' =>0,
                'status'=>0,
                'theme'=>$theme,
                'team_id'=>$team_id
            );
            if($crowd['bmstatus']=='0'){
                $data['starttime']=$time;
                $data['status']=1;
                //通知发送
            }
            if(isset($_SESSION['union_'.$crowd['id'].'_f_uid'])){
                $data['f_uid']=$_SESSION['union_'.$crowd['id'].'_f_uid'];
                unset($_SESSION['union_'.$crowd['id'].'_f_uid']);
            }
            $result = pdo_insert('yhzc_crowd_union', $data);
            if (!empty($result)) {
                $id = pdo_insertid();
                pdo_update('yhzc_crowd',array('bmnum +='=>1), array('id' => $crowd['id']));
                $rpttotal = pdo_fetchcolumn("select count(id) from ".tablename('yhzc_crowd_union')." where user_id={$_W['member']['uid']} and crowd_id={$crowd['id']} and uniacid={$_W['uniacid']}");
                if($rpttotal>1){
                    throw new Exception('已经报名成功，多多分享');
                }
                $bmcount=$this->getcrowd_union_count(false);
                if($crowd['bmlimit']<$bmcount['count'] && $crowd['bmlimit']!=0){
                    throw new Exception('该联合众筹名额已满，请关注其他项目');
                }
            }else{
                throw new Exception('报名失败');
            }
        }catch(Exception $e){
            $msg=$e->getMessage();
            pdo_rollback();
            die(json_encode(array('id'=>-1,'msg'=>$msg)));
        }
        pdo_commit();
        return $id;
    }
    public function getcrowd_team_all($page=0,$limit=10){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        if($crowd_id==''){
            return false;
        }
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id']=$crowd_id;
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=5;
        }
        $row=pdo_fetchall("select t.*,sum(r.money) as rmoney,count(distinct u.id) as ucount,count(r.id) as rcount from ".tablename('yhzc_crowd_team')." as t left join ".tablename('yhzc_crowd_union')." as u on u.crowd_id=t.crowd_id and u.team_id=t.id left join ".tablename('yhzc_crowd_report')." as r on r.union_id=u.id and r.status=1 where t.crowd_id=:crowd_id and t.uniacid=:uniacid and t.status=1 group by t.id order by t.id desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
        return $row;
    }
    public function getcrowd_team_count($key=false){
        global $_W;
        $crowd_id=$this->input->get('crowd_id');
        $wheres[':uniacid']=$_W['uniacid'];
        $wheres[':crowd_id']=$crowd_id;
        return pdo_fetch("SELECT count(id) as count FROM ".tablename('yhzc_crowd_team')." t where t.crowd_id=:crowd_id and t.uniacid=:uniacid and t.status=1",$wheres);
    }
    public function getmyunion_all($page=0,$limit=10){
        global $_W;
        $time=time();
        $where="where u.uniacid=:uniacid and u.user_id={$_W['member']['uid']}";
        $st=$this->input->get('st');
        if($st==0 || $st==''){
            //进行中
            $where.=" and c.endtime>{$time} and c.starttime<={$time} and u.cjmoney<c.money";
        }else if($st=='1'){
            //成
            $where.=" and u.cjmoney>=c.money";
        }else if($st=='-1'){
            //败
            $where.=" and c.endtime<{$time} and u.cjmoney<c.money";
        }
        $wheres[':uniacid']=$_W['uniacid'];
        $page=$this->input->get('page');
        $limit=$this->input->get('limit');
        if($page==0){
            $page=1;
        }
        if(!$limit){
            $limit=10;
        }
        return pdo_fetchall("SELECT c.*,u.cjmoney as ucjmoney,u.id as union_id,CEILING((u.cjmoney/c.money)*100) as pernum,from_unixtime(c.starttime) as cstarttime,from_unixtime(u.addtime) as uaddtime,count(r.id) as rcount FROM ".tablename('yhzc_crowd_union')." u left join ".tablename('yhzc_crowd')." c ON c.id=u.crowd_id LEFT JOIN ".tablename('yhzc_crowd_report')." r ON r.crowd_id=c.id ".$where." group by c.id order by u.addtime desc LIMIT ".($page-1)*$limit.",".$limit,$wheres);
    }
}