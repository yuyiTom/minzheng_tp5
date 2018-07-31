<?php

/**
yuyi

 * 老人列表
 */
// namespace app\admin\controller;
namespace app\older\controller;

use cmf\controller\AdminBaseController;
use think\Db;

// HomeBaseController
// AdminBaseController
class OlderController extends AdminBaseController {

	 /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 3000;


	function _initialize() 
	{
		parent::_initialize();
	}



/*  
	新的权限的逻辑判断
	http://localhost/minzheng/public/index.php/admin/older/index.html
	逻辑:
	1 先判断 mz_type 录入人员 账号类型 0:admin 1:shiqu 2:mz_street_office 3:juweihui



	


*/ 
	public function index()
	{
		// 获取 session 数据
		$input_person_id=session('ADMIN_ID');//录入人id
		$mz_type=session('mz_type'); //账号级别
		$shiqu=session('shiqu');// 市区
		$street_office_id=session('mz_street_office');//办事处
		// 分类展示暂时用不到
		$juweihui=session('juweihui');//居委会

		$where_ands=array();
		// 不要用like   直接用=
		$fields=array(
				'name'  => array("field"=>"name","operator"=>"like"),
				'phone'  => array("field"=>"phone","operator"=>"="),
				'idcard'  => array("field"=>"idcard","operator"=>"="),
				'input_year'  => array("field"=>"input_year","operator"=>"="),
				'huji_address'  => array("field"=>"huji_address","operator"=>"like"),
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
					// name like 'yu'
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

		// 是否展示 办事处下拉与户籍
		$show_street=0;
		// 是否展示 市区下拉
		$show_street_shiqu=0;
		if($mz_type!=0&&$mz_type!=1)
		{
			// 0 不显示
			$show_street=0;
			// 如果登录账号是办事处 则不显示下拉，但显示该办事处下全部的信息
			if($mz_type==2)
			{
				array_push($where_ands, "street_office_id = '$street_office_id'");
			}

			// 如果登录账号是居委会 则不显示下拉 只显示该人录入的信息
			if($mz_type==3)
			{
				array_push($where_ands, "input_person_id = '$input_person_id'");
			}

		}else
		{
			// 1 显示 办事处的下拉菜单,并显示全部的信息
			$show_street=1;
			// 1 为shiqu
			if($mz_type==1)
			{
				array_push($where_ands, "shiqu = '$shiqu'");
			}else  // 0 为 admin
			{
				$show_street_shiqu=1;
			}


		}
		// 把shiqu id传给前台
		$this->assign("shiqu",$shiqu);
		// 传值 两个显示标记 这种给get赋值 是老版本的方法
		$_GET['show_street']=$show_street;
		$_GET['show_street_shiqu']=$show_street_shiqu;


		// 接受前台下拉框  新老用户 判断 1新 2老
		$user_type=isset($_POST['user_type'])?$_POST['user_type']:"";
		if(!$user_type)
		{
			$user_type=isset($_GET['user_type'])?$_GET['user_type']:"";
		}
		$_GET['user_type']=$user_type;
		$this->assign("user_type",$user_type);//返回数据
		if($user_type!=0)
		{
			array_push($where_ands, "user_type = '$user_type'");
		}

		// 街道下拉框 街道 判断  0全部 1admin 2民政  这三个可以看全部
		// 其他的只能看自己街道的
		$street_office=isset($_POST['street_office'])?$_POST['street_office']:"";
		if(!$street_office)
		{
			$street_office=isset($_GET['street_office'])?$_GET['street_office']:"";
		}
		if($street_office!=0&&$street_office!=1&&$street_office!=2)
		{
			array_push($where_ands, "street_office_id = '$street_office'");
		}
		$_GET['street_office']=$street_office;
		$this->assign("street_office",$street_office);


		// shiqu  下拉
		$street_office_shiqu=isset($_POST['street_office_shiqu'])?$_POST['street_office_shiqu']:"";
		if(!$street_office_shiqu)
		{
			$street_office_shiqu=isset($_GET['street_office_shiqu'])?$_GET['street_office_shiqu']:"";
		}
		if($street_office_shiqu!=0)
		{
			array_push($where_ands, "shiqu = '$street_office_shiqu'");
		}
		$_GET['street_office_shiqu']=$street_office_shiqu;
		$this->assign("street_office_shiqu",$street_office_shiqu);


		$where= join(" and ", $where_ands);
		$lrsj_model=Db::name("lrsj");
    	$count=$lrsj_model->where($where)->count();

    	$lists = $lrsj_model
    	->order("id DESC")
    	->where($where)
    	// 这里不用cmf3中的方法 即 limit与select配合使用
    	->paginate(20);

		// // 测试 搜索条件
		// print_r(json_encode($where));


    	// 新版 返回搜索参数 的传递方式
    	$params=$this->request->param();


    	$this->assign('lists', $lists);
    	$this->assign('show_street', $show_street);
    	$this->assign('count', $count);
    	// 老版本的 查询form 的传参方式 因为项目逻辑 所以保留 。
    	// 不用 appends($_GET);
    	$this->assign("formget",$_GET);

		//在 render 前，使用appends方法保持分页条件
		$lists->appends($params);
		// $lists->appends($_GET);
		$this->assign('page', $lists->render());//单独提取分页出来

		// 渲染模板输出
		return $this->fetch();

	}


/*  yuyi

    获取pid的地区

   http://localhost/minzheng/public/index.php/admin/user/getRegionList

*/

    public function getRegionList()
    {

        $id=isset($_POST['id'])?$_POST['id']:0;
        // $id=1;
        $street_office=Db::name('street_office');
        if($id!=0)
        {
          $street_office_list=$street_office->where(array('pid'=>$id))->field('id,name,pid')->select();
	      echo json_encode($street_office_list);
        }else
        {
        	echo json_encode("id不可为0！");
        }

		// 这种写法在这里用不上 但可以学习
        // $json =null;
        // foreach ($street_office_list as $value) {
        //   $json .= json_encode($value) . ',';
        //   // $json .= json_encode($value['name']) . ',';
        //   // $json .= '"'.($value['name']) .'"'. ',';
        // } 
        // echo '[' . substr($json,0,strlen($json) - 1) . ']';
        // print_r(json_encode($street_office_list));

    }

	

	








/* 

	 不用继承 HomeBaseController  也可以直接访问admin下的 操作方法
	 http://localhost/minzheng/public/index.php/admin/older/index.html

*/
	public function index_yuanlai()
	{
		// 可以通过Request对象完成全局输入变量的检测、获取和安全过滤，
		// 支持包括$_GET、$_POST、$_REQUEST、$_SERVER、$_SESSION、
		// $_COOKIE、$_ENV 等系统变量，以及文件上传信息。
		// tp5 接受变量要 isset  先判断 后使用

		// 这两个不同
		// session("ss","ssssss");
		// $_SESSION['ss']="aaaa";

		// 	$request=$this->request;
		// 	$request->POST('id');

// unset($_SESSION);
// session_destroy();
// 		exit;


		$street_office_id=session('mz_street_office');
		$input_person_id=session('ADMIN_ID');
		// 录入人员 账号类型 0:admin,1民政，2办事处，3居委会
		$mz_type=session('mz_type');

		$where_ands=array();
		// 不要用like   直接用=
		$fields=array(
				'name'  => array("field"=>"name","operator"=>"like"),
				'phone'  => array("field"=>"phone","operator"=>"="),
				'idcard'  => array("field"=>"idcard","operator"=>"="),
				'input_year'  => array("field"=>"input_year","operator"=>"="),
				'huji_address'  => array("field"=>"huji_address","operator"=>"like"),
		);


		// 表单提交 是post  IS_POST
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
					// name like 'yu'
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

		// 不是admin与 民政 则不显示 办事处的下拉菜单
		if($mz_type!=0&&$mz_type!=1)
		{
			// 0 不显示
			$show_street=0;
			// 如果登录账号是办事处 则不显示下拉，但显示该办事处下全部的信息
			if($mz_type==2)
			{
				array_push($where_ands, "street_office_id = '$street_office_id'");
			}

			// 如果登录账号是居委会 则不显示下拉，只显示该人录入的信息
			if($mz_type==3)
			{
				array_push($where_ands, "input_person_id = '$input_person_id'");
				
			}

		}else
		{
			// 1 显示 办事处的下拉菜单,并显示全部的信息
			$show_street=1;
		}

		$_GET['show_street']=$show_street;


		// 下拉框  新老用户 判断 1新 2老
		$user_type=isset($_POST['user_type'])?$_POST['user_type']:"";
		if(!$user_type)
		{

			$user_type=isset($_GET['user_type'])?$_GET['user_type']:"";
		}
		$_GET['user_type']=$user_type;
		$this->assign("user_type",$user_type);
		if($user_type!=0)
		{
			array_push($where_ands, "user_type = '$user_type'");
		}

		// 下拉框 街道 判断  0全部 1admin 2民政  这三个可以看全部
		// 其他的只能看自己街道的
		$street_office=isset($_POST['street_office'])?$_POST['street_office']:"";
		if(!$street_office)
		{
			$street_office=isset($_GET['street_office'])?$_GET['street_office']:"";
		}
		if($street_office!=0&&$street_office!=1&&$street_office!=2)
		{
			array_push($where_ands, "street_office_id = '$street_office'");
		}
		$_GET['street_office']=$street_office;
		$this->assign("street_office",$street_office);

		$where= join(" and ", $where_ands);


		$lrsj_model=Db::name("lrsj");
    	$count=$lrsj_model->where($where)->count();

    	$lists = $lrsj_model
    	->order("id DESC")
    	->where($where)
    	// 这里不用cmf3中的方法 即 limit与select配合使用
    	->paginate(100);


    	$params=$this->request->param();


    	$this->assign('lists', $lists);
    	$this->assign('show_street', $show_street);
    	$this->assign('count', $count);
    	// 老版本的 查询form 的传参方式 因为项目逻辑 所以保留 。
    	// 不用 appends($_GET);
    	$this->assign("formget",$_GET);

		//在 render 前，使用appends方法保持分页条件
		$lists->appends($params);
		// $lists->appends($_GET);
		$this->assign('page', $lists->render());//单独提取分页出来

		// 渲染模板输出
		return $this->fetch();

	}


	public function add()
	{
		$mz_street_office=session('mz_street_office');
		$session_name=session('name');
		$shiqu=session('shiqu');

		$street_office=Db::name('street_office');
		$so_name=$street_office->where(array('id'=>$mz_street_office))->field("id,name")->find();
		$this->assign("so_name",$so_name);
		$this->assign("session_name",$session_name);
		$this->assign("shiqu",$shiqu);
		return $this->fetch();
	}



	public function add_post(){

		// success 与 error 跳转的 路径与样式不对
			
			$post=isset($_POST['post'])?$_POST['post']:"";
			$smeta=isset($_POST['smeta'])?$_POST['smeta']:"";
			$lrsj=Db::name('lrsj');

			$idcard=isset($post['idcard'])?$post['idcard']:"";
			$huji_address=isset($post['huji_address'])?$post['huji_address']:"";

			// 验证:合法性，重复性，验证是否够70岁
			$reg = "/(^\d{18}$)|(^\d{17}(\d|X|x)$)/";
			$flag=preg_match($reg,$idcard);
			if(!$flag)
			{
				$this->error("身份证号码不合法！");
				// exit;
			}
			$sfz_year=substr($idcard,6,4);
			$sfz_month=substr($idcard,10,2);
			$sfz_date=substr($idcard,12,2);
			$sfz_birth=$sfz_year.$sfz_month.$sfz_date;
			$today=date("Ymd");

			$cha_age=$today-$sfz_birth;
			if($cha_age<700000)
			{
				$this->error("身份证年龄未满70周岁！不予登记");
			}
			$find_idcard=$lrsj->where(array('idcard'=>$idcard))->find();
			$find_huji_address=$lrsj->where(array('huji_address'=>$huji_address))->find();
			if($find_idcard)
			{
				$this->error("身份证号码已存在，请勿重复添加！");
			}else if($find_huji_address)
			{
				$this->error("户籍地址已存在，请勿重复添加！");
			}

			$mz_street_office=session('mz_street_office');
			$session_name=session('name');
			$shiqu=session('shiqu');

			$juweihui=session('juweihui');
			$input_person_id=session('ADMIN_ID');


			$street_office=Db::name('street_office');
			$so_name=$street_office->where(['id'=>$mz_street_office])->field("id,name")->find();

			$street_office=Db::name('street_office');
			$street_office_name=$street_office->where(['id'=>$shiqu])->field("id,name")->find();
			
			$arr=array();
			$arr['street_office']=$so_name['name'];
			$arr['input_person']=$session_name;
			$arr['street_office_id']=$mz_street_office;
			$arr['input_person_id']=$input_person_id;

			$arr['shiqu_name']=$street_office_name['name'];
			$arr['shiqu']=$shiqu;



			$arr['name']=$post['name'];
			$arr['sex']=$post['sex'];
			$arr['age']=$post['age'];
			$arr['real_address']=$post['real_address'];
			$arr['huji_address']=trim($post['huji_address']);
			$arr['idcard']=trim($post['idcard']);
			$arr['phone']=trim($post['phone']);
			$arr['income']=$post['income'];
			$arr['economic_source']=$post['economic_source'];
			$arr['illness']=$post['illness'];
			$arr['self_ability']=$post['self_ability'];
			$arr['child_name']=$post['child_name'];
			$arr['child_sex']=$post['child_sex'];
			$arr['child_relation']=$post['child_relation'];
			$arr['child_phone']=$post['child_phone'];
			$arr['child_address']=$post['child_address'];
			$arr['creat_time']=time();
			$arr['edit_time']=$arr['creat_time'];
			$arr['user_type']=$post['user_type'];
			$arr['input_year']=date('Y',$arr['creat_time']);

			// 图片
			$arr['id_front_image']=$smeta['thumb_id_zheng'];
			$arr['house_image']=$smeta['thumb_id_huji'];
			$arr['personal_image']=$smeta['thumb_id_personal'];

			// 没有 add方法
			$arr_info=$lrsj->insert($arr);
			if($arr_info)
			{
				$this->success("添加成功！");
			}else
			{
				$this->error("添加失败！");
			}
		

	}

	public function edit(){

		

		// array_search() 函数与 in_array() 一样，在数组中查找一个键值。
		// 如果找到了该值，匹配元素的键名会被返回。如果没找到，则返回 false。 

		// $id =$_REQUEST['id'];
		// $id =$_GET['id'];
		// $id =$this->request->get('id');
		// $id_get =$this->request->get();
		// $id_get =$this->request->post();

		$id  = $this->request->param('id', 0, 'intval');
		// $id = intval(I("get.id"));
		$lrsj=Db::name('lrsj');
		$info=$lrsj->where(array('id'=>$id))->find();
		$post=array();
		$post['shiqu_name']=$info['shiqu_name'];
		$post['street_office']=$info['street_office'];
		$post['input_person']=$info['input_person'];
		$post['id']=$info['id'];
		$post['name']=$info['name'];
		$post['sex']=$info['sex'];
		$post['age']=$info['age'];
		$post['huji_address']=$info['huji_address'];
		$post['real_address']=$info['real_address'];
		$post['idcard']=$info['idcard'];
		$post['phone']=$info['phone'];
		$post['income']=$info['income'];
		$post['economic_source']=$info['economic_source'];
		$post['illness']=$info['illness'];
		$post['self_ability']=$info['self_ability'];
		$post['child_name']=$info['child_name'];
		$post['child_sex']=$info['child_sex'];
		$post['child_relation']=$info['child_relation'];
		$post['child_phone']=$info['child_phone'];
		$post['child_address']=$info['child_address'];
		// $post['creat_time']=$info['creat_time'];
		$post['creat_time']=date('Y-m-d H:i:s',$info['creat_time']);
		$post['edit_time']=date('Y-m-d H:i:s',$info['edit_time']);
		$post['user_type']=$info['user_type'];
		$post['input_year']=$info['input_year'];


		// print_r(json_encode($post));
		// exit;

		$smeta['thumb_id_zheng']=$info['id_front_image'];
		$smeta['thumb_id_huji']=$info['house_image'];
		$smeta['thumb_id_personal']=$info['personal_image'];

		
		$this->assign("post",$post);
		$this->assign("smeta",$smeta);
		// $this->assign("smeta",json_decode($post['smeta'],true));
		// $this->display();

		return $this->fetch();

	}



	public function edit_post()
	{
		// 展示界面效果不同
		// $this->success("保存成功！edit_post");
		// exit;

		$post=$_POST['post'];
		$smeta=$_POST['smeta'];

		$arr=array();
		
		$arr['name']=$post['name'];
		$arr['sex']=$post['sex'];
		$arr['age']=$post['age'];
		$arr['real_address']=$post['real_address'];
		$arr['phone']=$post['phone'];
		$arr['income']=$post['income'];
		$arr['economic_source']=$post['economic_source'];
		$arr['illness']=$post['illness'];
		$arr['self_ability']=$post['self_ability'];
		$arr['child_name']=$post['child_name'];
		$arr['child_sex']=$post['child_sex'];
		$arr['child_relation']=$post['child_relation'];
		$arr['child_phone']=$post['child_phone'];
		$arr['child_address']=$post['child_address'];
		$arr['edit_time']=time();
		$arr['user_type']=$post['user_type'];


		if(!empty($smeta['thumb_id_zheng']))
		{
			$arr['id_front_image']=$smeta['thumb_id_zheng'];
		}
		if(!empty($smeta['thumb_id_fan']))
		{
			$arr['id_back_image']=$smeta['thumb_id_fan'];
		}
		if(!empty($smeta['thumb_id_huji']))
		{
			$arr['house_image']=$smeta['thumb_id_huji'];
		}
		if(!empty($smeta['thumb_id_personal']))
		{
			$arr['personal_image']=$smeta['thumb_id_personal'];
		}



		$id = $post['id'];
		$lrsj=Db::name('lrsj');
		// 用 update 不用  save
		$result=$lrsj->where(array('id'=>$id))->update($arr);

		if ($result!==false) {
			$this->success("保存成功！");
		} else {
			$this->error("保存失败！");
		}

	}


// open_iframe_dialog
	public function show_details()
	{
		$lrsj_model=Db::name("lrsj");
		// $id = intval(I("get.id"));
		$id = $this->request->param('id', 0, 'intval');

		$lrsj=Db::name('lrsj');
		$info=$lrsj->where("id = $id")->find();
		$post=array();
		$post['shiqu_name']=$info['shiqu_name'];
		$post['street_office']=$info['street_office'];
		$post['input_person']=$info['input_person'];
		$post['id']=$info['id'];
		$post['name']=$info['name'];
		$post['sex']=$info['sex'];
		$post['age']=$info['age'];
		$post['huji_address']=$info['huji_address'];
		$post['real_address']=$info['real_address'];
		$post['idcard']=$info['idcard'];
		$post['phone']=$info['phone'];
		$post['income']=$info['income'];
		$post['economic_source']=$info['economic_source'];
		$post['illness']=$info['illness'];
		$post['self_ability']=$info['self_ability'];
		$post['child_name']=$info['child_name'];
		$post['child_sex']=$info['child_sex'];
		$post['child_relation']=$info['child_relation'];
		$post['child_phone']=$info['child_phone'];
		$post['child_address']=$info['child_address'];
		// $post['creat_time']=$info['creat_time'];
		$post['creat_time']=date('Y-m-d H:i:s',$info['creat_time']);
		$post['edit_time']=date('Y-m-d H:i:s',$info['edit_time']);
		$post['user_type']=$info['user_type'];
		$post['input_year']=$info['input_year'];


		// print_r(json_encode($post));

		$smeta['thumb_id_zheng']=$info['id_front_image'];
		$smeta['thumb_id_huji']=$info['house_image'];
		$smeta['thumb_id_personal']=$info['personal_image'];

		
		$this->assign("post",$post);
		$this->assign("smeta",$smeta);
		$this->assign("id",$id);
		
		return $this->fetch();

	}



	public function check_huji_address()
	{
		$huji_address=$_POST['huji_address'];
		$lrsj=Db::name('lrsj');
		$ha_count=$lrsj->where(array('huji_address'=>$huji_address))->find();
		if($ha_count)
		{
			print_r("1");

		}else
		{
			print_r("0");
		}
		// echo $ha_count;

	}


	public function check_idcard()
	{
		$idcard=$_POST['idcard'];
		$lrsj=Db::name('lrsj');
		$idcard_find=$lrsj->where(array('idcard'=>$idcard))->find();
		if($idcard_find)
		{
			print_r("1");

		}else
		{
			print_r("0");
		}
		
		
	}

	public function check_phone()
	{
		$phone=$_POST['phone'];
		$lrsj=Db::name('lrsj');
		$phone_count=$lrsj->where(array('phone'=>$phone))->find();
		if($phone_count)
		{
			print_r("1");

		}else
		{
			print_r("0");
		}
		// echo $phone_count;

	}


	/**
	 *  删除

	 */
	function delete(){


		$lrsj=Db::name("lrsj");
		// $id = I("get.id",0,"intval");
		$id =  $this->request->param('id', 0, 'intval');

		$f_info=$lrsj->where(array('id'=>$id))->find();
		$id_front_image=$f_info['id_front_image'];
		$house_image=$f_info['house_image'];
		$personal_image=$f_info['personal_image'];


// 原版本是 /minzheng
		$id_front_image=str_replace("/minzheng/public",".",$id_front_image);
		$house_image=str_replace("/minzheng/public",".",$house_image);
		$personal_image=str_replace("/minzheng/public",".",$personal_image);

		if(is_file($id_front_image))
		{
			$u1=unlink($id_front_image);
		}
		if(is_file($house_image))
		{
			$u2=unlink($house_image);
		}
		if(is_file($personal_image))
		{
			$u3=unlink($personal_image);
		}

		// $this->success($personal_image);

		if ($lrsj->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}


	}


	public function handbook()
	{
		
		return $this->fetch();
	}

// 导出逻辑
/*
	前台传入输出
	2018  0  0
	input_year   sel_street_office  user_type);
	
	后台 逻辑判断
	1、admin与民政的 账户即mz_type=0或1的  2018  0  0 可以导出全部信息
			2018  x  x  则按条件导出
	2、.1 办事处账户 mz_type=2   2018  0  0  可以导出本办事处的信息 即为 2018  x  0,
			
		  .2  2018  0  x 则按条件导出本办事处信息  即为 2018 x  x
	
	3、 .1 居委会mz_type=3  2018  0  0 但是还要精确到账号id  即为 2018  x  0  id  可以导出本账号录入的信息
		   
		   .2  2018  0  x  则按条件导出本账号录入的信息 即为 2018 x x  id

*/


// http://localhost/minzheng/public/index.php/older/older/export_list
	public function export_list()
	{
		
		// 传入的数据
		$input_year=isset($_POST['input_year'])?$_POST['input_year']:null;// 录入年份
		// 测试数据
		// $input_year="2018";
		$sel_street_office_shiqu=isset($_POST['sel_street_office_shiqu'])?$_POST['sel_street_office_shiqu']:null;//选择的市区
		// echo $sel_street_office_shiqu;
		// exit;
		$sel_street_office=isset($_POST['sel_street_office'])?$_POST['sel_street_office']:null;//选择的办事处
		$sel_user_type=isset($_POST['sel_user_type'])?$_POST['sel_user_type']:null;//选择的新老用户类型

		// session 数据
  //   	$mz_type=$_SESSION['mz_type'];// 登录账号类型
  //   	$admin_id=$_SESSION['ADMIN_ID'];// 登录账号id
  //   	$mz_street_office=$_SESSION['mz_street_office'];// 登录账号所属办事处id
		// $sion_name=$_SESSION['name'];//登录名称

		$mz_type=session('mz_type');
		$admin_id=session('ADMIN_ID');
		$mz_street_office=session('mz_street_office');
		$sion_name=session('name');
		$shiqu=session('shiqu');



		$lrsj=Db::name('lrsj');
		$street_o=Db::name('street_office');
		$street_office=Db::name('street_office');

		$where=array();
		$title_info="  12349老年人居家养老服务入网汇总表";
		// 判断 新老用户 
    	if($sel_user_type!=0)
		{
			$where['user_type']=$sel_user_type;
			if($sel_user_type==1)
			{
				$title_info="新用户".$title_info;
			}else
			{
				$title_info="老用户".$title_info;
			}

		}else
		{
			$title_info="全部用户".$title_info;
		}

		$where['input_year']=$input_year;

		// 先预定义 shiqu下拉 与 街道下拉
		$r_name=null;
		$so_name=null;
		// admin与民政
    	if($mz_type==0)
    	{
    		// admin 既要判断 shiqu下拉 也要判断 街道下拉
    		if($sel_street_office_shiqu!=0)//shiqu下拉
    		{
    			$where['shiqu']=$sel_street_office_shiqu;
    			$r_info=$street_office->where(array('id'=>$sel_street_office_shiqu))->find();
				$r_name=$r_info['name'];
    			// $titleName=$sion_name."统计,".$r_name;
    		}
    		
    		if($sel_street_office!=0)//街道下拉
    		{
    			$where['street_office_id']=$sel_street_office;
    			$so_info=$street_o->where(array('id'=>$sel_street_office))->find();
				$so_name=$so_info['name'];//办事处名称
    			// $titleName=$sion_name."统计,".$r_name." ".$so_name;
    		}
    		$titleName=$sion_name."统计,".$r_name." ".$so_name.$title_info;
    	}else if($mz_type==1) // shiqu 级别  
    	{

			// 没有shiqu下拉  所以只判断 街道 下拉是否有值
			if($sel_street_office!=0)
    		{
    			$where['street_office_id']=$sel_street_office;
				// 文件名  表头名
    			$so_info=$street_o->where(array('id'=>$sel_street_office))->find();
				$so_name=$so_info['name'];//办事处名称
    			$titleName=$sion_name."统计,".$so_name.$title_info;
    		}else 
    		{
    			// 文件名  表头名
				$titleName=$sion_name."统计各个街道".$title_info;
    		}
    		

    	}else if($mz_type==2)
    	{
    		// 办事处 只能用本办事处的 不考虑 传输过来的
			$where['street_office_id']=$mz_street_office;
			// 文件名  表头名
			$titleName=$sion_name."统计,".$title_info;
    		

    	}else if($mz_type==3)
    	{
    		// 居委会 只考虑自己录入的，不考虑办事处
    		$where['input_person_id']=$admin_id;
    		// 文件名  表头名
    		$so_info=$street_o->where(array('id'=>$mz_street_office))->find();
			$so_name=$so_info['name'];//办事处名称
    		$titleName=$so_name.",".$sion_name.",统计".$title_info;

    	}

    	// print_r(json_encode($where));

		$typev=isset($_POST['typev'])?$_POST['typev']:'';
		// 测试数据
		// $typev=1;
		if ($typev==0){
            $count =$lrsj->where($where)->count();
            // $array = array();
            // if ($count > self::EXPORT_SIZE){
            //     //显示下载链接
            //     $page = ceil($count/self::EXPORT_SIZE);
            //     for ($i=1;$i<=$page;$i++){
            //         $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
            //         $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
            //         $array[$i] = $limit1.' ~ '.$limit2 ;
            //     }
            // }
            // $limit = false;
            // echo json_encode($array);
            echo json_encode($count);
            exit;
        }else{
            //下载
            $limit1 = ($typev-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }

		

		// $lists=$lrsj->where($where)->order("creat_time DESC")->limit($limit)->select();
		$lists=$lrsj->where($where)->order("creat_time asc")->limit($limit)->select();
		$data = array();
		foreach ($lists as $val) {
			$param = array();
			$param['id']=$val['id'];
			$param['name']=$val['name'];
			$param['idcard']=$val['idcard'];
			$param['sex']=$val['sex']==1?"男":"女";
			$param['age']=$val['age'];
			$param['real_address']=$val['real_address'];
			$param['huji_address']=$val['huji_address'];
			$param['phone']=$val['phone'];
			$param['user_type']=$val['user_type']==1?"新用户":"老用户";
			$param['income']=$val['income'];
			$param['economic_source']=$val['economic_source'];
			$param['illness']=$val['illness'];
			$param['self_ability']=$val['self_ability'];
			$param['child_name']=$val['child_name'];
			$param['child_sex']=$val['child_sex']==1?"男":"女";
			$param['child_relation']=$val['child_relation'];
			$param['child_phone']=$val['child_phone'];
			$param['child_address']=$val['child_address'];
			$param['creat_time']=date('Y-m-d H:i:s',$val['creat_time']);
			$param['shiqu_name']=$val['shiqu_name'];
			$param['street_office']=$val['street_office'];
			$param['input_person']=$val['input_person'];
			$param['input_year']=$val['input_year'];
			$param['state']=$val['state']==1?"正常":"作废";
			$data[$val['id']] = $param;

		}

		$title = array(
                'id' => '老人编号ID',
                'name' => '老人名称',
                'idcard' => '身份证号码',
                'sex' => '性别',
                'age' => '年龄',
                'real_address' => '真实居住地址',
                'huji_address' => '户籍地址',
                'phone' => '联系电话',
                'user_type' => '用户类型',
                'income' => '月收入',
                'economic_source' => '经济来源 ',
                'illness' => '现病史',
                'self_ability' => '自理能力',
                'child_name' => '子女姓名',
                'child_sex' => '子女性别 ',
                'child_relation' => '与老人关系',
                'child_phone' => '子女手机号',
                'child_address' => '子女地址',
                'creat_time' => '添加/修改时间',
                'shiqu_name' => '所在市区',
                'street_office' => '街道办事处',
                'input_person' => '录入人',
                'input_year' => '录入年份',
                'state' => '状态'
        );

		// print_r(json_encode($lists));

		// 文件名=登录用户名
		// $fileName=$sion_name;
		// 暂定 文件名=表头名
		


		$titleName=$titleName."第".$typev."页";
		$fileName=$titleName;
		
        // echo json_encode($fileName);

        $r=array();
        $r['url']=$this->exportExcel($title,$data,$fileName,'./down/',false,$titleName);
        $r['size']=sizeof($data);
        $r['limit']=$limit;
        echo json_encode($r);
        
		


	}


// 文件下载地址
// http://localhost/minzheng/down/yuyi666.xlsx
	public function exportExcel($title=array(),$data=array(), $fileName='', $savePath='./down/', $isDown=false,$titleName){  
		// $title=array(),$data=array(), $fileName='', $savePath='./', $isDown=true;
		    //引入核心文件 路径不同于老版本

			// 不引用好像也能调用
		    $ven=vendor("phpoffice.PHPExcel.Classes.PHPExcel");
			$obj = new \PHPExcel();  
			// if($obj)
			// {
			// 	echo  $ven."_".$obj->getYuyi();

			// }else
			// {
			// 	echo "22";
			// }
			// exit;
		  
		    // set_time_limit(0); 

		    //横向单元格标识  
		    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');  
		      
		    $obj->getActiveSheet(0)->setTitle('sheet名称');   //设置sheet名称  
		    $_row = 1;   //设置纵向单元格标识  
		    if($title){  
		        $_cnt = count($title);  
		        $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row);   //合并单元格  
		        $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, $titleName.date('Y-m-d H:i:s'));  //设置合并后的单元格内容  
		        $_row++;  
		        $i = 0;  
		        foreach($title AS $v){   //设置列标题  
		            $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);  
		            $i++;  
		        }  
		        $_row++;  
		    }  
		  
		    //填写数据  
		    if($data){  
		        $i = 0;  
		        foreach($data AS $_v){  
		            $j = 0;  
		            foreach($_v AS $_cell){  
		                $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i+$_row), $_cell);  
		                $j++;  
		            }  
		            $i++;  
		        }  
		    }  
		      
		    //文件名处理  
		    if(!$fileName){  
		        $fileName = uniqid(time(),true);  
		    }  
		  
		  	
		    $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');  
		  	

			// php 直接访问网络下载文件 
			// 而在js中不能直接用 
		    // if($isDown){   //网页下载  
		    //     header('pragma:public');  
		    //     header("Content-Disposition:attachment;filename=$fileName.xls");  
		    //     $objWrite->save('php://output');exit;  
		    // }  

		  
		    $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码  
		    $_savePath = $savePath.$_fileName.'.xlsx';  

		    $objWrite->save($_savePath);  
		  
		    return $savePath.$fileName.'.xlsx';  
		    // return $fileName.'.xlsx';  
		    
		}  






}


