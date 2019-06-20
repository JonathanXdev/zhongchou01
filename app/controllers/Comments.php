<?php

//decode by http://www.yunlu99.com/
defined("BASEPATH") or exit("No direct script access allowed");
class Comments extends CI_Controller
{
	public $w;
	public $user;
	public function __construct()
	{
		parent::__construct();
		global $_W;
		$this->w = $_W;
		$this->load->library("parser");
		$this->load->helper(array("url_helper", "file", "form"));
		checkauth();
		$this->user = getuser();
	}
	public function addcmt()
	{
		global $_W;
		$from_id = $this->input->post("from_id", true);
		$vfrom = $this->input->post("vfrom", true);
		$pid = $this->input->post("pid", true);
		$content = $this->input->post("content", true);
		if ($content == '') {
			die(json_encode(array("id" => -1, "msg" => "内容不能为空")));
		} else {
			if (mb_strlen($content) > 100) {
				die(json_encode(array("id" => -1, "msg" => "长度大于64个字符")));
			}
		}
		$time = time();
		$rptrow = pdo_get("yhzc_comments", array("from_id" => $from_id, "vfrom" => $vfrom, "uid" => $_W["member"]["uid"], "uniacid" => $_W["uniacid"], "content" => $content));
		if (!empty($rptrow)) {
			die(json_encode(array("id" => -1, "msg" => "请勿重复提交")));
		}
		$data = array("vfrom" => $vfrom, "from_id" => $from_id, "uid" => $_W["member"]["uid"], "uniacid" => $_W["uniacid"], "pid" => $pid == '' ? 0 : $pid, "avatar" => $this->user["avatar"], "nickname" => $this->user["name"] == '' ? @$_W["fans"]["tag"]["nickname"] : $this->user["name"], "content" => $content, "addtime" => $time, "likenum" => 0, "verify" => 0);
		$cmtcfg = unserialize(mysys("cmt"));
		if ($cmtcfg["verify"] == "1") {
			$data["verify"] = 0;
		} else {
			$data["verify"] = 1;
		}
		$nwords = explode(",", $cmtcfg["words"]);
		foreach ($nwords as $cv) {
			if (strpos($content, $cv) !== false) {
				$data["verify"] = -1;
				break;
			}
		}
		$result = pdo_insert("yhzc_comments", $data);
		if (!empty($result)) {
			$id = pdo_insertid();
			die(json_encode(array("id" => 1, "msg" => "ok", "data" => array("id" => $id, "avatar" => $this->user["avatar"], "nickname" => @$_W["fans"]["tag"]["nickname"], "addtime" => $time))));
		}
	}
	public function getcmt()
	{
		global $_W;
		$page = $this->input->get("page", true);
		$limit = $this->input->get("limit", true);
		$from_id = $this->input->get("from_id", true);
		$vfrom = $this->input->get("ff", true);
		$where = "where h.uniacid=:uniacid";
		$wheres[":uniacid"] = $_W["uniacid"];
		if ($page == 0) {
			$page = 1;
		}
		if (!$limit) {
			$limit = 10;
		}
		if ($from_id != '' || $from_id != 0) {
			$where .= " and h.from_id=:from_id and (h.verify=1 or (h.uid={$_W["member"]["uid"]}))";
			$wheres[":from_id"] = $from_id;
		}
		if ($vfrom != '') {
			$where .= " and h.vfrom=:vfrom";
			$wheres[":vfrom"] = $vfrom;
		}
		$cmt = pdo_fetchall("SELECT h.*,f.nickname as fnickname,f.avatar as favatar,f.uid as fuid,FROM_UNIXTIME(h.addtime,'%Y年%m月%d日') as ctime FROM " . tablename("yhzc_comments") . " as h left join " . tablename("yhzc_comments") . " as f on f.id=h.pid " . $where . " order by h.id desc LIMIT " . ($page - 1) * $limit . "," . $limit, $wheres);
		foreach ($cmt as $k => $v) {
			if ($v["avatar"] == '') {
				$cmt[$k]["avatar"] = MODULE_URL . "app/views/img/timg.jpg";
			} else {
				if (!strpos($v["avatar"], "http")) {
					$cmt[$k]["avatar"] = tomedia($v["avatar"]);
				}
			}
		}
		if (!empty($cmt)) {
			die(json_encode(array("id" => 1, "msg" => "OK", "data" => $cmt)));
		} else {
			die(json_encode(array("id" => -1, "msg" => "没有找到")));
		}
	}
}