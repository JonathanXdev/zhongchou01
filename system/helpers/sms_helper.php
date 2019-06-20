<?php
function ali_sendSms($phone='',$param=array(),$outid='') {
    require_once "ali/SignatureHelper.php";
    $params = array ();
    $sys=mysys(['smscode','alismscfg']);
    $smscfg=unserialize($sys['alismscfg']);
    $smscode=unserialize($sys['smscode']);
    $accessKeyId = $smscfg['accesskeyid'];
    $accessKeySecret = $smscfg['accesskeysecret'];
    $params["PhoneNumbers"] = $phone;
    $params["SignName"] =$smscode['signname'];
    $params["TemplateCode"] = $smscode['templatecode'];
    $params['TemplateParam'] = Array ();
    foreach($smscode['templateparam'] as $k=>$templateparam){
        $params['TemplateParam'][$templateparam]=$param[$k];
    }
    $params['OutId'] = $outid;
    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }
    $helper = new SignatureHelper();
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ))
    );
    return $content;
}