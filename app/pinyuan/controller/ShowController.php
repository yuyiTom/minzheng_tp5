<?php

/**
yuyi

 * 医疗数据展示
 */
namespace app\Pinyuan\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class ShowController extends AdminBaseController {

	function _initialize() 
	{
		parent::_initialize();
	}


// 品源数据展示
	public function showpinyuan()
	{
		$fields=array(
			'name'  => array("field"=>"name","operator"=>"like")
		);

		$where_ands=array();
		if($this->request->post())
		{
			foreach ($fields as  $param =>$val) {
				# code...
				if(isset($_POST[$param])&&!empty($_POST[$param]))
				{
					$operator=$val['operator'];
					$field   =$val['field'];
					$get=$_POST["$param"];
					$_GET["$param"]=$get;
					if($operator=="like"){
						$get="%$get%";
					}
					array_push($where_ands, "$field $operator '$get'");
				}
			}
		}else
		{
			// 分页提交 用get
			foreach ($fields as $param =>$val){
				if (isset($_GET[$param]) && !empty($_GET[$param])) {
					$operator=$val['operator'];
					$field   =$val['field'];
					$get=$_GET["$param"];
					if($operator=="like"){
						$get="%$get%";
					}
					array_push($where_ands, "$field $operator '$get'");
				}
			}
		}
		$where= join(" and ", $where_ands);

		$pinyuan_bmd=Db::name('pinyuan_bmd');
		$count=$pinyuan_bmd->where($where)->count();
		
		$lists=$pinyuan_bmd->order("id DESC")
    	->where($where)
    	// 这里不用cmf3中的方法 即 limit与select配合使用
    	->paginate(20);

		// $lists=$pinyuan_bmd->select();
  //   	print_r($lists);
  //   	exit();

    	


		// 新版 返回搜索参数 的传递方式
    	$params=$this->request->param();

    	$this->assign('lists', $lists);
    	$this->assign('count', $count);
    	// 老版本的 查询form 的传参方式 因为项目逻辑 所以保留 。
    	// 不用 appends($_GET);
    	$this->assign("formget",$_GET);
		//在 render 前，使用appends方法保持分页条件
		$lists->appends($params);
		// $lists->appends($_GET);
		$this->assign('page', $lists->render());//单独提取分页出来
		// 渲染模板输出
		return $this->fetch("showpinyuan");

	}


	public function delete_py()
	{
		$pinyuan_bmd=Db::name("pinyuan_bmd");
		// $id = I("get.id",0,"intval");
		$id = $this->request->param('id', 0, 'intval');

		if ($pinyuan_bmd->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
		
	}



	public function show_details_py()
	{
		$pinyuan_bmd=Db::name("pinyuan_bmd");
		$id = $this->request->param('id', 0, 'intval');
		$info=$pinyuan_bmd->where("id = $id")->find();

		$post=array();
		$post['CheckID']=$info['CheckID'];
		$post['CheckNum']=$info['CheckNum'];
		$post['CheckTime']=$info['CheckTime'];
		$post['id']=$info['id'];
		$post['PatientName']=$info['PatientName'];
		$post['PatientGender']=$info['PatientGender'];
		$post['PatientBirthDate']=$info['PatientBirthDate'];
		$post['PatientAge']=$info['PatientAge'];
		$post['PatientRace']=$info['PatientRace'];
		$post['PatientFatherHeight']=$info['PatientFatherHeight'];
		$post['PatientMotherHeight']=$info['PatientMotherHeight'];
		$post['CheckPosition']=$info['CheckPosition'];
		$post['SOS']=$info['SOS'];
		$post['TScore']=$info['TScore'];
		$post['ZScore']=$info['ZScore'];
		$post['BMI']=$info['BMI'];
		$post['PAB']=$info['PAB'];
		$post['BQI']=$info['BQI'];
		$post['AdtPct']=$info['AdtPct'];
		$post['AgePct']=$info['AgePct'];
		$post['EOA']=$info['EOA'];
		$post['Risk']=$info['Risk'];
		$post['Suggestion']=$info['Suggestion'];
		$post['add_time']=$info['add_time'];

		
		$this->assign("post",$post);
		$this->assign("id",$id);
		
		return $this->fetch();

		

	}








}
