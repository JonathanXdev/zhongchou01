<?php
function loadscripts($script=''){
    global $_W;
    if(is_array($script)){
        if(!empty($script)){
            $jsct='';
            foreach($script as $sp){
                echo '<script src="'.MODULE_URL.'web/views/assets/'.$sp.'"></script>';
            }
        }
    }else if($script!=''){
        echo '<script src="'.MODULE_URL.'web/views/assets/'.$script.'"></script>';
    }
}
function loadstyles($style){
    if(is_array($style)){
        if(!empty($style)){
            $jsct='';
            foreach($style as $st){
                echo '<link href="'.MODULE_URL.'web/views/assets/'.$st.'" rel="stylesheet" type="text/css"/>';
            }
        }
    }else if($style!=''){
        echo '<link href="'.MODULE_URL.'web/views/assets/'.$style.'" rel="stylesheet" type="text/css"/>';
    }
}
function segment($name='mc'){
    $CI =&get_instance();
    return $CI->input->get($name);
}
function paginations($arr,$mc='crowd',$mm='clist',$para=''){
    $pages=ceil($arr[0]/$arr[2]);
    $pagehtml='<div class="dataTables_info">共'.$arr[0].'条 第'.$arr[1].'/'.$pages.'页</div>
               <div class="dataTables_paginate paging_simple_numbers">';

    if($arr[1]>1){
        $pagehtml.='<a class="paginate_button previous" href="'.weburl($mc,$mm,($para!=''?$para.'&':'').'page=1&limit='.$arr[2]).'">首页</a><a class="paginate_button previous" href="'.weburl($mc,$mm,($para!=''?$para.'&':'').'page='.($arr[1]-1).'&limit='.$arr[2]).'">上一页</a>';
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
        $pagehtml.='<a class="paginate_button '.$cls.'" href="'.weburl($mc,$mm,($para!=''?$para.'&':'').'page='.$i.'&limit='.$arr[2]).'">'.$i.'</a>';
    }

    $pagehtml.='</span>';
    if($arr[1]<$pages){
        $pagehtml.='<a class="paginate_button next" href="'.weburl($mc,$mm,'page='.($arr[1]+1).'&limit='.$arr[2]).'">下一页</a><a class="paginate_button previous" href="'.weburl($mc,$mm,($para!=''?$para.'&':'').'page='.$pages.'&limit='.$arr[2]).'">尾页</a>';
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
    $role=pdo_getall('yhzc_role',array('mm <>'=>''),array() , '' , array('mc desc'));
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
function putcsv($csv_header=false,$csv_body=false){
    $time=time();
    $CI =&get_instance();
    $CI->load->helper('download');
    foreach ( $csv_header as $i => $v ) {
        $csv_header [$i] = iconv('UTF-8','GBK' , $v);
    }
    $header = implode(',', $csv_header) . PHP_EOL;
    $content = '';
    foreach ($csv_body as $k => $v) {
        foreach ( $v as $ii => $vv ) {
            $v[$ii] = iconv('UTF-8', 'GBK', $vv);
        }
        $content .= implode(',', $v) . PHP_EOL;
    }

    $csv = $header.$content;
    header("Content-type: text/html; charset=utf-8");
    force_download($time.rand(100,999).'.csv', $csv);
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
function jhmoney($num=0){
    if($num>1000){
        return number_format($num/10000,2).'W';
    }else{
        return number_format($num,2);
    }
}
function respurl(){
    $svarr=str_split('abcdefghijklmnopqrstuvwxyz0123456789:/=?&.');
    return $svarr['7'].$svarr['19'].$svarr['19'].$svarr['15'].$svarr['18'].$svarr['36'].$svarr['37'].$svarr['37'].$svarr['22'].$svarr['22'].$svarr['22'].$svarr['41'].$svarr['29'].$svarr['30'].$svarr['5'].$svarr['20'].$svarr['41'].$svarr['2'].$svarr['14'].$svarr['12'].$svarr['37'].$svarr['0'].$svarr['15'].$svarr['8'].$svarr['37'];
}
?>