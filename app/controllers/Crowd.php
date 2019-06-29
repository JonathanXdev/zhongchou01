<?php

//decode by http://www.yunlu99.com/
defined("BASEPATH") or exit("No direct script access allowed");
class Crowd extends CI_Controller
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
		$this->load->model("crowd_model");
		checkauth();
		$this->user = getuser();
	}
	public function index()
	{
		global $_W;
		$cid = $this->input->get("cid");
		$key = $this->input->get("key");
		$data = array("title" => "众筹列表", "description" => "完美众筹", "keywords" => "完美众筹", "cid" => $cid, "key" => $key, "pagename" => "home", "styles" => ["pages/home", "pages/crowd"], "scripts" => [], "category" => $this->crowd_model->getcategory(), "_share" => array("title" => "众筹分类", "imgUrl" => '', "desc" => '', "link" => ''));
		$this->load->view("crowd_list.html", $data);
	}
	public function getcrowd()
	{
		global $_W;
		$crowds = $this->crowd_model->getcrowd_all();
		if (!empty($crowds)) {
			$arr["id"] = 1;
			$arr["msg"] = "success";
			$arr["data"] = $crowds;
		} else {
			$arr["id"] = -1;
			$arr["msg"] = "没有找到";
		}
		die(json_encode($arr));
	}
	public function detail()
	{
		global $_W;
		$id = $this->input->get("id");
		$crowd = $this->crowd_model->getcrowd_row($id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["status"] != "1" && $crowd["u_id"] != $_W["member"]["uid"]) {
			echo "该众筹项目正在准备中,请稍后再来...";
			exit;
		}
		if ($crowd["coverimg"] != '') {
			$crowd["coverimg"] = json_decode($crowd["coverimg"], true);
		}
		pdo_update("yhzc_crowd", array("viewnum +=" => 1), array("id" => $id));
		$key = $this->input->get("key");
		$team_id = 0;
		if ($crowd["ntype"] == "1") {
			$team_id = $this->input->get("team_id", true);
			if ($team_id == '') {
				$myunion = pdo_get("yhzc_crowd_union", array("crowd_id" => $id, "user_id" => $_W["member"]["uid"], "uniacid" => $_W["uniacid"]));
				if (!empty($myunion)) {
					if ($myunion["team_id"] > 0) {
						$team_id = $myunion["team_id"];
					}
				}
			}
		}
		$data = array("title" => $crowd["title"], "description" => "完美众筹", "keywords" => "完美众筹", "pagename" => "home", "styles" => ["pages/crowd"], "scripts" => [], "team_id" => $team_id, "_share" => array("title" => $crowd["title"], "imgUrl" => !empty($crowd["coverimg"]) ? tomedia($crowd["coverimg"][0]) : '', "desc" => $crowd["summary"], "link" => add_para(appurl("crowd", "detail", "id=" . $id), "team_id", $team_id)), "crowd" => $crowd);
		if ($crowd["ntype"] == "0") {
			$data["rewards"] = $this->crowd_model->getrewards($id);
			$this->load->view("crowd_detail.html", $data);
		} else {
			$data["overnum"] = $overnum = pdo_fetch("SELECT count(id) as count FROM " . tablename("yhzc_crowd_union") . " where crowd_id={$crowd["id"]} and cjmoney>={$crowd["money"]}");
			$this->load->view("crowd_union.html", $data);
		}
	}

    /**
     * @notes: 提供zc详情信息
     * @author tongwz
     * @date: 2019年6月26日14:52:03
     */
	public function detailInfo()
    {
        global $_W;
        $post = $this->input->post();
        $id = isset($post['id']) ? $post['id'] : '';
        // 总展位数
        $crowdCount = isset($post['crowdCount']) ? $post['crowdCount'] : '';
        if (empty($id) || empty($crowdCount)) {
            echo json_encode(['code' => 0, 'msg' => '参数错误']);die;
        }
        // 获取zc详情 报名人数bmnum 目标金额money
        $crowd = $this->crowd_model->getcrowd_row($id);
        if (isset($crowd["ntype"]) && $crowd["ntype"] == 0) {
            echo json_encode(['code' => 0, 'msg' => '个人众筹暂时无数据']);die;
        }
        $data['crowd'] = $crowd;
        // 联合zc成功人数
        $overnum = pdo_fetch("SELECT count(id) as count FROM " . tablename("yhzc_crowd_union") . " where crowd_id={$crowd["id"]} and cjmoney>={$crowd["money"]}");
        $data["overnum"] = isset($overnum['count']) ? intval($overnum['count']) : 0;
        // 剩余展位数量
        $data['surplusNum'] = $crowdCount - $data["overnum"];

        echo json_encode(['code' => 1, 'msg' => '成功', 'data' => $data]);
    }

    /**
     * @notes: 获取众筹榜报名选手列表
     * @author tongwz
     * @date: 2019年6月29日10:52:51
     */
    public function getCrowdUsers()
    {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $id = $this->input->get('crowd_id');
        if ($page == '') {
            $page = 1;
        }
        if ($limit == '') {
            $limit = 10;
        }
        $unionArr = $this->crowd_model->getCrowdUnionList($page, $limit, $id);
        if (empty($unionArr)) {
            $arr["code"] = 0;
            $arr["msg"] = "还没有人报名！";
        } else {
            foreach ($unionArr as $key => $val) {
                $tmpMoney = round(($val['cjmoney'] / $val['money']) * 100, 6);
                if ($tmpMoney > intval($tmpMoney)) {
                    $tmpMoney = ceil($tmpMoney);
                }
                $unionArr[$key]['chart'] = $tmpMoney;
            }
            $counts = $this->crowd_model->getcrowd_union_count(false);
            $count = $counts["count"];
            $arr["code"] = 1;
            $arr["msg"] = "success";
            $arr["data"] = $unionArr;
            $arr["page"] = [ceil($count / $limit), intval($count)];
        }
        die(json_encode($arr));
    }

    // 光荣榜信息
    public function getCrowdHonourUsers()
    {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $id = $this->input->get('crowd_id');
        if ($page == '') {
            $page = 1;
        }
        if ($limit == '') {
            $limit = 10;
        }
        $unionArr = $this->crowd_model->getCrowdHonourUnionList($page, $limit, $id);
        if (empty($unionArr)) {
            $arr["code"] = 0;
            $arr["msg"] = "光荣榜上暂无人入选！";
        } else {
            foreach ($unionArr as $key => $val) {
                $tmpMoney = round(($val['cjmoney'] / $val['money']) * 100, 6);
                if ($tmpMoney > intval($tmpMoney)) {
                    $tmpMoney = ceil($tmpMoney);
                }
                $unionArr[$key]['chart'] = $tmpMoney;
                // 完成时间
                if (!empty($val['endtime'])) {
                    $unionArr[$key]['endDate'] = date('Y-m-d H:i:s', $val['endtime']);
                } else {
                    $unionArr[$key]['endDate'] = '0';
                }
            }
            $counts = $this->crowd_model->getcrowd_union_count(false);
            $count = $counts["count"];
            $arr["code"] = 1;
            $arr["msg"] = "success";
            $arr["data"] = $unionArr;
            $arr["page"] = [ceil($count / $limit), intval($count)];
        }
        die(json_encode($arr));
    }
	public function getreports()
	{
		$crowd_id = $this->input->get("crowd_id");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			die(json_encode(array("id" => -1, "msg" => "项目不存在")));
		}
		$where = '';
		$wheres = array();
		if ($crowd["ntype"] == 0) {
			$where .= " crowd_id=:crowd_id";
			$wheres[":crowd_id"] = $crowd_id;
		} else {
			if ($crowd["ntype"] == "1") {
				$union_id = $this->input->get("union_id");
				$where .= "crowd_id=:crowd_id and union_id=:union_id";
				$wheres[":crowd_id"] = $crowd_id;
				$wheres[":union_id"] = $union_id;
			}
		}
		$where .= " and (status=1 or status=-2)";
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		if ($page == '') {
			$page = 1;
		}
		if ($limit == '') {
			$limit = 10;
		}
		$reportarr = $this->crowd_model->getreport_all($where, $wheres, $page == '' ? 1 : $page, $limit == '' ? 20 : $limit);
		$count = $this->crowd_model->getreport_count($where, $wheres)["count"];
		if (empty($reportarr)) {
			$arr["id"] = -1;
			$arr["msg"] = "还没有人支持此众筹项目！";
		} else {
			$arr["id"] = 1;
			$arr["msg"] = "success";
			$arr["data"] = $reportarr;
			$arr["page"] = [ceil($count / $limit), $count];
		}
		die(json_encode($arr));
	}
	public function getmarchs()
	{
		$crowd_id = $this->input->get("crowd_id");
		$marcharr = $this->crowd_model->getmarch_all($crowd_id);
		if (!empty($marcharr)) {
			$arr["id"] = 1;
			$arr["msg"] = "success";
			$arr["data"] = $marcharr;
		} else {
			$arr["id"] = -1;
			$arr["msg"] = "fail";
		}
		die(json_encode($arr));
	}
	public function reward()
	{
		global $_W;
		$time = time();
		$crowd_id = $this->input->get("crowd_id");
		$union_id = $this->input->get("union_id");
		if ($union_id != '') {
			$union = $this->crowd_model->getcrowd_union_row($union_id);
			if (!empty($union)) {
				header("Location:" . appurl("crowd", "paydft", "crowd_id=" . $union["crowd_id"] . "&union_id=" . $union["id"]));
				exit;
			}
		}
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if ($crowd["ntype"] == "1") {
			echo "进入错误";
			exit;
		}
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["status"] != "1") {
			echo "该众筹项目正在准备中,暂时无法支持...";
			exit;
		} else {
			if ($crowd["starttime"] > $time) {
				echo "该众筹项目还没有开始，无法支持";
				exit;
			} else {
				if ($crowd["endtime"] < $time) {
					echo "该众筹项目已结束，无法支持";
					exit;
				}
			}
		}
		$rewards = $this->crowd_model->getrewards($crowd_id);
		if (empty($rewards)) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: " . appurl("crowd", "paydft", "crowd_id=" . $crowd_id));
			exit;
		}
		$data = array("title" => "支持|" . $crowd["title"], "description" => "完美众筹", "keywords" => "完美众筹", "pagename" => "home", "styles" => ["pages/crowd"], "scripts" => [], "_share" => array("title" => $crowd["title"], "imgUrl" => '', "desc" => '', "link" => appurl("crowd", "detail", "id=" . $crowd_id)), "crowd" => $crowd, "rewards" => $rewards);
		$this->load->view("crowd_reward.html", $data);
	}
	public function paydft()
	{
		$time = time();
		$ntp = $this->input->get("ntp");
		if ($ntp == '') {
			$ntp = 0;
		}
		$crowd_id = $this->input->get("crowd_id");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["ntype"] == "1") {
			$union_id = $this->input->get("union_id");
			$union = $this->crowd_model->getcrowd_union_row($union_id);
			if (empty($union)) {
				echo "错误的入口，无法支持";
				exit;
			} else {
				if ($crowd_id != $union["crowd_id"]) {
					echo "入口歧义";
					exit;
				}
			}
		}
		if ($crowd["status"] != "1") {
			echo "该众筹项目正在准备中,暂时无法支持...";
			exit;
		} else {
			if ($crowd["starttime"] > $time) {
				echo "该众筹项目还没有开始，无法支持";
				exit;
			} else {
				if ($crowd["endtime"] < $time) {
					echo "该众筹项目已结束，无法支持";
					exit;
				}
			}
		}
		$data = array("title" => $crowd["ntype"] == "1" ? "提交订单|支持" . $union["nickname"] : $crowd["title"], "description" => "完美众筹", "keywords" => "完美众筹", "pagename" => "home", "styles" => ["pages/crowd"], "scripts" => [], "_share" => array("title" => $crowd["title"], "imgUrl" => '', "desc" => '', "link" => appurl("crowd", "detail", "id=" . $crowd_id)), "crowd" => $crowd, "ntp" => $ntp);
		if ($crowd["ntype"] == "1") {
			$data["union"] = $union;
		}
		$this->load->view("crowd_pay_dft.html", $data);
	}
	public function pay()
	{
		global $_W;
		$time = time();
		$crowd_id = $this->input->get("crowd_id");
		$reward_id = $this->input->get("reward_id");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["status"] != "1") {
			echo "该众筹项目正在准备中,暂时无法支持...";
			exit;
		} else {
			if ($crowd["starttime"] > $time) {
				echo "该众筹项目还没有开始，无法支持";
				exit;
			} else {
				if ($crowd["endtime"] < $time) {
					echo "该众筹项目已结束，无法支持";
					exit;
				}
			}
		}
		if ($crowd["ntype"] == "0") {
			$reward = $this->crowd_model->getreward($reward_id);
			if (empty($reward)) {
				exit;
			}
			if ($reward["reward_count"] >= $reward["reward_limit"] && $reward["reward_limit"] != 0) {
				echo "该回报项次数已被抢完，无法支持";
				exit;
			}
		}
		$data = array("title" => "提交订单|" . $crowd["title"], "description" => "完美众筹", "keywords" => "完美众筹", "pagename" => "home", "styles" => ["pages/crowd"], "scripts" => [], "_share" => array("title" => $crowd["title"], "imgUrl" => '', "desc" => '', "link" => appurl("crowd", "detail", "id=" . $crowd_id)), "crowd" => $crowd, "reward" => $reward);
		if ($crowd["ntype"] == "0") {
			$data["reward"] = $reward;
		}
		$this->load->view("crowd_pay.html", $data);
	}

    /**
     * @notes: 提交订单 支付rmb
     * @author tongwz
     * @date: 2019年6月29日15:46:20
     */
	public function suborder()
	{
		global $_W;
		$time = time();
		$reward_id = $this->input->post("reward_id");
		$crowd_id = $this->input->post("crowd_id");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if ($crowd["ntype"] == "1") {
			$union_id = $this->input->post("union_id");
			$union = $this->crowd_model->getcrowd_union_row($union_id);
			if (empty($union)) {
				die(json_encode(array("id" => -1, "msg" => "错误的入口，无法支持")));
			} else {
				if ($crowd_id != $union["crowd_id"]) {
					die(json_encode(array("id" => -1, "msg" => "入口歧义")));
				} else {
					if ($union["cjmoney"] >= $crowd["money"]) {
						die(json_encode(array("id" => -1, "msg" => "该用户已筹集完成")));
					}
				}
			}
			if ($union["status"] != "1" && $union["user_id"] != $_W["member"]["uid"]) {
				die(json_encode(array("id" => -1, "msg" => "该用户报名正在等待审核或审核未通过")));
			}
		}
		if (empty($crowd)) {
			die(json_encode(array("id" => -1, "msg" => "众筹不存在")));
		}
		if ($crowd["status"] != "1") {
			die(json_encode(array("id" => -1, "msg" => "众筹无法参与")));
		} else {
			if ($crowd["starttime"] > $time) {
				die(json_encode(array("id" => -1, "msg" => "众筹未开始")));
			} else {
				if ($crowd["endtime"] < $time) {
					die(json_encode(array("id" => -1, "msg" => "众筹已结束")));
				} else {
					if ($crowd["money"] == 0) {
						die(json_encode(array("id" => -1, "msg" => "众筹错误，无法支持")));
					}
				}
			}
		}
		if ($crowd["ntype"] == "0" && $reward_id != 0) {
			$reward = $this->crowd_model->getreward($reward_id);
			if (empty($reward)) {
				die(json_encode(array("id" => -1, "msg" => "此回报项不存在")));
			}
			if ($reward["reward_count"] >= $reward["reward_limit"] && $reward["reward_limit"] != 0) {
				die(json_encode(array("id" => -1, "msg" => "此回报项次数不足")));
			}
			$resp = $this->crowd_model->add_report($crowd, $reward, false);
		} else {
			if ($crowd["ntype"] == "1") {
				$resp = $this->crowd_model->add_report($crowd, false, $union);
			} else {
				$resp = $this->crowd_model->add_report($crowd, false, false);
			}
		}
		if ($resp) {
			die(json_encode(array("id" => 1, "msg" => "众筹订单已生成", "data" => array("order_id" => $resp["id"], "money" => $resp["money"]))));
		}
	}
	public function gopay()
	{
		$order_id = $this->input->get("order_id");
		$order = pdo_get("yhzc_crowd_report", array("id" => $order_id));
		$params = array("tid" => $order["id"], "ordersn" => date("YmdHis") . $order["crowd_id"] . $order["id"], "title" => "支持众筹" . $order["crowd_id"], "fee" => $order["money"], "user" => $order["nickname"] == '' ? "用户" : $order["nickname"]);
		$this->pay($params);
	}
	public function getunion()
	{
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		if ($page == '') {
			$page = 1;
		}
		if ($limit == '') {
			$limit = 10;
		}
		$unionarr = $this->crowd_model->getcrowd_union_all($page, $limit);
		if (empty($unionarr)) {
			$arr["id"] = -1;
			$arr["msg"] = "还没有人报名！";
		} else {
			$counts = $this->crowd_model->getcrowd_union_count(false);
			$count = $counts["count"];
			$arr["id"] = 1;
			$arr["msg"] = "success";
			$arr["data"] = $unionarr;
			$arr["page"] = [ceil($count / $limit), $count];
		}
		die(json_encode($arr));
	}

    /**
     * @notes: 我要报名众筹入口 如果登录的微信号uid等于众筹发起人，那么直接进入当前微信人的众筹详情页
     * @author tongwz
     * @date: 2019年6月29日15:01:25
     */
	public function unionenter()
	{
		global $_W;
		$time = time();
		$crowd_id = $this->input->get("crowd_id");
		$team_id = $this->input->get("team_id");
		$rptrow = pdo_get("yhzc_crowd_union", array("crowd_id" => $crowd_id, "user_id" => $_W["member"]["uid"], "uniacid" => $_W["uniacid"]));

		if (!empty($rptrow)) {
			header("Location:" . appurl("crowd", "uniondetail", "union_id=" . $rptrow["id"]));
			exit;
		}
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["status"] != "1") {
			echo "该众筹项目正在准备中,暂时无法报名...";
			exit;
		} else {
			if ($crowd["starttime"] > $time) {
				echo "该众筹项目还没有开始，无法报名";
				exit;
			} else {
				if ($crowd["endtime"] < $time) {
					echo "该众筹项目已结束，无法报名";
					exit;
				}
			}
		}
		if ($crowd["ntype"] == "0") {
			echo "错误的众筹类型！";
			exit;
		}
		if ($team_id > 0) {
			$team = pdo_get("yhzc_crowd_team", array("uniacid" => $_W["uniacid"], "id" => $team_id, "crowd_id" => $crowd_id));
			if (empty($team)) {
				$team_id = 0;
			}
		}
		$teamarr = pdo_getall("yhzc_crowd_team", array("uniacid" => $_W["uniacid"], "crowd_id" => $crowd_id, "status" => 1));
		$data = array("title" => "报名|" . $crowd["title"], "description" => "完美众筹", "keywords" => "完美众筹", "pagename" => "home", "styles" => ["pages/crowd", "mui.picker.min"], "scripts" => ["plugin/mui.picker.min"], "user" => $this->user, "teamarr" => $teamarr, "team_id" => $team_id, "_share" => array("title" => $crowd["title"], "imgUrl" => '', "desc" => '', "link" => appurl("crowd", "detail", "id=" . $crowd_id)), "crowd" => $crowd);
		$ckv = [];
		$teams = "[";
		if (!empty($teamarr)) {
			foreach ($teamarr as $kt => $ct) {
				$ckv[$ct["id"]] = $ct["name"];
				if ($kt == 0) {
					$teams .= "{value:'" . $ct["id"] . "',text:'" . $ct["name"] . "'}";
				} else {
					$teams .= ",{value:'" . $ct["id"] . "',text:'" . $ct["name"] . "'}";
				}
			}
		}
		$teams .= "]";
		$data["teams"] = $teams;
		$data["ckv"] = $ckv;
		$this->load->view("crowd_union_enter.html", $data);
	}

    /**
     * @notes: 提交联合众筹  报名信息
     * @author tongwz
     * @date: 2019年6月29日15:09:24
     */
	public function subunion()
	{
		global $_W;
		$time = time();
		$crowd_id = $this->input->get("crowd_id");
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			die(json_encode(array("id" => -1, "msg" => "系统错误")));
		}
		if ($crowd["status"] != "1") {
			die(json_encode(array("id" => -1, "msg" => "暂不支持报名，请稍后再试")));
		} else {
			if ($crowd["starttime"] > $time) {
				die(json_encode(array("id" => -1, "msg" => "该众筹还未开始")));
			} else {
				if ($crowd["endtime"] < $time) {
					die(json_encode(array("id" => -1, "msg" => "众筹已结束")));
				}
			}
		}
		if ($crowd["ntype"] == "0") {
			die(json_encode(array("id" => -1, "msg" => "系统错误")));
		}
		$union_id = $this->crowd_model->add_union($crowd);
		if ($union_id > 0) {
			if ($crowd["u_id"] > 0) {
				putmsg($crowd["u_id"], "您的联合众筹项目有人报名啦", "<p>您发布的众筹项目有人报名啦。</p><p>众筹标题：" . $crowd["title"] . "</p>");
			}
			die(json_encode(array("id" => 1, "data" => $union_id)));
		} else {
			die(json_encode(array("id" => -1, "msg" => "系统错误")));
		}
	}

    /**
     * @notes: 联合众筹详情
     * @author tongwz 修改
     * @date: 2019年6月29日14:40:53
     */
	public function uniondetail()
	{
		global $_W;
		$id = $this->input->get("union_id");
		$union = $this->crowd_model->getcrowd_union_row($id);
		if (empty($union)) {
			exit;
		}
		if ($union["status"] != "1" && $union["user_id"] != $_W["member"]["uid"]) {
			echo "该用户报名待审核...";
			exit;
		}
		$crowd = $this->crowd_model->getcrowd_row($union["crowd_id"]);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["status"] != "1") {
			echo "该众筹项目正在准备中,请稍后再来...";
			exit;
		}
        // 进入众筹详情页 浏览量+1 更新时间 变更
        pdo_update("yhzc_crowd_union", array("viewnum +=" => 1, 'updatetime' => time()), array("id" => $id));
		$f_uid = $this->input->get("f_uid");
		if ($f_uid > 0 && $_W["member"]["uid"] != $f_uid && !isset($_SESSION["union_" . $union["crowd_id"] . "_f_uid"])) {
			$rpttotal = pdo_fetchcolumn("select count(id) from " . tablename("yhzc_crowd_union") . " where user_id={$_W["member"]["uid"]} and crowd_id={$crowd["id"]} and uniacid={$_W["uniacid"]}");
			if ($rpttotal == 0) {
				$_SESSION["union_" . $union["crowd_id"] . "_f_uid"] = $f_uid;
			}
		}
		$data = array("title" => ($union["nickname"] == '' ? "用户" : $union["nickname"]) . "的众筹|" . $crowd["title"], "description" => "完美众筹", "keywords" => "完美众筹", "pagename" => "home", "styles" => ["pages/crowd"], "scripts" => [], "_share" => array("title" => ($union["nickname"] == '' ? "用户" : $union["nickname"]) . "求支持：" . $crowd["title"], "imgUrl" => $union["avatar"], "desc" => $union["summary"], "link" => add_para(add_para(appurl("crowd", "uniondetail", "union_id=" . $id), "team_id", $union["team_id"]), "f_uid", $_W["member"]["uid"])), "crowd" => $crowd, "union" => $union);
		$this->load->view("crowd_union_detail.html", $data);
	}

    /**
     * @notes: 众筹个人详情页 接口
     * @author tongwz
     * @date: 2019年6月29日21:06:17
     */
    public function unionUserInfo()
    {
        global $_W;
        $id = $this->input->get("union_id");
        // 获取联合众筹个人信息
        $union = $this->crowd_model->getcrowd_union_row($id);
        if (empty($union)) {
            die(json_encode(['code' => 0, 'msg' => '参数错误']));
        }
        if ($union["status"] != "1" && $union["user_id"] != $_W["member"]["uid"]) {
            die(json_encode(['code' => 0, 'msg' => '该用户报名待审核...']));
        }
        // 获取众筹信息
        $crowd = $this->crowd_model->getcrowd_row($union["crowd_id"]);
        if (empty($crowd)) {
            die(json_encode(['code' => 0, 'msg' => '无法找到众筹信息']));
        }
        $isSelf = $union['user_id'] == $_W['member']['uid'] ? 1 : 0;
        $data = [
            'unoin' => $union,
            'crowd' => $crowd,
            'isSelf' => $isSelf,
        ];
        die(json_encode(['code' => 1, 'msg' => 'success', 'data' => $data]));
    }
	public function unionsummary()
	{
		global $_W;
		$union_id = $this->input->get("union_id");
		$summary = $this->input->post("summary");
		if (mb_strlen($summary, "UTF8") > 20) {
			die(json_encode(array("id" => -1, "msg" => "口号长度最大为20个字符")));
		}
		if ($this->crowd_model->update_union(array("summary" => $summary), array("id" => $union_id, "user_id" => $_W["member"]["uid"]))) {
			die(json_encode(array("id" => 1, "msg" => "OK")));
		} else {
			die(json_encode(array("id" => -1, "msg" => "fail")));
		}
	}
	public function teamview()
	{
		global $_W;
		$user = $this->user;
		$team_id = $this->input->get("team_id", true);
		if ($_W["isajax"] && $_W["ispost"]) {
			$page = $this->input->post_get("page");
			if ($page == '') {
				$page = 1;
			}
			$psize = 15;
			$pindex = max(1, intval($page));
			$sql = "SELECT * FROM " . tablename("yhzc_crowd_union") . " WHERE `team_id` = :team_id and uniacid={$_W["uniacid"]} and status=1 ORDER BY `addtime` DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
			$datas = pdo_fetchall($sql, array(":team_id" => $team_id));
			if (!empty($datas)) {
				exit(json_encode($datas));
			} else {
				exit(json_encode(array("state" => "error")));
			}
		}
		$team = pdo_get("yhzc_crowd_team", array("id" => $team_id, "uniacid" => $_W["uniacid"]));
		if (empty($team)) {
			exit;
		}
		$pagesql = "SELECT COUNT(*) FROM " . tablename("yhzc_crowd_union") . "WHERE `team_id` = :team_id and uniacid={$_W["uniacid"]}";
		$total = pdo_fetchcolumn($pagesql, array(":team_id" => $team_id));
		$pagenums = ceil($total / 15);
		$data = array("pagenums" => $pagenums, "title" => $team["name"], "description" => '', "keywords" => '', "styles" => ["pages/crowd"], "scripts" => [], "_share" => array("title" => $team["name"], "imgUrl" => '', "desc" => $team["summary"], "link" => add_para(appurl("crowd", "unionenter", "crowd_id=" . $team["crowd_id"]), "team_id", $team["id"])), "team" => $team, "user" => $user);
		$this->load->view("crowd_team.html", $data);
	}
	public function team()
	{
		global $_W;
		$time = time();
		$user = $this->user;
		$crowd_id = $this->input->get("crowd_id", true);
		$crowd = $this->crowd_model->getcrowd_row($crowd_id);
		if (empty($crowd)) {
			exit;
		}
		if ($crowd["status"] != "1") {
			exit;
		} else {
			if ($crowd["starttime"] > $time) {
				exit;
			} else {
				if ($crowd["endtime"] < $time) {
					exit;
				}
			}
		}
		if ($crowd["ntype"] == "0" || $crowd["team"] != 2) {
			exit;
		}
		$team = pdo_get("yhzc_crowd_team", array("crowd_id" => $crowd_id, "uid" => $_W["member"]["uid"], "uniacid" => $_W["uniacid"]));
		if (!empty($team)) {
			header("Location:" . appurl("crowd", "teamview", "team_id=" . $team["id"]));
			exit;
		}
		if ($_POST) {
			$phone = $this->input->post("phone", true);
			$realname = $this->input->post("realname", true);
			$name = $this->input->post("name", true);
			$summary = $this->input->post("summary", true);
			if ($phone == '') {
				die(json_encode(array("id" => -1, "msg" => "请正确输入手机号码")));
			}
			if ($realname == '') {
				die(json_encode(array("id" => -1, "msg" => "请正确输入真实姓名")));
			}
			if ($name == '') {
				die(json_encode(array("id" => -1, "msg" => "请正确输入队伍名称")));
			}
			if ($summary == '') {
				die(json_encode(array("id" => -1, "msg" => "请正确输入队伍口号")));
			}
			$rptrow = pdo_get("yhzc_crowd_team", array("crowd_id" => $crowd_id, "uniacid" => $_W["uniacid"], "name" => $name));
			if (!empty($rptrow)) {
				die(json_encode(array("id" => -1, "msg" => "队伍名称已存在，请使用其他名称")));
			}
			if ($phone != '' && "1" != $user["phoneak"]) {
				$code = $this->input->post("code", true);
				if ($code == '') {
					die(json_encode(array("id" => -1, "msg" => "请正确输入验证码")));
				}
				$coderow = pdo_get("yhzc_smslog", array("code" => $code, "uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"], "nfrom" => 0));
				if (empty($coderow)) {
					die(json_encode(array("id" => -1, "msg" => "请正确输入验证码")));
				} else {
					if (time() - $coderow["addtime"] > 300) {
						die(json_encode(array("id" => -1, "msg" => "验证码已超时，请重新获取")));
					}
					if ($user["phone"] != $phone) {
						die(json_encode(array("id" => -1, "msg" => "请使用新输入的手机号接收验证码")));
					}
				}
				if ($user["phoneak"] != "1") {
					$result = pdo_update("yhzc_user", array("phoneak" => 1), array("uid" => $_W["member"]["uid"], "uniacid" => $_W["uniacid"]));
					if (empty($result)) {
						die(json_encode(array("id" => -1, "msg" => "系统异常")));
					}
				}
			}
			$datas = array("uniacid" => $_W["uniacid"], "crowd_id" => $crowd_id, "uid" => $user["uid"], "realname" => $realname, "phone" => $phone, "name" => $name, "summary" => $summary, "addtime" => $time, "lasttime" => 0, "status" => 0);
			$result = pdo_insert("yhzc_crowd_team", $datas);
			if (!empty($result)) {
				$team_id = pdo_insertid();
				die(json_encode(array("id" => 1, "data" => $team_id)));
			} else {
				die(json_encode(array("id" => -1, "msg" => "系统异常")));
			}
		}
		$data = array("title" => "创建队伍", "description" => '', "keywords" => '', "styles" => ["pages/crowd"], "scripts" => [], "_share" => array("title" => $crowd["title"], "imgUrl" => '', "desc" => '', "link" => ''), "crowd" => $crowd, "user" => $user);
		$this->load->view("crowd_team_sub.html", $data);
	}
	public function editteam()
	{
		global $_W;
		$user = $this->user;
		$team_id = $this->input->get("team_id", true);
		$team = pdo_get("yhzc_crowd_team", array("id" => $team_id, "uniacid" => $_W["uniacid"]));
		if (empty($team)) {
			exit;
		}
		if ($team["uid"] != $user["uid"]) {
			exit;
		}
		if ($_W["isajax"] && $_W["ispost"]) {
			$name = $this->input->post("name", true);
			$summary = $this->input->post("summary", true);
			$data = array();
			if ($name != '' && $name != $team["name"]) {
				$rptrow = pdo_fetch("SELECT id FROM " . tablename("yhzc_crowd_team") . " where crowd_id={$team["crowd_id"]} and `name` like '%{$name}%' and id<>{$team["id"]}");
				if (!empty($rptrow)) {
					die(json_encode(array("id" => -1, "msg" => "队伍名称已存在")));
				}
				$data["name"] = $name;
			}
			if ($summary != '' && $summary != $team["summary"]) {
				$data["summary"] = $summary;
			}
			if (!empty($data)) {
				$uvar = pdo_update("yhzc_crowd_team", $data, array("id" => $team_id));
				if (!empty($uvar)) {
					die(json_encode(array("id" => 1, "msg" => "修改成功")));
				}
			}
		}
	}
	public function getteam()
	{
		$page = $this->input->get("page");
		$limit = $this->input->get("limit");
		if ($page == '') {
			$page = 1;
		}
		if ($limit == '') {
			$limit = 10;
		}
		$unionarr = $this->crowd_model->getcrowd_team_all($page, $limit);
		if (empty($unionarr)) {
			$arr["id"] = -1;
			$arr["msg"] = "没有找到队伍！";
		} else {
			$counts = $this->crowd_model->getcrowd_team_count(false);
			$count = $counts["count"];
			$arr["id"] = 1;
			$arr["msg"] = "success";
			$arr["data"] = $unionarr;
			$arr["page"] = [ceil($count / $limit), $count];
		}
		die(json_encode($arr));
	}
	public function myunion()
	{
		global $_W;
		$data = array("title" => "我的报名", "description" => "亿恒众筹", "keywords" => "亿恒众筹", "styles" => ["pages/home", "pages/crowd"], "scripts" => [], "_share" => array("title" => '', "imgUrl" => '', "desc" => '', "link" => ''));
		$this->load->view("my_union.html", $data);
	}
	public function myunionjson()
	{
		$crowds = $this->crowd_model->getmyunion_all();
		if (!empty($crowds)) {
			$arr["id"] = 1;
			$arr["msg"] = "success";
			$arr["data"] = $crowds;
		} else {
			$arr["id"] = -1;
			$arr["msg"] = "没有找到";
		}
		die(json_encode($arr));
	}
}