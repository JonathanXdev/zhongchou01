<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->library('pagination');
        $this->load->helper(array('url_helper','file','form'));
        //$this->load->model('goodluck_model');

    }
	public function index()
	{
		$this->load->view('welcome_message');
	}
	public function test(){
	    echo 1;
    }
}
