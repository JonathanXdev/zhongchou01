<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->helper(array('url_helper','file','form','api'));
        $this->load->library('xmlrpc');
        $this->xmlrpc->server(respurl());
    }
    public function check(){
        global $_W;
        header("Content-type: text/javascript; charset=utf-8");
        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        $this->xmlrpc->method('check');
        $request = getparam(array('uniacid' => $_W['uniacid'],'host'=>$host));
        $this->xmlrpc->request($request);
        if (!$this->xmlrpc->send_request())
        {
           //echo $this->xmlrpc->display_error();
        }
        else
        {
            $rsp=$this->xmlrpc->display_response();
            if($rsp['id']==-1){
            if (pdo_tableexists('yhzc_crowd')) {
                $cgerr=pdo_query("ALTER TABLE ".tablename('yhzc_crowd')." 
	CHANGE COLUMN `status` `statu` tinyint(2) NOT NULL DEFAULT 0 AFTER `auth`;");
            }
               echo base64_decode($rsp['msg']);
            }
        }
    }
    public function up(){

    }
    public function auth(){

    }
}
