<?php

//decode by http://www.yunlu99.com/
defined("BASEPATH") or exit("No direct script access allowed");
class Task extends CI_Controller
{
	public $w;
	private $itime = 30;
	public function __construct()
	{
		parent::__construct();
		global $_W;
		$this->w = $_W;
		$this->load->helper(array("url_helper", "file", "form"));
	}
	public function auto()
	{
		global $_W;
		$time = time();
		$cache = cache_load("task-" . $_W["uniacid"]);
		if ($cache["auto"] == "off") {
			die(json_encode(array("id" => -1, "msg" => "自动任务已关闭，请重新开启")));
		}
		$getpara = $this->input->get(null, true);
		$osign = $getpara["sign"];
		unset($getpara["sign"]);
		$osecret = @$cache["secret"];
		$oosign = getSignature($getpara, $osecret);
		if ($osign != $oosign) {
			die(json_encode(array("id" => -1, "msg" => "致命错误，无法开启自动任务")));
		}
		ignore_user_abort(true);
		ob_end_clean();
		header("Connection: close");
		header("HTTP/1.1 200 OK");
		ob_start();
		echo json_encode(array("id" => 1, "msg" => "自动任务开启成功，即将运行"));
		$size = ob_get_length();
		header("Content-Length: {$size}");
		ob_end_flush();
		flush();
		if (function_exists("fastcgi_finish_request")) {
			fastcgi_finish_request();
		}
		$interval = 1;
		$start = 0;
		$step = 40;
		OmdEV:
		$crowd = pdo_fetchall("select id,ntype,money,cjmoney from " . tablename("yhzc_crowd") . " where taskst=0 and uniacid={$_W["uniacid"]} and endtime<={$time} and status>0 order by endtime asc limit " . $start * 20 . "," . $step);
		if (!empty($crowd)) {
			foreach ($crowd as $r) {
				$data = array("uniacid" => $_W["uniacid"], "tskid" => md5("crowd" . $r["id"]), "uid" => 0, "status" => 0, "level" => 0);
				if ($r["ntype"] == "1") {
					$data["ntype"] = 1;
					$data["taskinfo"] = serialize(array("crowd_id" => $r["id"], "num" => 0, "ntype" => $r["ntype"]));
				} else {
					if ($r["ntype"] == "0") {
						$data["ntype"] = 0;
						$data["taskinfo"] = serialize(array("crowd_id" => $r["id"], "num" => 0, "ntype" => $r["ntype"], "fail" => $r["cjmoney"] >= $r["money"] ? "0" : "1"));
					}
				}
				$result = pdo_insert("yhzc_task", $data);
				if (!empty($result)) {
					pdo_update("yhzc_crowd", array("taskst" => 1), array("id" => $r["id"]));
				}
			}
			$start++;
			sleep($interval);
			if (true) {
				goto OmdEV;
			}
		}
		$this->runtask();
		$secret = rand(100000, 999999);
		$secret = md5($time . $secret);
		$getpara["tms"] = $time;
		$cache["secret"] = $secret;
		$cache["timestamp"] = $time;
		$cache["auto"] = "on";
		cache_write("task-" . $_W["uniacid"], $cache);
		$url = appweburl("task", "auto", array("tms" => $time, "sign" => getSignature($getpara, $secret)));
		sleep($this->itime);
		$var = file_get_contents($url);
		exit;
	}
	public function ontask($id = 0)
	{
		global $_W;
		$ff = $this->input->get("do", true);
		if ($ff != "r" || $_W["uid"] == '') {
			exit;
		}
		checklogin();
		$cache = cache_load("task-" . $_W["uniacid"]);
		if ($cache["auto"] == "on") {
			die(json_encode(array("id" => -1, "msg" => "自动任务已经开启，请先结束")));
		}
		$cache["auto"] = "start";
		cache_write("task-" . $_W["uniacid"], $cache);
		$time = time();
		$getpara = array("i" => $_W["uniacid"], "c" => "entry", "do" => "web", "m" => "yh_zhongchou", "mc" => "task", "mm" => "auto");
		$getpara["tms"] = $time;
		$secret = rand(100000, 999999);
		$secret = md5($time . $secret);
		$cache["secret"] = $secret;
		cache_write("task-" . $_W["uniacid"], $cache);
		$sign = getSignature($getpara, $secret);
		$url = appweburl("task", "auto", array("tms" => $time, "sign" => $sign));
		$var = file_get_contents($url);
		die($var);
	}
	public function check()
	{
		global $_W;
		$time = time();
		$ff = $this->input->get("do", true);
		if ($ff != "r" || $_W["uid"] == '') {
			exit;
		}
		checklogin();
		$cache = cache_load("task-" . $_W["uniacid"]);
		if ($cache["auto"] == "off") {
			die(json_encode(array("id" => -1, "msg" => "自动任务关闭状态")));
		} else {
			if ($time - $cache["timestamp"] > $this->itime + 15) {
				$cache["auto"] = "off";
				cache_write("task-" . $_W["uniacid"], $cache);
				die(json_encode(array("id" => -1, "msg" => "自动任务运行超时，已关闭")));
			} else {
				if ($cache["auto"] == "on") {
					die(json_encode(array("id" => 1, "msg" => "任务进行中")));
				}
			}
		}
	}
	public function offtask($id = 0)
	{
		global $_W;
		$ff = $this->input->get("do", true);
		if ($ff != "r" || $_W["uid"] == '') {
			exit;
		}
		checklogin();
		$cache = cache_load("task-" . $_W["uniacid"]);
		$cache["auto"] = "off";
		cache_write("task-" . $_W["uniacid"], $cache);
		die(json_encode(array("id" => 1, "msg" => "自动任务关闭成功")));
	}
	private function runtask($taskid = 0)
	{
		global $_W;
		if ($taskid == 0) {
			$task = pdo_fetch("select * from " . tablename("yhzc_task") . " where status=0 and uniacid={$_W["uniacid"]} order by level desc limit 1");
		} else {
			$task = pdo_fetch("select * from " . tablename("yhzc_task") . " where status=0 and uniacid={$_W["uniacid"]} and id={$taskid}");
		}
		if (!empty($task)) {
			pdo_update("yhzc_task", array("status" => 1), array("id" => $task["id"]));
			switch ($task["ntype"]) {
				case 0:
					$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg", "uniacid" => $_W["uniacid"]));
					if (!empty($wxmsg)) {
						$tpl = unserialize($wxmsg["value"]);
					} else {
						return false;
					}
					$interval = 1;
					$start = 0;
					$step = 40;
					$taskinfo = unserialize($task["taskinfo"]);
					if ($taskinfo["fail"] == "0" && @$tpl["success"] == '' || $taskinfo["fail"] == "1" && @$tpl["failed"] == '') {
						return false;
					}
					xn5Cr:
					$report = pdo_fetchall("select r.*,c.title,c.starttime as cstarttime,c.endtime as cendtime,c.money as cmoney from " . tablename("yhzc_crowd_report") . " as r left join " . tablename("yhzc_crowd") . " as c on c.id=r.crowd_id where r.crowd_id={$taskinfo["crowd_id"]} and r.id>{$taskinfo["num"]} and r.uniacid={$_W["uniacid"]} and r.status=1 order by r.id asc limit " . $start * 20 . "," . $step);
					if (!empty($report)) {
						foreach ($report as $r) {
							$rid = $r["id"];
							if ($r["openid"] == '') {
								continue;
							}
							$acc = mc_notice_init();
							if ($taskinfo["fail"] == "0") {
								$tpldata = array("first" => array("value" => "您参与的众筹项目已筹款完成", "color" => "#173177"), "keyword1" => array("value" => $r["title"]), "keyword2" => array("value" => date("Y-m-d H:i:s", $r["cstarttime"])), "keyword3" => array("value" => date("Y-m-d H:i:s", $r["cendtime"])), "remark" => array("value" => "点击查看详情"));
								$acc->sendTplNotice($r["openid"], $tpl["success"], $tpldata, $url = $_W["siteroot"] . "app/index.php?i={$r["uniacid"]}&c=entry&do=r&m=yh_zhongchou&mc=crowd&mm=detail&id={$r["crowd_id"]}", $topcolor = "#FF683F");
							} else {
								$tpldata = array("first" => array("value" => "您参与的众筹项目筹款失败，我们将尽快处理退款", "color" => "#173177"), "keyword1" => array("value" => $r["title"]), "keyword2" => array("value" => number_format($r["cmoney"], 2) . "元"), "keyword3" => array("value" => number_format($r["money"], 2) . "元"), "remark" => array("value" => "点击查看详情"));
								$acc->sendTplNotice($r["openid"], $tpl["failed"], $tpldata, $url = $_W["siteroot"] . "app/index.php?i={$r["uniacid"]}&c=entry&do=r&m=yh_zhongchou&mc=crowd&mm=detail&id={$r["crowd_id"]}", $topcolor = "#FF683F");
							}
						}
						$taskinfo["num"] = $rid;
						pdo_update("yhzc_task", array("taskinfo" => serialize($taskinfo)), array("id" => $task["id"]));
						$start++;
						sleep($interval);
						if (true) {
							goto xn5Cr;
						}
						break;
					}
					pdo_update("yhzc_task", array("status" => -1), array("id" => $task["id"]));
					break;
				case 1:
					$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg", "uniacid" => $_W["uniacid"]));
					if (!empty($wxmsg)) {
						$tpl = unserialize($wxmsg["value"]);
					} else {
						return false;
					}
					$interval = 1;
					$start = 0;
					$step = 40;
					$taskinfo = unserialize($task["taskinfo"]);
					if (@$tpl["failed"] == '') {
						return false;
					}
					NLfT1:
					$union = pdo_fetchall("select u.*,c.title,c.starttime as cstarttime,c.endtime as cendtime,c.money as cmoney,m.openid from " . tablename("yhzc_crowd_union") . " as u left join " . tablename("yhzc_crowd") . " as c on c.id=u.crowd_id left join " . tablename("yhzc_user") . " as m on m.uid=u.user_id where u.crowd_id={$taskinfo["crowd_id"]} and u.id>{$taskinfo["num"]} and u.uniacid={$_W["uniacid"]} and u.status=1 order by u.id asc limit " . $start * 20 . "," . $step);
					if (!empty($union)) {
						foreach ($union as $u) {
							$union_id = $u["id"];
							if ($u["openid"] == '') {
								continue;
							}
							$acc = mc_notice_init();
							$tpldata = array("first" => array("value" => "您参与的众筹项目筹款失败，所有已筹金额我们将尽快原路退回给支持您的用户", "color" => "#173177"), "keyword1" => array("value" => $u["title"]), "keyword2" => array("value" => number_format($u["cmoney"], 2) . "元"), "keyword3" => array("value" => number_format($u["cjmoney"], 2) . "元（已筹）"), "remark" => array("value" => "点击查看详情"));
							$acc->sendTplNotice($u["openid"], $tpl["failed"], $tpldata, $url = $_W["siteroot"] . "app/index.php?i={$_W["uniacid"]}&c=entry&do=r&m=yh_zhongchou&mc=crowd&mm=uniondetail&union_id={$u["id"]}", $topcolor = "#FF683F");
						}
						$taskinfo["num"] = $union_id;
						pdo_update("yhzc_task", array("taskinfo" => serialize($taskinfo)), array("id" => $task["id"]));
						$start++;
						sleep($interval);
						if (true) {
							goto NLfT1;
						}
						break;
					}
					pdo_update("yhzc_task", array("status" => -1), array("id" => $task["id"]));
					break;
				case 2:
					$interval = 1;
					$start = 0;
					$step = 40;
					$taskinfo = unserialize($task["taskinfo"]);
					uDzmV:
					$report = pdo_fetchall("SELECT r.*,c.title FROM " . tablename("yhzc_crowd_report") . " as r left join " . tablename("yhzc_crowd") . " as c on c.id=r.crowd_id WHERE r.crowd_id={$taskinfo["crowd_id"]} and r.status=1 and r.uniacid={$_W["uniacid"]} order by r.id asc limit " . $start * 20 . "," . $step);
					if (!empty($report)) {
						foreach ($report as $r) {
							$rid = $r["id"];
							$refund = wechatrefund($r);
							if ($refund) {
								$cj = pdo_fetch("SELECT sum(money) as money FROM " . tablename("yhzc_crowd_report") . " WHERE status = 1 and uniacid=:uniacid and crowd_id={$taskinfo["crowd_id"]} LIMIT 1", array(":uniacid" => $_W["uniacid"]));
								$cgmy = pdo_update("yhzc_crowd", array("cjmoney" => $cj["money"]), array("id" => $taskinfo["id"], "uniacid" => $_W["uniacid"]));
								$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg", "uniacid" => $_W["uniacid"]));
								if (!empty($wxmsg)) {
									$tpl = unserialize($wxmsg["value"]);
								} else {
									return false;
								}
								if (@$tpl["refund"] != '') {
									$acc = mc_notice_init();
									$tpldata = array("first" => array("value" => "您所支持的众筹项目【" . $r["title"] . "】已退款", "color" => "#173177"), "reason" => array("value" => "众筹失败或其他"), "refund" => array("value" => $r["money"] . "元"), "remark" => array("value" => "支持金额将原路退回，请注意查收"));
									$acc->sendTplNotice($r["openid"], $tpl["refund"], $tpldata, '', $topcolor = "#FF683F");
								}
							}
						}
						$taskinfo["num"] = $rid;
						pdo_update("yhzc_task", array("taskinfo" => serialize($taskinfo)), array("id" => $task["id"]));
						$start++;
						sleep($interval);
						if (true) {
							goto uDzmV;
						}
						break;
					}
					pdo_update("yhzc_task", array("status" => -1), array("id" => $task["id"]));
					break;
				case 3:
					$interval = 1;
					$start = 0;
					$step = 40;
					$taskinfo = unserialize($task["taskinfo"]);
					iwOLN:
					$report = pdo_fetchall("SELECT r.*,u.user_id as uuser_id,u.realname as urealname,u.cjmoney FROM " . tablename("yhzc_crowd_report") . " as r left join " . tablename("yhzc_crowd_union") . " as u on u.id=r.union_id left join " . tablename("yhzc_crowd") . " as c on c.id=r.crowd_id WHERE r.crowd_id={$taskinfo["crowd_id"]} and r.status=1 and r.uniacid={$_W["uniacid"]} and r.union_id>0 and u.cjmoney<c.money order by r.id asc limit " . $start * 20 . "," . $step);
					if (!empty($report)) {
						foreach ($report as $r) {
							$rid = $r["id"];
							$refund = wechatrefund($r);
							if ($refund) {
								$cj = pdo_fetch("SELECT sum(money) as money FROM " . tablename("yhzc_crowd_report") . " WHERE status = 1 and uniacid=:uniacid and union_id={$r["union_id"]} LIMIT 1", array(":uniacid" => $_W["uniacid"]));
								$cgmy = pdo_update("yhzc_crowd_union", array("cjmoney" => $cj["money"]), array("id" => $r["union_id"], "uniacid" => $_W["uniacid"]));
								$totalmy = pdo_fetch("SELECT sum(cjmoney) as money FROM " . tablename("yhzc_crowd_union") . " WHERE crowd_id = {$r["crowd_id"]} and uniacid=:uniacid LIMIT 1", array(":uniacid" => $_W["uniacid"]));
								$ttcgmy = pdo_update("yhzc_crowd", array("cjmoney" => $totalmy["money"]), array("id" => $r["crowd_id"], "uniacid" => $_W["uniacid"]));
								$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg", "uniacid" => $_W["uniacid"]));
								if (!empty($wxmsg)) {
									$tpl = unserialize($wxmsg["value"]);
								} else {
									return false;
								}
								if (@$tpl["refund"] != '') {
									$acc = mc_notice_init();
									$tpldata = array("first" => array("value" => "您所支持的好友【" . $r["urealname"] . "】参与的众筹项目已退款", "color" => "#173177"), "reason" => array("value" => "筹款失败或其他（自）"), "refund" => array("value" => $r["money"] . "元"), "remark" => array("value" => "支持金额将原路退回，请注意查收"));
									$acc->sendTplNotice($r["openid"], $tpl["refund"], $tpldata, '', $topcolor = "#FF683F");
								}
							}
						}
						$taskinfo["num"] = $rid;
						pdo_update("yhzc_task", array("taskinfo" => serialize($taskinfo)), array("id" => $task["id"]));
						$start++;
						sleep($interval);
						if (true) {
							goto iwOLN;
						}
						break;
					}
					pdo_update("yhzc_task", array("status" => -1), array("id" => $task["id"]));
					break;
				default:
			}
		} else {
			return false;
		}
	}
}