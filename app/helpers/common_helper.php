<?php
/*
 * 完美众筹版权所有，盗版必究
 * */
function mversion(){
    return '2.4.6';
}
function segment($name='mc'){
    $CI =&get_instance();
    return $CI->input->get($name);
}
function paginations($arr,$mc='crowd',$mm='clist'){
    $pages=ceil($arr[0]/$arr[2]);
    $pagehtml='<div class="dataTables_info">共'.$arr[0].'条 第'.$arr[1].'/'.$pages.'页</div>
               <div class="dataTables_paginate paging_simple_numbers">';

    if($arr[1]>1){
        $pagehtml.='<a class="paginate_button previous" href="'.weburl($mc,$mm,'page=1&limit='.$arr[2]).'">首页</a><a class="paginate_button previous" href="'.weburl($mc,$mm,'page='.($arr[1]-1).'&limit='.$arr[2]).'">上一页</a>';
    }
    $pagehtml.='<span>';
    $annum=2-($pages-$arr[1]);
    $bnnum=2-($arr[1]-1);
    if($pages>=5){
        $istart=($arr[1]-2)<=0?1:($arr[1]-2);
        if($annum>0 && $istart-$annum>=1){
            $istart-=$annum;
        }
        $iend=($arr[1]+2)>$pages?$pages:($arr[1]+2);
        if($bnnum>0 && $arr[1]+$bnnum<=$pages){
            $iend+=$bnnum;
        }
    }else{
        $istart=1;
        $iend=$pages;
    }
    for($i=$istart;$i<=$iend;$i++){
        $cls=$i==$arr[1]?'current':'';
        $pagehtml.='<a class="paginate_button '.$cls.'" href="'.weburl($mc,$mm,'page='.$i.'&limit='.$arr[2]).'">'.$i.'</a>';
    }

    $pagehtml.='</span>';
    if($arr[1]<$pages){
        $pagehtml.='<a class="paginate_button next" href="'.weburl($mc,$mm,'page='.($arr[1]+1).'&limit='.$arr[2]).'">下一页</a><a class="paginate_button previous" href="'.weburl($mc,$mm,'page='.$pages.'&limit='.$arr[2]).'">尾页</a>';
    }

    $pagehtml.='</div>';
    return $pagehtml;
}
function menuarr(){
    global $_W;
    $menu=pdo_getall('yhzc_role',array('display'=>1),array() , '' , array('sort desc'));
    $urow=pdo_fetch("SELECT m.*,r.role_name,r.role,g.group_name FROM ".tablename('yhzc_manage')." as m LEFT JOIN ".tablename('yhzc_manage_role')." as r ON m.role_id=r.role_id LEFT JOIN ".tablename('yhzc_manage_group')." as g ON m.group_id=g.group_id WHERE m.user_id = :user_id and m.uniacid=:uniacid LIMIT 1", array(':user_id' => $_W['uid'],':uniacid'=>$_W['uniacid']));
    $urolearr=explode(',',$urow['role']);
    $parentarr=array();
    foreach($menu as $k=>$m){
        if($m['pid']==0){
            $parentarr[$m['id']]= $m;
        }else{
            if($_W['isfounder']==1){
                $parentarr[$m['pid']]['sub'][]=$m;
            }else{
                if(in_array($m['id'],$urolearr)){
                    $parentarr[$m['pid']]['sub'][]=$m;
                }
            }
        }
    }
    return $parentarr;
}
function getrole($result=false){
    global $_W;
    $role=pdo_getall('yhzc_role',array(),array() , '' , array('mc desc'));
    if($result===false){
        return $role;
    }else{
        $rolearr=array();
        foreach($role as $r){
            $rolearr[$r['id']]=array(
                'mc'=>$r['mc'],
                'mm'=>$r['mm'],
                'name'=>$r['name'],
            );
        }
        return $rolearr;
    }
}
function role_result($role,$id=0){
    $rolearr=array();
    foreach($role as $r){
        if($id>0 && $r['id']==$id){
            return $r;
        }else {
            $rolearr[$r['id']] = array(
                'mc' => $r['mc'],
                'mm' => $r['mm'],
                'name' => $r['name'],
            );
        }
    }
    return $rolearr;
}
function checkrole($mcs=false,$mms=false){
    global $_W;
    if($_W['isfounder']==1){
        return true;
    }
    if($mcs!==false){
        $mc=$mcs;
    }else{
        $mc=segment('mc');
    }
    if($mms!==false){
        $mm=$mms;
    }else{
        $mm=segment('mm');
    }
    $urow=pdo_fetch("SELECT m.*,r.role_name,r.role,g.group_name FROM ".tablename('yhzc_manage')." as m LEFT JOIN ".tablename('yhzc_manage_role')." as r ON m.role_id=r.role_id LEFT JOIN ".tablename('yhzc_manage_group')." as g ON m.group_id=g.group_id WHERE m.user_id = :user_id and m.uniacid=:uniacid LIMIT 1", array(':user_id' => $_W['uid'],':uniacid'=>$_W['uniacid']));
    if(empty($urow)){
        return false;
    }
    $sysrole=pdo_get('yhzc_role', array('mc'=>$mc,'mm'=>$mm));
    if(empty($sysrole)){
        return true;
    }
    $urolearr=explode(',',$urow['role']);
    if(in_array($sysrole['id'],$urolearr)){
        return true;
    }else{
        return false;
    }
}
function unicode_encode($name){
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }
        else
        {
            $str .= $c2;
        }
    }
    return $str;
}
function unicode_decode($name){
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches))
    {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0)
            {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            }
            else
            {
                $name .= $str;
            }
        }
    }
    return $name;
}

