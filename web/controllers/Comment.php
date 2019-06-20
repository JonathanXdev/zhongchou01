<?php

//decode by http://www.yunlu99.com/
defined("BASEPATH") or exit("No direct script access allowed");
class Comment extends CI_Controller
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
	}
	public function index()
	{
		if (!checkrole()) {
			echo "无权限";
			exit;
		}
		global $_W;
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		if ($page == 0) {
			$page = 1;
		}
		if (!$limit) {
			$limit = 10;
		}
		$from_id = $this->input->get("from_id");
		$vfrom = $this->input->get("vfrom");
		$verify = $this->input->get("verify");
		$where = "where uniacid=:uniacid";
		$wheres[":uniacid"] = $_W["uniacid"];
		if ($from_id != '') {
			$where .= " and from_id=:from_id";
			$wheres[":from_id"] = $from_id;
		}
		if ($vfrom != '') {
			$where .= " and vfrom=:vfrom";
			$wheres[":vfrom"] = $vfrom;
		}
		if ($verify == "0" || $verify == "1") {
			$where .= " and verify=:verify";
			$wheres[":verify"] = $verify;
		}
		$cmtarr = pdo_fetchall("SELECT * FROM " . tablename("yhzc_comments") . " " . $where . " order by id desc LIMIT " . ($page - 1) * $limit . "," . $limit, $wheres);
		$count = pdo_fetch("SELECT count(id) as count FROM " . tablename("yhzc_comments") . " " . $where, $wheres);
		$data = array("title" => "评论/留言审核", "description" => '', "keywords" => '', "w" => $this->w, "pagename" => "cmt_list", "styles" => array(array("value" => "plugins/datatables/css/jquery.datatables.min.css")), "scripts" => array(array("value" => "plugins/datatables/js/jquery.datatables.min.js"), array("value" => "js/pages/article.js")), "comment" => $cmtarr, "pagination" => [$count["count"], $page ? $page : 1, $limit ? $limit : 10]);
		$this->load->view("framework.html", $data);
	}
	public function docmt()
	{
		global $_W;
		$vd = $this->input->get("vd", true);
		$id = $this->input->get("id", true);
		$where = array("uniacid" => $_W["uniacid"], "id" => $id);
		if ($vd == "del") {
			$result = pdo_delete("yhzc_comments", $where);
		} else {
			if ($vd == "v") {
				$result = pdo_update("yhzc_comments", array("verify" => 1), $where);
			}
		}
		if (!empty($result)) {
			die(json_encode(array("id" => 1, "msg" => "操作成功")));
		} else {
			die(json_encode(array("id" => -1, "msg" => "操作失败")));
		}
	}
}