<?php

//decode by http://www.yunlu99.com/
defined("BASEPATH") or exit("No direct script access allowed");
class Crowd extends CI_Controller
{
	public $w;
	public function __construct()
	{
		parent::__construct();
		checklogin();
		global $_W;
		$this->w = $_W;
		$this->load->library("parser");
		$this->load->helper(array("url_helper", "file", "form"));
		$this->load->model("crowd_model");
	}
	public function clist()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		$key = $this->input->get("key");
		$where = '';
		$wheres = array();
		if ($key != '') {
			$where .= " and (c.title like :key or u.name like :key or u.phone=:keys)";
			$wheres[":key"] = "%{$key}%";
			$wheres[":keys"] = $key;
		}
		$data = array("title" => "众筹列表", "description" => "众筹列表", "keywords" => "众筹列表", "w" => $this->w, "key" => $this->input->get("key"), "pagename" => "crowd_list", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js"), array("value" => "js/pages/crowd.js")), "crowd" => $this->crowd_model->getcrowd_all($page, $limit, $key), "pagination" => [$this->crowd_model->getcrowd_count($where, $wheres)["count"], $page ? $page : 1, $limit ? $limit : 10]);
		$this->load->view("framework.html", $data);
	}
	public function start()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$from = $this->input->get("ff");
		if ($from == '') {
			$data = array("title" => "发起众筹|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_add", "styles" => array(), "scripts" => array());
			$this->load->view("framework.html", $data);
		} else {
			$id = $this->input->get("id");
			$data = array("title" => "发起众筹|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_start", "styles" => array(array("value" => "plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"), array("value" => "plugins/bootstrap-tagsinput/bootstrap-tagsinput.css")), "scripts" => array(array("value" => "plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"), array("value" => "plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js"), array("value" => "js/pages/crowd_start.js"), array("value" => "js/city.js")), "ntype" => !$this->input->get("type") ? "0" : $this->input->get("type"), "category" => $this->crowd_model->getcategory());
			if ($id > 0) {
				$data["crowd"] = $this->crowd_model->getcrowd_row($id);
				$data["rewards"] = $this->crowd_model->getrewards($id);
			}
			$this->load->view("framework.html", $data);
		}
	}
	public function review()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$data = array("title" => "众筹审核|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_review", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css"), array("value" => "3")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js"), array("value" => "js/pages/crowd.js")), "ntype" => $this->input->get("type"));
		$this->load->view("framework.html", $data);
	}
	public function subcrowd()
	{
		if (!checkrole("crowd", "start")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$id = $this->input->post("id");
		$ntype = $this->input->post("ntype", true);
		$crowd_id = $this->crowd_model->add_crowd();
		if ($crowd_id > 0) {
			if ($ntype == 0) {
				if ($this->crowd_model->add_crowd_rewards($crowd_id)) {
				}
				$arr = array("id" => 1, "msg" => "成功", "data" => $crowd_id, "link" => weburl("crowd", "start", array("type" => $ntype, "ff" => 1, "id" => $crowd_id)));
				die(json_encode($arr));
			} else {
				$arr = array("id" => 1, "msg" => "成功", "data" => $crowd_id, "link" => weburl("crowd", "start", array("type" => $ntype, "ff" => 1, "id" => $crowd_id)));
				die(json_encode($arr));
			}
		}
		$arr = array("id" => 1, "msg" => "成功");
		die(json_encode($arr));
	}
	public function category()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$data = array("title" => "众筹分类管理|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_category", "styles" => array(array("value" => "plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css")), "scripts" => array(array("value" => "plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js"), array("value" => "plugins/uniform/jquery.uniform.min.js"), array("value" => "js/pages/crowd_category.js")), "category" => $this->crowd_model->getcategory());
		$this->load->view("framework.html", $data);
	}
	public function delcategory()
	{
		if (!checkrole("crowd", "category")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$id = $this->input->get("id");
		if ($id > 0) {
			if ($this->crowd_model->delcategory($id)) {
				die(json_encode(array("id" => 1, "msg" => "删除成功")));
			}
		}
	}
	public function editcategory()
	{
		if (!checkrole("crowd", "category")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$id = $this->input->post("pk");
		if ($id > 0) {
			$this->crowd_model->editcategory();
		} else {
			$this->crowd_model->addcategory();
		}
	}
	public function reportlist()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		global $_W;
		$id = $this->input->get("id");
		if ($id > 0) {
			$crowd = $this->crowd_model->getcrowd_row($id);
			if (!empty($crowd)) {
				if ($crowd["ntype"] == "1") {
					$union_id = $this->input->get("union_id");
					$team_id = $this->input->get("team_id");
					if ($union_id != '') {
						$crowd_union = $this->crowd_model->getcrowd_union_row($union_id);
					}
					if ($team_id != '') {
						$crowd_team = pdo_get("yhzc_crowd_team", array("id" => $team_id, "uniacid" => $_W["uniacid"]));
					}
				}
				$data = array("title" => "支持列表|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_reportlist", "styles" => array(), "scripts" => array(array("value" => "js/pages/crowd_report_list.js")), "ntype" => !$this->input->get("type") ? "0" : $this->input->get("type"), "category" => $this->crowd_model->getcategory());
				$data["crowd"] = $crowd;
				if ($crowd["ntype"] == "1") {
					if (isset($crowd_union)) {
						$data["crowd_union"] = $crowd_union;
					}
					if (isset($crowd_team)) {
						$data["crowd_team"] = $crowd_team;
					}
				}
				$this->load->view("framework.html", $data);
			}
		}
	}
	public function rewardlist()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$id = $this->input->get("id");
		if ($id > 0) {
			$crowd = $this->crowd_model->getcrowd_row($id);
			if (!empty($crowd)) {
				if ($crowd["ntype"] != "0") {
					exit;
				}
				$rewards = $this->crowd_model->getrewards($crowd["id"]);
				$data = array("title" => "回报处理|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_rewardlist", "styles" => array(), "scripts" => array(array("value" => "plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"), array("value" => "js/pages/crowd_reward_list.js")), "ntype" => !$this->input->get("type") ? "0" : $this->input->get("type"), "category" => $this->crowd_model->getcategory());
				$data["crowd"] = $crowd;
				$data["rewards"] = $rewards;
				$this->load->view("framework.html", $data);
			}
		}
	}
	public function bmlist()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		global $_W;
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		$keys = $this->input->get("keys");
		$crowd_id = $this->input->get("crowd_id");
		$team_id = $this->input->get("team_id");
		$f_uid = $this->input->get("f_uid");
		$crowdrow = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowdrow)) {
			exit;
		}
		if ($crowdrow["ntype"] != "1") {
			exit;
		}
		$team = pdo_fetchall("select t.*,sum(r.money) as rmoney,count(u.id) as ucount,count(r.id) as rcount from " . tablename("yhzc_crowd_team") . " as t left join " . tablename("yhzc_crowd_union") . " as u on u.crowd_id=t.crowd_id and u.team_id=t.id left join " . tablename("yhzc_crowd_report") . " as r on r.union_id=u.id and r.status=1 where t.crowd_id={$crowdrow["id"]} and t.uniacid={$_W["uniacid"]} group by t.id order by t.id desc ");
		$data = array("title" => "联合众筹列表", "description" => "众筹列表", "keywords" => "众筹列表", "w" => $this->w, "team_id" => $team_id, "pagename" => "crowd_bmlist", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js"), array("value" => "plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"), array("value" => "js/pages/crowd_union.js")), "crowd_union" => $this->crowd_model->getcrowd_union_all($page, $limit, $keys), "crowd" => $crowdrow, "team" => $team, "fst" => $this->input->get("fst"), "fed" => $this->input->get("fed"), "fg" => $this->input->get("fg"), "pagination" => [$this->crowd_model->getcrowd_union_count($keys)["count"], $page ? $page : 1, $limit ? $limit : 10]);
		$this->load->view("framework.html", $data);
	}
	public function downunion()
	{
		global $_W;
		if (!checkrole("crowd", "bmlist")) {
			echo "无权限";
			exit;
		}
		$fst = $this->input->get("fst");
		$fed = $this->input->get("fed");
		$team_id = $this->input->get("team_id");
		$dpage = $this->input->get("dpage");
		$limit = 100;
		$key = $this->input->get("key");
		$crowd_id = $this->input->get("crowd_id");
		$crowdrow = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowdrow)) {
			exit;
		}
		if ($crowdrow["ntype"] != "1") {
			exit;
		}
		$union = $this->crowd_model->getcrowd_union_all($dpage, $limit, $key);
		if (empty($union)) {
			echo "没有数据";
			exit;
		}
		if (count($union) > 500) {
			$arr["id"] = -1;
			$arr["msg"] = "每次最大支持下载500行";
			die(json_encode($arr));
		}
		$csv_header = ["用户ID", "昵称", "姓名", "手机号码", "支持人数", "已筹金额", "报名时间"];
		$csv_body = [];
		foreach ($union as $od) {
			$csv_body[] = [$od["user_id"], $od["nickname"], $od["realname"], $od["phone"], $od["ppnum"], $od["cjmoney"], date("Y-m-d H:i", $od["addtime"])];
		}
		putcsv($csv_header, $csv_body);
	}
	public function downreward()
	{
		global $_W;
		if (!checkrole("crowd", "rewardlist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$crowd_id = $this->input->get("crowd_id");
		$crowdrow = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowdrow)) {
			exit;
		}
		$crowd_id = $this->input->get("crowd_id");
		$reward_id = $this->input->get("reward_id");
		$reward_status = $this->input->get("reward_status");
		$dpage = $this->input->get("dpage");
		$fst = $this->input->get("fst");
		$fed = $this->input->get("fed");
		$limit = 100;
		$where = "cr.crowd_id=:crowd_id and cr.status=1 and cr.reward_id>0";
		$wheres[":crowd_id"] = $crowd_id;
		if ($reward_id != "all" && $reward_id != 0) {
			$where .= " and cr.reward_id=:reward_id";
			$wheres[":reward_id"] = $reward_id;
		}
		if ($reward_status == "0" || $reward_status == "1") {
			$where .= " and cr.reward_status=:reward_status";
			$wheres[":reward_status"] = $reward_status;
		}
		if ($fst != '') {
			$where .= " and cr.addtime>=:fst";
			$wheres[":fst"] = strtotime($fst);
		}
		if ($fed != '') {
			$where .= " and u.addtime<=:fed";
			$wheres[":fed"] = strtotime($fed);
		}
		$reportarr = $this->crowd_model->getreportreward_all($where, $wheres, $dpage == '' ? 1 : $dpage, $limit == '' ? 100 : $limit);
		if (empty($reportarr)) {
			echo "没有数据";
			exit;
		}
		if (count($reportarr) > 500) {
			$arr["id"] = -1;
			$arr["msg"] = "每次最大支持下载500行";
			die(json_encode($arr));
		}
		$csv_header = ["用户ID", "昵称", "姓名", "支持金额", "数量", "用户地址", "额外信息", "回报类型", "处理时间", "留言"];
		$csv_body = [];
		foreach ($reportarr as $od) {
			$uaddress = $od["reward_type"] == "0" ? $od["uaprovince"] . $od["uacity"] . $od["uaother"] . "\n" . $od["uaname"] . "\n" . $od["uazipcode"] . "\n" . $od["uaphone"] : "无";
			$zdname = unserialize($od["reward_fd"]);
			$zdvalue = unserialize($od["reward_other"]);
			$zdgrptxt = '';
			if (!empty($zdname)) {
				foreach ($zdname as $k => $zd) {
					$zdgrptxt .= $zd . ":" . (@$zdvalue[$k] == '' ? "空" : $zdvalue[$k]) . "\n";
				}
			}
			$csv_body[] = [$od["user_id"], $od["nickname"], $od["realname"], $od["money"], $od["reward_num"], "\"" . $uaddress . "\"", "\"" . $zdgrptxt . "\"", $od["reward_type"] == "1" ? "虚拟" : "实物", $od["reward_time"] == 0 ? "无" : $od["ureward_time"], $od["message"]];
		}
		putcsv($csv_header, $csv_body);
	}
	public function unionchange()
	{
		if (!checkrole("crowd", "bmlist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$status = $this->input->post("status");
		$union_id = $this->input->post("union_id");
		$var = $this->crowd_model->update_union(array("status" => $status, "starttime" => time()), array("id" => $union_id));
		if ($var) {
			die(json_encode(array("id" => 1, "msg" => "操作成功")));
		} else {
			die(json_encode(array("id" => 1, "msg" => "操作失败")));
		}
	}
	public function download()
	{
		global $_W;
		if (!checkrole("crowd", "reportlist")) {
			echo "无权限";
			exit;
		}
		$type = $this->input->post("tp");
		$changeid = $this->input->post("changeid");
		$changeid = explode("|", $changeid);
		if (count($changeid) > 100) {
			$arr["id"] = -1;
			$arr["msg"] = "每次最大支持下载100行";
			die(json_encode($arr));
		}
		switch ($type) {
			case "0":
				$where = '';
				foreach ($changeid as $ks => $cgid) {
					if ($ks == 0) {
						$where .= "(id={$cgid}";
					} else {
						$where .= " or id={$cgid}";
					}
				}
				$where .= ")";
				$odarr = pdo_fetchall("SELECT crowd_id,wx_orderid, money FROM " . tablename("yhzc_crowd_report") . " where uniacid={$_W["uniacid"]} and {$where}");
				$csv_header = ["商户订单号", "退款金额", "退款原因"];
				$csv_body = [];
				foreach ($odarr as $od) {
					$csv_body[] = ["\"" . $od["wx_orderid"] . "\t" . "\"", "\"" . $od["money"] . "\"", "众筹退款"];
				}
			case "1":
			default:
		}
		putcsv($csv_header, $csv_body);
	}
	public function orderjson()
	{
		if (!checkrole("crowd", "reportlist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$crowd_id = $this->input->get("crowd_id");
		$status = $this->input->get("status");
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		$key = $this->input->get("key");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		$where = '';
		$wheres = array();
		if ($crowd["ntype"] == 0) {
			$where .= " r.crowd_id=:crowd_id";
			$wheres[":crowd_id"] = $crowd_id;
		} else {
			if ($crowd["ntype"] == "1") {
				$union_id = $this->input->get("union_id");
				$team_id = $this->input->get("team_id");
				$where .= "r.crowd_id=:crowd_id";
				$wheres[":crowd_id"] = $crowd_id;
				if ($union_id > 0) {
					$where .= " and r.union_id=:union_id";
					$wheres[":union_id"] = $union_id;
				}
				if ($team_id > 0) {
					$where .= " and u.team_id=:team_id";
					$wheres[":team_id"] = $team_id;
				}
			}
		}
		if ($status == "0" || $status == "1" || $status == "-1" || $status == "-2") {
			$where .= " and r.status=:status";
			$wheres[":status"] = $status;
		}
		if ($key != '') {
			$where .= " and (r.user_id like :key or r.nickname like :key or r.wx_orderid like :key)";
			$wheres[":key"] = "%{$key}%";
		}
		$reportarr = $this->crowd_model->getreport_all($where, $wheres, $page == '' ? 1 : $page, $limit == '' ? 20 : $limit);
		$count = $this->crowd_model->getreport_count($where, $wheres)["count"];
		if (empty($reportarr)) {
			$arr["id"] = -1;
			$arr["msg"] = "还没有人支持此众筹项目！";
		}
		$arr["id"] = 1;
		$arr["msg"] = "success";
		$arr["data"] = $reportarr;
		$arr["page"] = [ceil($count / $limit), $count];
		die(json_encode($arr));
	}
	public function orderrewardjson()
	{
		if (!checkrole("crowd", "rewardlist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$crowd_id = $this->input->get("crowd_id");
		$reward_id = $this->input->get("reward_id");
		$reward_status = $this->input->get("reward_status");
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		$key = $this->input->get("key");
		$crowd = $crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["ntype"] != "0") {
			exit;
		}
		$where = "cr.crowd_id=:crowd_id and cr.status=1 and cr.reward_id>0";
		$wheres[":crowd_id"] = $crowd_id;
		if ($reward_id != "all" && $reward_id != 0) {
			$where .= " and cr.reward_id=:reward_id";
			$wheres[":reward_id"] = $reward_id;
		}
		if ($reward_status == "0" || $reward_status == "1") {
			$where .= " and cr.reward_status=:reward_status";
			$wheres[":reward_status"] = $reward_status;
		}
		if ($key != '') {
			$where .= " and (cr.user_id like :key or cr.nickname like :key or cr.wx_orderid like :key)";
			$wheres[":key"] = "%{$key}%";
		}
		$reportarr = $this->crowd_model->getreportreward_all($where, $wheres, $page == '' ? 1 : $page, $limit == '' ? 20 : $limit);
		$count = $this->crowd_model->getreportreward_count($where, $wheres)["count"];
		if (empty($reportarr)) {
			$arr["id"] = -1;
			$arr["msg"] = "没有找到！";
		}
		$arr["id"] = 1;
		$arr["msg"] = "success";
		$rwdarr = array();
		if (!empty($reportarr)) {
			foreach ($reportarr as $k => $r) {
				if ($r["reward_fd"] != '') {
					$r["reward_other"] = unserialize($r["reward_other"]);
					$r["reward_fd"] = unserialize($r["reward_fd"]);
				}
				$rwdarr[$k] = $r;
			}
		}
		$arr["data"] = $rwdarr;
		$arr["page"] = [ceil($count / $limit), $count];
		die(json_encode($arr));
	}
	public function order_reply()
	{
		global $_W;
		if (!checkrole("crowd", "reportlist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$crowd_id = $this->input->get("crowd_id");
		$union_id = $this->input->get("union_id");
		if ($union_id != '') {
			$union = $this->crowd_model->getcrowd_union_row($union_id);
			if (empty($union)) {
				exit;
			}
		} else {
			if ($crowd_id != '') {
				$crowd = $this->crowd_model->getcrowd_row($crowd_id);
				if (empty($crowd)) {
					exit;
				}
			} else {
				exit;
			}
		}
		$orderstr = $this->input->post("order_id");
		if ($orderstr != '') {
			$orderarr = explode("|", $orderstr);
			if (count($orderarr) > 100) {
				$arr["id"] = -1;
				$arr["msg"] = "仅支持每次退款100人";
			} else {
				$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg", "uniacid" => $_W["uniacid"]));
				if (!empty($wxmsg)) {
					$tpl = unserialize($wxmsg["value"]);
				}
				$errarr = array();
				$okarr = array();
				$owhere = '';
				$onrefund = mysys("refund");
				foreach ($orderarr as $ks => $od) {
					if ($ks == 0) {
						$owhere .= "(id={$od}";
					} else {
						$owhere .= " or id={$od}";
					}
				}
				$owhere .= ")";
				$rptall = pdo_fetchall("SELECT * FROM " . tablename("yhzc_crowd_report") . " WHERE {$owhere} and uniacid={$_W["uniacid"]}");
				foreach ($rptall as $om) {
					if ($om["status"] == "1") {
						if ($om["union_id"] != 0 && $om["union_id"] != @$union["id"]) {
							exit;
						} else {
							if ($union_id == '' && $om["crowd_id"] != @$crowd["id"]) {
								exit;
							}
						}
						$refund = wechatrefund($om);
						if ($refund) {
							if ($union_id != '') {
								$cwhere = " and union_id={$union["id"]}";
							} else {
								$cwhere = " and crowd_id={$crowd["id"]}";
							}
							$cj = pdo_fetch("SELECT sum(money) as money FROM " . tablename("yhzc_crowd_report") . " WHERE status = 1 and uniacid=:uniacid {$cwhere} LIMIT 1", array(":uniacid" => $_W["uniacid"]));
							if ($union_id != '') {
								$cgmy = pdo_update("yhzc_crowd_union", array("cjmoney" => $cj["money"]), array("id" => $union["id"], "uniacid" => $_W["uniacid"]));
								$totalmy = pdo_fetch("SELECT sum(cjmoney) as money FROM " . tablename("yhzc_crowd_union") . " WHERE crowd_id = {$union["crowd_id"]} and uniacid=:uniacid LIMIT 1", array(":uniacid" => $_W["uniacid"]));
								$ttcgmy = pdo_update("yhzc_crowd", array("cjmoney" => $totalmy["money"]), array("id" => $union["crowd_id"], "uniacid" => $_W["uniacid"]));
								if (@$tpl["refund"] != '') {
									$acc = mc_notice_init();
									$tpldata = array("first" => array("value" => "您所支持的好友【" . $union["realname"] . "】参与的众筹项目已退款", "color" => "#173177"), "reason" => array("value" => "筹款失败或其他"), "refund" => array("value" => $om["money"] . "元"), "remark" => array("value" => "支持金额将原路退回，请注意查收"));
									$acc->sendTplNotice($om["openid"], $tpl["refund"], $tpldata, '', $topcolor = "#FF683F");
								}
							} else {
								$cgmy = pdo_update("yhzc_crowd", array("cjmoney" => $cj["money"]), array("id" => $crowd["id"], "uniacid" => $_W["uniacid"]));
								if (@$tpl["refund"] != '') {
									$acc = mc_notice_init();
									$tpldata = array("first" => array("value" => "您所支持的众筹项目【" . $crowd["title"] . "】已退款", "color" => "#173177"), "keyword1" => array("value" => "众筹失败或其他"), "keyword2" => array("value" => $om["money"] . "元"), "remark" => array("value" => "支持金额将原路退回，请注意查收"));
									$acc->sendTplNotice($om["openid"], $tpl["refund"], $tpldata, '', $topcolor = "#FF683F");
								}
							}
							if (empty($cgmy)) {
							}
							$okarr[] = $om["id"];
						} else {
							$errarr[] = $om["id"];
						}
					}
				}
				if (empty($errarr)) {
					$arr["id"] = 1;
					if ($onrefund == 1) {
						$arr["msg"] = "提交退款成功";
					} else {
						$arr["msg"] = "操作成功，余额支付用户已退回余额，微信支付用户请进入微信支付平台进行退款操作";
					}
				} else {
					$arr["id"] = -1;
					$arr["msg"] = "退款出现异常";
					$arr["data"] = $errarr;
				}
			}
		} else {
			$arr["id"] = -1;
			$arr["msg"] = "没有选择任何订单";
		}
		die(json_encode($arr));
	}
	public function order_reward()
	{
		global $_W;
		if (!checkrole("crowd", "rewardlist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$orderstr = $this->input->post("order_id");
		$test = '';
		if ($orderstr != '') {
			$orderarr = explode("|", $orderstr);
			if (count($orderarr) > 20) {
				$arr["id"] = -1;
				$arr["msg"] = "仅支持每次处理20人";
			} else {
				$sucnum = 0;
				$failnum = 0;
				$tplwhere = array();
				foreach ($orderarr as $o) {
					if ($o <= 0) {
						continue;
					}
					$rw_data = array("reward_status" => 1);
					$result = pdo_update("yhzc_crowd_report", $rw_data, array("id" => $o, "uniacid" => $_W["uniacid"], "reward_status <>" => 1));
					if (!empty($result)) {
						$tplwhere[] = $o;
						$sucnum++;
					} else {
						$failnum++;
					}
				}
				$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg"));
				if (!empty($wxmsg)) {
					$tpl = unserialize($wxmsg["value"]);
					if (isset($tpl["reward"])) {
						$tplw = "(";
						foreach ($tplwhere as $k => $nid) {
							if ($k == 0) {
								$tplw .= "cr.id={$nid}";
							} else {
								$tplw .= " or cr.id={$nid}";
							}
						}
						$tplw .= ")";
						$tplarr = pdo_fetchall("SELECT cr.user_id as uid,cr.nickname as nickname,cr.openid as openid,crd.reward_title as reward_title,c.title as title FROM " . tablename("yhzc_crowd_report") . " as cr LEFT JOIN " . tablename("yhzc_crowd_reward") . " as crd ON cr.reward_id=crd.id LEFT JOIN " . tablename("yhzc_crowd") . " as c ON c.id=cr.crowd_id where cr.uniacid={$_W["uniacid"]} and {$tplw}");
						if (!empty($tplarr)) {
							foreach ($tplarr as $t) {
								$tpldata = array("first" => array("value" => $t["nickname"] . "您好，您支持的项目回报已发出", "color" => "#173177"), "keyword1" => array("value" => $t["title"]), "keyword2" => array("value" => $t["reward_title"]), "keyword3" => array("value" => "已处理"), "remark" => array("value" => ''));
								$acc = @mc_notice_init();
								$tv = $acc->sendTplNotice($t["openid"], $tpl["reward"], $tpldata, $url = appurl("user", "order"), $topcolor = "#FF683F");
								putmsg($t["uid"], "您支持的项目回报已发出", "<p>您支持的项目回报已发出，请注意查收</p><p>众筹标题：" . $t["title"] . "</p>");
							}
						}
					}
				}
				$arr["id"] = 1;
				$arr["msg"] = "执行成功，处理成功" . $sucnum . "条，失败" . $failnum . "条";
			}
		} else {
			$arr["id"] = -1;
			$arr["msg"] = "没有选择任何订单";
		}
		die(json_encode($arr));
	}
	public function march()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$crowd_id = $this->input->get("crowd_id");
		$crowd = $crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		$data = array("title" => "众筹进度", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_march", "styles" => array(array("value" => "plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css")), "scripts" => array(array("value" => "plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js"), array("value" => "plugins/uniform/jquery.uniform.min.js"), array("value" => "js/pages/crowd_march.js")), "crowd" => $crowd, "march" => $this->crowd_model->getmarch($crowd_id));
		$this->load->view("framework.html", $data);
	}
	public function delmarch()
	{
		if (!checkrole("crowd", "march")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$id = $this->input->get("id");
		if ($id > 0) {
			if ($this->crowd_model->delmarch($id)) {
				die(json_encode(array("id" => 1, "msg" => "删除成功")));
			}
		}
	}
	public function editmarch()
	{
		if (!checkrole("crowd", "march")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$id = $this->input->post("pk");
		if ($id > 0) {
			$this->crowd_model->editmarch();
		} else {
			$this->crowd_model->addmarch();
		}
	}
	public function citydata()
	{
		$citydata = get_area();
		header("Content-type: text/javascript; charset=utf-8");
		if (!empty($citydata)) {
			echo "var cityData3 = " . json_encode($citydata);
		} else {
			echo "var cityData3 =[]";
		}
	}
	private function refund($refund_id)
	{
		global $_W;
		load()->classs("pay");
		$refundlog = pdo_get("core_refundlog", array("id" => $refund_id));
		$paylog = pdo_get("core_paylog", array("uniacid" => $_W["uniacid"], "uniontid" => $refundlog["uniontid"]));
		$refund_param = reufnd_wechat_build($refund_id);
		$wechat = Pay::create("wechat");
		$response = $wechat->refund($refund_param);
		unlink(ATTACHMENT_ROOT . $_W["uniacid"] . "_wechat_refund_all.pem");
		if (is_error($response)) {
			pdo_update("core_refundlog", array("status" => "-1"), array("id" => $refund_id));
		}
		return $response;
	}
	public function check()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		$key = $this->input->get("key");
		$where = '';
		$wheres = array();
		if ($key != '') {
			$where .= "(u.name like :key or u.uid=:keys or u.phone=:keys or c.crowd_info like :keyt)";
			$wheres[":key"] = "%{$key}%";
			$wheres[":keys"] = $key;
			$cdkey = str_replace("\"", '', json_encode($key));
			$cdkey = str_replace("\\", "_", $cdkey);
			$wheres[":keyt"] = "%{$cdkey}%";
		}
		$data = array("title" => "众筹审核", "description" => "众筹审核", "keywords" => "众筹审核", "key" => $this->input->get("key"), "w" => $this->w, "pagename" => "crowd_cache", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js")), "crowd" => $this->crowd_model->get_crowd_cache_all($page, $limit, $where, $wheres), "pagination" => [$this->crowd_model->get_crowd_cache_count($where, $wheres)["count"], $page ? $page : 1, $limit ? $limit : 10]);
		$this->load->view("framework.html", $data);
	}
	public function docheck()
	{
		if (!checkrole("crowd", "check")) {
			echo "无权限";
			exit;
		}
		$id = $this->input->get("id");
		if ($id == '') {
			exit;
		}
		$crowd = $this->crowd_model->get_crowd_cache_row($id);
		if (empty($crowd)) {
			exit;
		}
		$crowd["default_info"] = @json_decode($crowd["default_info"], true);
		$crowd["crowd_info"] = @json_decode($crowd["crowd_info"], true);
		$crowd["crowd_reward"] = @json_decode($crowd["crowd_reward"], true);
		$ctarr = $this->crowd_model->getcategory();
		foreach ($ctarr as $ct) {
			if ($ct["id"] == $crowd["default_info"]["cid"]) {
				$crowd["category"] = $ct;
				break;
			}
		}
		$crowd["city"] = get_area($crowd["default_info"]["city"]);
		$data = array("title" => "众筹审核", "description" => "众筹审核", "keywords" => "众筹审核", "w" => $this->w, "pagename" => "crowd_cache_check", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js")), "crowd" => $crowd);
		$this->load->view("framework.html", $data);
	}
	public function subcheck()
	{
		global $_W;
		$vdo = $this->input->post("vdo");
		$msg = $this->input->post("msg");
		$id = $this->input->post("id");
		$crowd = $this->crowd_model->get_crowd_cache_row($id);
		if (empty($crowd)) {
			die(json_encode(array("id" => -1, "msg" => "该项目不存在")));
		}
		if ($crowd["status"] != 1) {
			die(json_encode(array("id" => -1, "msg" => "该项目当前非审核状态")));
		}
		$crowd["default_info"] = @json_decode($crowd["default_info"], true);
		$crowd["crowd_info"] = @json_decode($crowd["crowd_info"], true);
		$crowd["crowd_reward"] = @json_decode($crowd["crowd_reward"], true);
		$wxmsg = pdo_get("yhzc_system", array("key" => "wxmsg", "uniacid" => $_W["uniacid"]));
		$user = pdo_get("yhzc_user", array("uid" => $crowd["u_id"], "uniacid" => $_W["uniacid"]));
		if (!empty($wxmsg) && !empty($user)) {
			$tpl = unserialize($wxmsg["value"]);
		}
		switch ($vdo) {
			case "ok":
				$result = pdo_update("yhzc_crowd_cache", array("status" => 2), array("id" => $id, "uniacid" => $_W["uniacid"]));
				if (!empty($result)) {
					putmsg($crowd["u_id"], "众筹项目审核通过", "<p>您提交的众筹项目已审核通过！</p><p>众筹标题：" . $crowd["crowd_info"]["title"] . "</p>");
					if (isset($tpl["verifysuccess"])) {
						$tpldata = array("first" => array("value" => "您发布的众筹项目已通过审核", "color" => "#173177"), "keyword1" => array("value" => $crowd["crowd_info"]["title"]), "keyword2" => array("value" => "预计" . date("Y-m-d", $crowd["crowd_info"]["starttime"])), "keyword3" => array("value" => $crowd["crowd_info"]["money"] . "元"), "remark" => array("value" => "点击查看详情"));
						$acc = mc_notice_init();
						$acc->sendTplNotice($user["openid"], $tpl["verifysuccess"], $tpldata, $url = $_W["siteroot"] . "app/index.php?i={$_W["uniacid"]}&c=entry&do=r&m=yh_zhongchou&mc=mycrowd&mm=index", $topcolor = "#FF683F");
					}
					$content = $crowd["crowd_content"];
					$tags = @json_decode($crowd["crowd_info"]["tags"], true);
					$crowd["crowd_info"]["tags"] = '';
					foreach ($tags as $k => $t) {
						if ($k == 0) {
							$crowd["crowd_info"]["tags"] .= $t;
						} else {
							$crowd["crowd_info"]["tags"] .= "," . $t;
						}
					}
					$cdata = array_merge($crowd["default_info"], $crowd["crowd_info"], array("content" => $content));
					$cdata["uniacid"] = $_W["uniacid"];
					$cdata["addtime"] = time();
					$cdata["status"] = 0;
					$cdata["u_id"] = $crowd["u_id"];
					$cdata["gear"] = "1,3,5,10,20,100";
					$addcrowdwar = pdo_insert("yhzc_crowd", $cdata);
					if (empty($addcrowdwar)) {
						die(json_encode(array("id" => -1, "msg" => "系统错误")));
					}
					$crowd_id = pdo_insertid();
					pdo_update("yhzc_crowd_cache", array("crowd_id" => $crowd_id), array("id" => $id, "uniacid" => $_W["uniacid"]));
					if ($crowd["default_info"]["ntype"] == "0" && !empty($crowd["crowd_reward"])) {
						$rewarddata = '';
						foreach ($crowd["crowd_reward"] as $k => $reward) {
							if ($k > 0) {
								$rewarddata .= ",";
							}
							$rewarddata .= "({$_W["uniacid"]}," . $crowd_id . "," . $reward["reward_type"] . "," . $reward["reward_money"] . ",'" . $reward["reward_title"] . "','" . $reward["reward_content"] . "'," . $reward["reward_limit"] . "," . $reward["reward_day"] . ",'" . $reward["reward_fd"] . "'," . $reward["reward_marknum"] . ")";
						}
						$reward_result = pdo_query("INSERT INTO " . tablename("yhzc_crowd_reward") . " (uniacid,crowd_id,reward_type,reward_money,reward_title,reward_content,reward_limit,reward_day,reward_fd,reward_marknum) VALUES {$rewarddata}");
						if (empty($reward_result)) {
							die(json_encode(array("id" => -1, "msg" => "回报项插入失败，请注意手动加入！")));
						}
					}
					die(json_encode(array("id" => 1, "msg" => "审核通过，目前并未发布，请2秒后进入编辑页进行编辑和上线", "data" => array("id" => $crowd_id, "ntype" => $crowd["default_info"]["ntype"]))));
				}
				break;
			case "reply":
				$result = pdo_update("yhzc_crowd_cache", array("status" => 0), array("id" => $id, "uniacid" => $_W["uniacid"]));
				if (!empty($result)) {
					putmsg($crowd["u_id"], "众筹项目审核未通过，请修改后提交", $msg . "<p>众筹标题：" . $crowd["crowd_info"]["title"] . "</p>");
					if (isset($tpl["verifyfailed"])) {
						$tpldata = array("first" => array("value" => "您发布的众筹项目未通过审核", "color" => "#173177"), "keyword1" => array("value" => $crowd["crowd_info"]["title"]), "keyword2" => array("value" => $crowd["crowd_info"]["money"] . "元"), "keyword3" => array("value" => $msg), "remark" => array("value" => "点击此处，修改后重新提交"));
						$acc = mc_notice_init();
						$acc->sendTplNotice($user["openid"], $tpl["verifyfailed"], $tpldata, $url = $_W["siteroot"] . "app/index.php?i={$_W["uniacid"]}&c=entry&do=r&m=yh_zhongchou&mc=mycrowd&mm=start&id={$id}", $topcolor = "#FF683F");
					}
					die(json_encode(array("id" => 1, "msg" => "退回成功")));
				}
				break;
			case "pass":
				$result = pdo_update("yhzc_crowd_cache", array("status" => -1), array("id" => $id, "uniacid" => $_W["uniacid"]));
				if (!empty($result)) {
					putmsg($crowd["u_id"], "众筹项目审核未通过", $msg . "<p>众筹标题：" . $crowd["crowd_info"]["title"] . "</p>");
					if (isset($tpl["verifyfailed"])) {
						$tpldata = array("first" => array("value" => "您发布的众筹项目未通过审核", "color" => "#173177"), "keyword1" => array("value" => $crowd["crowd_info"]["title"]), "keyword2" => array("value" => $crowd["crowd_info"]["money"] . "元"), "keyword3" => array("value" => $msg), "remark" => array("value" => "您可以点击此处重新发起众筹"));
						$acc = mc_notice_init();
						$acc->sendTplNotice($user["openid"], $tpl["verifyfailed"], $tpldata, $url = $_W["siteroot"] . "app/index.php?i={$_W["uniacid"]}&c=entry&do=r&m=yh_zhongchou&mc=mycrowd&mm=index", $topcolor = "#FF683F");
					}
					die(json_encode(array("id" => 1, "msg" => "该项目已被成功退回，并不允许再次提交审核")));
				}
				break;
		}
		die(json_encode(array("id" => 1, "msg" => "ok")));
	}
	public function close()
	{
		global $_W;
		$crowd_id = $this->input->post("crowd_id", true);
		$endmoney = $this->input->post("endmoney", true);
		if ($crowd_id == '' || $endmoney == '' || $endmoney <= 0) {
			die(json_encode(array("id" => -1, "msg" => "数据非法")));
		}
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			die(json_encode(array("id" => -1, "msg" => "异常操作")));
		}
		if ($crowd["ntype"] == "1") {
			$union_id = $this->input->post("union_id", true);
			if ($union_id == '') {
				die(json_encode(array("id" => -1, "msg" => "数据非法")));
			} else {
				$union = $this->crowd_model->getcrowd_union_row($union_id);
				if (empty($union)) {
					die(json_encode(array("id" => -1, "msg" => "异常操作")));
				} else {
					if ($union["crowd_id"] != $crowd_id) {
						die(json_encode(array("id" => -1, "msg" => "异常操作")));
					}
				}
				$var = $this->crowd_model->update_union(array("status" => 2, "endmoney" => $endmoney), array("id" => $union_id));
				if (!empty($var)) {
					die(json_encode(array("id" => 1, "msg" => "结算联合众筹成功")));
				}
			}
		} else {
			$result = pdo_update("yhzc_crowd", array("status" => 2, "endmoney" => $endmoney), array("id" => $crowd_id, "uniacid" => $_W["uniacid"]));
			if (!empty($result)) {
				die(json_encode(array("id" => 1, "msg" => "结算众筹成功")));
			}
		}
		die(json_encode(array("id" => -1, "msg" => "结算失败")));
	}
	public function team()
	{
		if (!checkrole("crowd", "clist")) {
			echo "无权限";
		}
		global $_W;
		$crowd_id = $this->input->get("crowd_id");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["ntype"] != "1" || $crowd["team"] == '' || $crowd["team"] == 0) {
			exit;
		}
		$data = array("title" => "队伍管理", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_team", "styles" => array(array("value" => "plugins/x-editable/bootstrap3-editable/css/bootstrap-editable.css")), "scripts" => array(array("value" => "plugins/x-editable/bootstrap3-editable/js/bootstrap-editable.js"), array("value" => "plugins/uniform/jquery.uniform.min.js"), array("value" => "js/pages/crowd_team.js")), "crowd" => $crowd, "team" => pdo_fetchall("select t.*,sum(r.money) as rmoney,count(distinct u.id) as ucount,count(r.id) as rcount from " . tablename("yhzc_crowd_team") . " as t left join " . tablename("yhzc_crowd_union") . " as u on u.crowd_id=t.crowd_id and u.team_id=t.id left join " . tablename("yhzc_crowd_report") . " as r on r.union_id=u.id and r.status=1 where t.crowd_id={$crowd["id"]} and t.uniacid={$_W["uniacid"]} group by t.id order by t.id desc "));
		$this->load->view("framework.html", $data);
	}
	public function delteam()
	{
		if (!checkrole("crowd", "clist")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		global $_W;
		$id = $this->input->get("id");
		if ($id > 0) {
			$dvar = pdo_delete("yhzc_crowd_team", array("id" => $id, "uniacid" => $_W["uniacid"]));
			if (!empty($dvar)) {
				pdo_update("yhzc_crowd_union", array("team_id" => 0), array("team_id" => $id, "uniacid" => $_W["uniacid"]));
				die(json_encode(array("id" => 1, "msg" => "删除成功")));
			}
		}
	}
	public function editteam()
	{
		if (!checkrole("crowd", "march")) {
			die(json_encode(array("id" => -1, "msg" => "无权限")));
		}
		$id = $this->input->post("pk");
		if ($id > 0) {
			$this->crowd_model->editteam();
		} else {
			$this->crowd_model->addteam();
		}
	}
	public function stat()
	{
		$crowd_id = $this->input->get("crowd_id", true);
		$data = array("title" => "众筹统计|亿恒众筹", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "w" => $this->w, "pagename" => "crowd_stat", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js"), array("value" => "plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"), array("value" => "js/pages/crowd_union.js")));
		if ($crowd_id > 0) {
			$data["crowd"] = $this->crowd_model->getcrowd_row($crowd_id);
			if (empty($data["crowd"])) {
				exit;
			}
			$data["rewards"] = $this->crowd_model->getrewards($crowd_id);
		} else {
			exit;
		}
		$this->load->view("framework.html", $data);
	}
	public function statjson()
	{
		global $_W;
		$crowd_id = $this->input->get("crowd_id", true);
		$starttime = $this->input->get("starttime", true);
		$endtime = $this->input->get("endtime", true);
		$ff = $this->input->get("ff", true);
		if ($starttime != '') {
			$starttime = strtotime($starttime);
		}
		if ($endtime != '') {
			$endtime = strtotime($endtime);
		}
		$where = " r.status=1 and r.crowd_id=:crowd_id and r.uniacid=:uniacid";
		$wheres = array(":crowd_id" => $crowd_id, "uniacid" => $_W["uniacid"]);
		if ($starttime == '' && $endtime == '') {
			$time = strtotime(date("Y-m-d"));
			$mtime = $time - 7 * 24 * 3600;
			$where .= " and r.addtime>{$mtime}";
		} else {
			if ($starttime != '' && $endtime == '') {
				$where .= " and r.addtime>={$starttime} && r.addtime<" . ($starttime + 24 * 3600);
			} else {
				if ($starttime != '' && $endtime != '') {
					if ($endtime < $starttime) {
						die(json_encode(array("id" => -1, "msg" => "结束时间不能小于开始时间")));
					}
					if ($endtime - $starttime > 30 * 24 * 3600) {
						die(json_encode(array("id" => -1, "msg" => "查询时间间隔不能大于30天")));
					}
					$where .= " and r.addtime>={$starttime} && r.addtime<{$endtime}";
				}
			}
		}
		$stat7 = pdo_fetchall("SELECT count(r.id) ppnum,sum(r.money) as money,\n    DATE(FROM_UNIXTIME(r.addtime)) addtime FROM " . tablename("yhzc_crowd_report") . " r  where {$where} GROUP BY\n    DATE(FROM_UNIXTIME(r.addtime)) order by DATE(FROM_UNIXTIME(r.addtime)) DESC LIMIT 0,30", $wheres);
		if (!empty($stat7)) {
			if ($ff == "down") {
				$csv_header = ["时间", "支持总次数", "筹款总额"];
				$csv_body = [];
				foreach ($stat7 as $od) {
					$csv_body[] = [$od["addtime"], $od["ppnum"], $od["money"]];
				}
				putcsv($csv_header, $csv_body);
			} else {
				die(json_encode(array("id" => 1, "data" => $stat7)));
			}
		} else {
			if ($ff == "down") {
				echo "无数据";
			} else {
				die(json_encode(array("id" => -1, "msg" => "没有数据")));
			}
		}
	}
	public function refundtask()
	{
		global $_W;
		$time = time();
		$id = $this->input->get("id", true);
		$crowd = $this->crowd_model->getcrowd_row($id);
		if (empty($crowd)) {
			die(json_encode(array("id" => -1, "msg" => "项目不存在")));
		}
		if ($time <= $crowd["endtime"]) {
			die(json_encode(array("id" => -1, "msg" => "项目结束时间未到")));
		}
		$level = 2;
		$tskid = "crowd-refund-" . $crowd["id"];
		if ($crowd["ntype"] == "0") {
			$ntype = 2;
		} else {
			if ($crowd["ntype"] == "1") {
				$ntype = 3;
			}
		}
		$crowd_id = $crowd["id"];
		if ($tskid == '' || $ntype == '' || $crowd_id == '') {
			die(json_encode(array("id" => -1, "msg" => "缺少必要参数")));
		}
		$tskid = md5($tskid);
		$rpt = pdo_get("yhzc_task", array("tskid" => $tskid, "uniacid" => $_W["uniacid"]));
		if (!empty($rpt)) {
			if ($rpt["status"] == "-1") {
				die(json_encode(array("id" => -1, "msg" => "自动任务已提交过，并已完成")));
			} else {
				if ($rpt["status"] == "1") {
					die(json_encode(array("id" => -1, "msg" => "自动任务已提交过，正在执行中")));
				} else {
					die(json_encode(array("id" => -1, "msg" => "自动任务已提交过，并等待执行")));
				}
			}
		}
		$data = array("uniacid" => $_W["uniacid"], "tskid" => $tskid, "uid" => $_W["uid"], "status" => 0, "level" => $level, "ntype" => $ntype, "taskinfo" => serialize(array("crowd_id" => $crowd_id, "num" => 0, "ntype" => $ntype)));
		$result = pdo_insert("yhzc_task", $data);
		if (!empty($result)) {
			die(json_encode(array("id" => 1, "msg" => "自动任务提交成功")));
		}
	}
}