function iscollect($bsid=0,$nfrom=0){
    global $_W;
    $u_id=$_W['member']['uid'];
    $strow = pdo_get('yhzc_user_collect', array('uniacid'=>$_W['uniacid'],'bsid'=>$bsid,'u_id'=>$u_id,'nfrom'=>$nfrom));
    if(empty($strow)){
        return false;
    }else{
        return $strow;
    }
}
function addcollect($bsid=0,$nfrom=0,$title='',$link=''){
    global $_W;
    $time=time();
    $u_id=$_W['member']['uid'];
    $uniacid=$_W['uniacid'];
    if(iscollect($bsid,$nfrom)){
        $result =pdo_update('yhzc_user_collect', array('addtime' => $time), array('uniacid'=>$_W['uniacid'],'bsid'=>$bsid,'u_id'=>$u_id,'nfrom'=>$nfrom));
    }else{
        $data=array(
            'uniacid'=>$_W['uniacid'],
            'u_id'=>$u_id,
            'bsid'=>$bsid,
            'nfrom'=>$nfrom,
            'title'=>$title,
            'link'=>$link,
            'addtime'=>$time
        );
        $result = pdo_insert('yhzc_user_collect', $data);
    }
    if($result){
        return true;
    }else{
        return false;
    }
}
function removecollect($bsid=0,$nfrom=0){
    global $_W;
    $u_id=$_W['member']['uid'];
    $uniacid=$_W['uniacid'];
    $result = pdo_delete('yhzc_user_collect', array('uniacid'=>$_W['uniacid'],'bsid'=>$bsid,'u_id'=>$u_id,'nfrom'=>$nfrom));
    if (!empty($result)) {
        return true;
    }else{
        return false;
    }
}
function get_area($t='all'){
    if($t=='all'){
        $area = pdo_getall('yhzc_area');
        $city3=cache_load('city.data.3');
        if(!$city3){
            $city3=getchildarea(-1,$area);
            cache_write('city.data.3', $city3);
        }
        return $city3;
    }else{
        $arr2 = str_split($t, 2);
        $area = pdo_fetch("SELECT a1.area_name as a1name,a2.area_name as a2name,a.area_name as a3name FROM ".tablename('yhzc_area')." as a left join ".tablename('yhzc_area')." as a1 on a1.area_code=".$arr2[0]."0000 left join ".tablename('yhzc_area')." as a2 on a2.area_code=".$arr2[0].$arr2[1]."00  WHERE a.area_code = :area_code LIMIT 1", array(':area_code' => $t));
        return $area;
    }
}
function getchildarea($pid=0,$area=array()){
    if(empty($area)){
        $area = pdo_getall('yhzc_area');
    }
    if($pid==0){
        $pid==-1;
    }
    $arr=array();
    foreach($area as $a){
        if($pid==$a['parent_id']){
            $narr=array(
                'value'=>$a['area_code'],
                'text'=>$a['area_name'],
            );
            if($a['level']<3){
                $narr['children']=getchildarea($a['area_id'],$area);
            }
            $arr[]=$narr;
        }
    }
    return $arr;
}
function getuser(){
    global $_W;
    $user = pdo_get('yhzc_user', array('uid' => $_W['member']['uid'],'uniacid'=>$_W['uniacid']));
    if(empty($user)){
        $user_data = array(
            'uid' =>$_W['member']['uid'],
            'uniacid' => $_W['uniacid'],
            'openid' =>$_W['openid'],
            'name' =>$_W['member']['realname'],
            'phone' =>$_W['member']['mobile'] ,
            'avatar'=>@$_W['fans']['tag']['avatar']==''?'':$_W['fans']['tag']['avatar'],
            'phoneak' => 0,
            'xp' => 10,
            'money' =>0,
            'point' => 0
        );
        $result = pdo_insert('yhzc_user', $user_data);
        if (!empty($result)) {
            $user = pdo_get('yhzc_user', array('uid' => $_W['member']['uid'],'uniacid'=>$_W['uniacid']));
        }
    }else{}
    return $user;
}
function msgcount(){
    global $_W;
    return pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yhzc_msg')." where uid={$_W['member']['uid']} and uniacid={$_W['uniacid']} and status=0 ");
}
if (!function_exists('putmsg')) {
    function putmsg($uid=0,$title,$content){
        global $_W;
        if($title=='' || $content==''){
            return false;
        }
        $data = array(
            'uniacid' =>$_W['uniacid'],
            'uid' => $uid,
            'title'=>$title,
            'msg'=>$content,
            'addtime'=>time(),
            'status'=>0
        );
        $result = pdo_insert('yhzc_msg', $data);
        if (!empty($result)) {
            return true;
        }else{
            return false;
        }
    }
}
function curpageurl()
{
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on")
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80")
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    }
    else
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
?>