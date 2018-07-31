<?php



namespace app\timeBank\controller;

use cmf\controller\AdminBaseController;
use think\Db;


class TimeBankController extends AdminBaseController {



	function _initialize() 
	{
		parent::_initialize();
	}



	public function index()
	{

		$tb_account=DB::name('tb_account');
		$input_person_id=session('ADMIN_ID');//录入人id
		$input_name=session('name');

		$where_ands=array();
		// 不要用like   直接用=
		$fields=array(
				'name'  => array("field"=>"name","operator"=>"like"),
				'phone'  => array("field"=>"phone","operator"=>"="),
				'id_card'  => array("field"=>"id_card","operator"=>"=")
		);
		// 表单提交 是post  老版本IS_POST
		if($this->request->POST()){
			foreach ($fields as $param =>$val){
				if (isset($_POST[$param]) && !empty($_POST[$param])) {
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

		$count=$tb_account
			->order("id DESC")
			->where($where)
			->count();

		$accoun_info=$tb_account
			->order("id DESC")
			->where($where)
			->paginate(20);


		// 新版 返回搜索参数 的传递方式
    	$params=$this->request->param();
    	$this->assign('lists', $accoun_info);
		$this->assign('count', $count);
		//在 render 前，使用appends方法保持分页条件
		$accoun_info->appends($params);
		$this->assign('page', $accoun_info->render());//单独提取分页出来
		// 渲染模板输出
		return $this->fetch();


	}

// addAccount  其中A 大写则对应html文件名 add_account  要加一个下划线
	public function addAccount()
	{
		$session_name=session('name');
		$create_time=time();
		$this->assign("create_time",$create_time);
		$this->assign("session_name",$session_name);
		return $this->fetch();
	}

	public function addaccount_post()
	{
		$tb_account=Db::name('tb_account');
		$post=isset($_POST['post'])?$_POST['post']:"";
		// print_r(json_encode($post));
		if($post=="")
		{
			$this->error("数据传输有误！");
		}

		$id_card=isset($post['id_card'])?$post['id_card']:"";


		// 验证:合法性，重复性
		$reg = "/(^\d{18}$)|(^\d{17}(\d|X|x)$)/";
		$flag=preg_match($reg,$id_card);
		if(!$flag)
		{
			$this->error("身份证号码不合法！");
		}
		$find_idcard=$tb_account
		->where(array('id_card'=>$id_card))->find();
		if($find_idcard)
		{
			$this->error("身份证号码已存在，请勿重复添加！");
		}
		$session_name=session('name');
		$input_person_id=session('ADMIN_ID');

		// 验证密码
		$password=$post['password'];
		$password2=$post['password2'];
		if($password!=$password2)
		{
			$this->error("两次输入的密码不一致！");
		}

		$arr=array();
		$arr['name']=trim($post['name']);
		$arr['id_card']=$post['id_card'];
		$arr['sex']=$post['sex'];
		$arr['tel']=trim($post['tel']);
		$arr['phone']=trim($post['phone']);
		$arr['address']=$post['address'];
		$arr['note']=$post['note'];
		$arr['input_person_id']=$input_person_id;
		$arr['agent']=$session_name;
		$arr['create_time']=time();
		$arr['password']=$post['password'];
		$arr['psw_md5']=$this->psw_md5($post['password']);

		// 没有 add方法
		$arr_info=$tb_account->insert($arr);
		if($arr_info)
		{
			$this->success("添加成功！");
		}else
		{
			$this->error("添加失败！");
		}

	}

	public function editAccount(){

		$id  = $this->request->param('id', 0, 'intval');
		// $id = intval(I("get.id"));
		$tb_account=Db::name('tb_account');
		$info=$tb_account->where(array('id'=>$id))->find();
		$post=array();
		$post['id']=$info['id'];
		$post['agent']=$info['agent'];
		$post['name']=$info['name'];
		$post['sex']=$info['sex'];
		$post['tel']=$info['tel'];
		$post['phone']=$info['phone'];
		$post['id_card']=$info['id_card'];
		$post['address']=$info['address'];
		$post['time']=$info['time'];
		$post['note']=$info['note'];
		$post['input_person_id']=$info['input_person_id'];
		$post['create_time']=date('Y-m-d H:i:s',$info['create_time']);
		$post['statuse']=$info['statuse'];
		$this->assign("post",$post);
		// $this->assign("smeta",json_decode($post['smeta'],true));
		return $this->fetch();


	}


	public function editAccount_post()
	{
		$tb_account=Db::name('tb_account');
		$post=isset($_POST['post'])?$_POST['post']:"";
		if($post=="")
		{
			$this->error("数据传输有误！");
		}

		$id_card=isset($post['id_card'])?$post['id_card']:"";
		$id=isset($post['id'])?$post['id']:"";

		// 验证:合法性，重复性
		$reg = "/(^\d{18}$)|(^\d{17}(\d|X|x)$)/";
		$flag=preg_match($reg,$id_card);
		if(!$flag)
		{
			$this->error("身份证号码不合法！");
		}
		$find_idcard=$tb_account
		->where(array('id_card'=>$id_card))->find();
		if($find_idcard&&$id!=$find_idcard['id'])
		{
			// 编辑时候有id但是不是自己的
		    $this->error("身份证号码已存在，请勿重复添加！");
		}

		$arr=array();
		$arr['name']=trim($post['name']);
		$arr['sex']=$post['sex'];
		$arr['id_card']=$post['id_card'];
		$arr['tel']=trim($post['tel']);
		$arr['phone']=trim($post['phone']);
		$arr['address']=$post['address'];
		$arr['note']=$post['note'];

		$result=$tb_account->where(array('id'=>$id))->update($arr);
		if ($result!==false) {
			$this->success("保存成功！");
		} else {
			$this->error("保存失败！");
		}


	}


	public function show_tb_details()
	{

		$tb_account=Db::name('tb_account');
		$id = $this->request->param('id', 0, 'intval');
		$info=$tb_account->where(array('id'=>$id))->find();

		$post=array();
		$post['name']=$info['name'];
		$post['id_card']=$info['id_card'];
		$post['sex']=$info['sex'];
		$post['tel']=$info['tel'];
		$post['phone']=$info['phone'];
		$post['address']=$info['address'];
		$post['time']=$info['time'];
		$post['note']=$info['note'];
		$post['input_person_id']=$info['input_person_id'];
		$post['agent']=$info['agent'];
		$post['create_time']=date('Y-m-d H:i:s',$info['create_time']);
		$post['statuse']=$info['statuse'];

		$this->assign("post",$post);

		return $this->fetch();

	}


	public function savetime()
	{

		$tb_account=Db::name('tb_account');
		$id = $this->request->param('id', 0, 'intval');
		$info=$tb_account->where(array('id'=>$id))->find();
		$post=array();
		$post['id']=$info['id'];
		$post['name']=$info['name'];
		$post['id_card']=$info['id_card'];
		$post['phone']=$info['phone'];
		$post['time']=$info['time'];
		$post['create_time']=date('Y-m-d H:i:s',time());
		$a_name=session('name');

		$this->assign("post",$post);
		$this->assign("a_name",$a_name);
		return $this->fetch();


	}

	public function savetime_post(){

		$tb_account=Db::name('tb_account');
		$tb_passbook=Db::name('tb_passbook');
		$post=isset($_POST['post'])?$_POST['post']:"";
		$id=$post['id'];

		$info=$tb_account->where(array('id'=>$id))->find();


		$a_name=session('name');

		$arr=array();
		$arr['a_id']=$post['id'];

		// 账户人 信息要重新从后台获取
		$arr['name']=$info['name'];
		$arr['id_card']=$info['id_card'];


		$arr['time_change']=$post['save_time'];
		$arr['type']=1;
		$arr['create_time']=time();
		$arr['input_person_id']=session('ADMIN_ID');;
		$arr['agent']=session('name');
		$arr['note']=$post['note'];


		// tp自带的 setInc  字段累加
		// 以前的这种方法不好使  array("exp","time + {$post['save_time']}");
		$tb_account_add=$tb_account->where(array('id'=>$id))->setInc('time',$post['save_time']);
		if($tb_account_add)
		{	
			$arr_info=$tb_passbook->insert($arr);
			if($arr_info)
			{
				$this->success("存时成功！");
			}else
			{
				$this->error("存时失败！");
			}

		}else
		{
			$this->error("存时失败！2");
		}

	}

	public function taketime()
	{
		$tb_account=Db::name('tb_account');
		$id = $this->request->param('id', 0, 'intval');
		$info=$tb_account->where(array('id'=>$id))->find();
		$post=array();
		$post['id']=$info['id'];
		$post['name']=$info['name'];
		$post['id_card']=$info['id_card'];
		$post['phone']=$info['phone'];
		$post['time']=$info['time'];
		$post['create_time']=date('Y-m-d H:i:s',time());
		$a_name=session('name');

		$this->assign("post",$post);
		$this->assign("a_name",$a_name);
		return $this->fetch();

	}

	public function taketime_post()
	{
		$tb_account=Db::name('tb_account');
		$tb_passbook=Db::name('tb_passbook');
		$post=isset($_POST['post'])?$_POST['post']:"";
		$id=$post['id'];
		$info=$tb_account->where(array('id'=>$id))->find();
		$a_name=session('name');
		$arr=array();
		$arr['a_id']=$post['id'];
		// 账户人 信息要重新从后台获取
		$arr['name']=$info['name'];
		$arr['id_card']=$info['id_card'];
		$arr['time_change']=$post['save_time'];
		$arr['type']=2;
		$arr['create_time']=time();
		$arr['input_person_id']=session('ADMIN_ID');
		$arr['agent']=session('name');
		$arr['note']=$post['note'];

		$tb_account_d=$tb_account->where(array('id'=>$id))->setDec('time',$post['save_time']);
		if($tb_account_d)
		{	
			$arr_info=$tb_passbook->insert($arr);
			if($arr_info)
			{
				$this->success("取时成功！");
			}else
			{
				$this->error("取时失败！");
			}

		}else
		{
			$this->error("取时失败！2");
		}


	}









	public function deleteaccount()
	{
		$tb_account=Db::name('tb_account');
		$id = $this->request->param('id', 0, 'intval');
		if ($tb_account->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}


	}

	// 密码md5 加密结构  md5("tb".$psw)
	public function psw_md5($psw)
	{
		return $psw_md5=md5("tb".$psw);
	}

	public function check_idcard()
	{
		$id_card=isset($_POST['id_card'])?$_POST['id_card']:"";
		$id=isset($_POST['id'])?$_POST['id']:"";
		$lrsj=Db::name('tb_account');
		$idcard_find=$lrsj->where(array('id_card'=>$id_card))->find();
		
		// 身份证号码存在
		if($idcard_find)
		{
			// 添加时没有id
			if($id=="")
			{
				print_r("1");
			}else if($id!=$idcard_find['id']) //编辑时候有id但是不是自己的
			{
				print_r("1");
			}else//编辑时候有id 是自己的
			{
				print_r("0");
			}

		}else // 不存在
		{
			print_r("0");
		}
	}



}




