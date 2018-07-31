<?php

/**
yuyi

 * 老人档案信息
 */
namespace app\region\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class RegionController extends AdminBaseController {


	function _initialize() 
	{
		parent::_initialize();
	}



	public function index()
	{
		$id=$this->request->param('id',1,"intval");
		// echo $id;


		$data=array();
		$street_office=Db::name('street_office');
		$street_office_info=$street_office->where(array('pid'=>$id))->order("id desc")->select();
		foreach ($street_office_info as $value) {

			$param = array();
			$param['id']=$value['id'];
			$param['name']=$value['name'];
			$param['pid']=$value['pid'];
			$param['level']=$value['level'];
			$param['state']=$value['state'];
			$child_r=$street_office->where(array('pid'=>$param['id']))->order("id desc")->select();
			$param['child_count']=sizeof($child_r);
			foreach ($child_r as $val) {
				$p_c = array();

				$p_c['c_id']=$val['id'];
				$p_c['c_name']=$val['name'];
				$p_c['c_pid']=$val['pid'];
				$p_c['c_level']=$val['level'];
				$p_c['c_state']=$val['state'];
				$param['child'][$val['id']]=$p_c;
			}
			$data[$value['id']]=$param;

		}

		// print_r(json_encode($data));
		// exit;

		$this->assign('data', $data);
		$this->assign('id', $id);
		return $this->fetch();

	}






}