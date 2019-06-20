<?php

//decode by http://www.yunlu99.com/
defined("BASEPATH") or exit("No direct script access allowed");
class User extends CI_Controller
{
	public $w;
	public function __construct()
	{
		parent::__construct();
		checklogin();
		global $_W;
		$this->w = $_W;
		$this->load->helper(array("url_helper", "file", "form"));
		$this->load->model("user_model");
		$this->load->library("wxpay");
	}
	public function pay()
	{
		global $_W;
		$sys = mysys(["wxpay", "sysname"]);
		$wxpay = unserialize($sys["wxpay"]);
		$mchid = $wxpay["mchid"];
		$appid = $_W["account"]["key"];
		$appKey = $_W["account"]["secret"];
		$apiKey = $wxpay["apikey"];
		include_once "system/libraries/Wxpay.php";
		$wxPay = new CI_Wxpay($mchid, $appid, $appKey, $apiKey);
		$openId = $_W["openid"];
		if (!$openId) {
			exit("获取openid失败");
		}
		$outTradeNo = date("YmdHis") . $oid;
		$payAmount = $money;
		$sendName = @$sys["sysname"] == '' ? "完美众筹" : $sys["sysname"];
		$wishing = urldecode("提现到账啦，请注意查收！");
		$act_name = urldecode("红包提现");
		$rsps = $wxPay->createTransfers($openId, $payAmount, $outTradeNo, $sendName, $wishing, $act_name);
		if ($rsps != false && $rsps->result_code == "SUCCESS") {
			$ludata = array("status" => 1, "oktime" => @strtotime($rsps->payment_time), "info" => $rsps->payment_no);
			$result = pdo_update(tables("money_log"), $ludata, array("id" => $oid));
			if (!empty($result)) {
				return $this->result(0, "提现成功，请注意查收！");
			} else {
				$result = pdo_update(tables("money_log"), $ludata, array("id" => $oid));
				return $this->result(0, "提现成功，请注意查收！");
			}
		} else {
			$ludata = array("status" => -1, "info" => "提现失败");
			$result = pdo_update(tables("money_log"), $ludata, array("id" => $oid));
			$uvar = pdo_update(tables("user"), array("money +=" => $money), array("uid" => $user["uid"]));
			if (!empty($result)) {
				return $this->result(-1, "提现出错啦！");
			}
		}
	}
}