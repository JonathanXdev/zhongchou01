<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Collect extends CI_Controller {
    public $w;
    public function __construct(){
        parent::__construct();
        global $_W;
        $this->w=$_W;
        $this->load->helper(array('url_helper','file','form'));
        checkauth();
    }
	public function index(){
        if($_POST){
            $bsid=$this->input->post('bsid');
            $nfrom=$this->input->post('nfrom');
            $title=$this->input->post('title');
            $link=$this->input->post('link');
            if(!iscollect($bsid,$nfrom)){
                if(addcollect($bsid,$nfrom,$title,$link)){
                    die(json_encode(array('id'=>1,'data'=>1)));
                }
            }else{
                if(removecollect($bsid,$nfrom)){
                    die(json_encode(array('id'=>1,'data'=>0)));
                };
            }
        }
	}
}
