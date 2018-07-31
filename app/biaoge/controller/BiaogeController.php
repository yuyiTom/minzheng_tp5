<?php

/**
yuyi

 * 老人列表
 */
// namespace app\admin\controller;
namespace app\biaoge\controller;

use cmf\controller\HomeBaseController;
use think\Db;

class BiaogeController extends HomeBaseController {


	function _initialize() 
	{
		parent::_initialize();
	}
	

	public function biaoge_one()
	{

		 // return $this->fetch('biaoge_one');
		 return $this->fetch();
		
	}

	public function biaoge_two()
	{
		// 这两个一样
		// themes/admin_simpleboot3/older\biaoge\biaoge2.html
		// return $this->fetch();
		// themes/admin_simpleboot3/older\biaoge\biaoge2.html
		// return $this->fetch('biaoge2'); 



		// themes/admin_simpleboot3/older\\biaoge2.html
		// return $this->fetch(':biaoge2'); 

		// themes/admin_simpleboot3/older\biaoge.html
		// return $this->fetch('/biaoge'); 


	}




}