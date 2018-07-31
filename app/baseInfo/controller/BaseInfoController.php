<?php

/**
yuyi

 * 老人档案信息
 */
namespace app\baseInfo\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class BaseInfoController extends AdminBaseController {
	 /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 3000;
	
	function _initialize() 
	{
		parent::_initialize();
	}

	public function index()
	{

		$street_office_id=session('mz_street_office');
		$input_person_id=session('ADMIN_ID');
		$mz_type=session('mz_type');
		$shiqu=session('shiqu');// 市区
		// 分类展示暂时用不到
		$juweihui=session('juweihui');//居委会


		$where_ands=array();
		// 不要用like   直接用=
		$fields=array(
				'name'  => array("field"=>"name","operator"=>"like"),
				'id_card'  => array("field"=>"id_card","operator"=>"="),
				'input_year'  => array("field"=>"input_year","operator"=>"="),
		);


		// 表单提交 是post
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

		// 是否展示 市区下拉
		$show_street_shiqu=0;
		// 0 不显示
		$show_street=0;


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
			// 1 为shiqu
			if($mz_type==1)
			{
				array_push($where_ands, "shiqu = '$shiqu'");
			}else  // 0 为 admin
			{
				$show_street_shiqu=1;
			}
		}
		$_GET['show_street_shiqu']=$show_street_shiqu;
		$_GET['show_street']=$show_street;
		// 把shiqu id传给前台
		$this->assign("shiqu",$shiqu);

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
		$lr_baseinfo_model=DB::name("lr_baseinfo");
    	$count=$lr_baseinfo_model->where($where)->count();

    	$lists = $lr_baseinfo_model
    	->order("id DESC")
    	->where($where)
    	->paginate(10);
    	
    	// print_r(json_encode($where));
    	
    	$params=$this->request->param();

    	$this->assign('lists', $lists);
    	$this->assign('show_street', $show_street);
    	$this->assign('count', $count);
    	$this->assign("formget",$_GET);

    	//在 render 前，使用appends方法保持分页条件
		$lists->appends($params);
		// $lists->appends($_GET);
		$this->assign('page', $lists->render());//单独提取分页出来
		// 渲染模板输出
		return $this->fetch();
		

	}

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





	public function add()
	{


		$mz_street_office=session('mz_street_office');
		$session_name=session('name');
		$shiqu=session('shiqu');

		$street_office=DB::name('street_office');
		$so_name=$street_office->where(array('id'=>$mz_street_office))->field("id,name")->find();
		$this->assign("so_name",$so_name);
		$this->assign("session_name",$session_name);
		$this->assign("shiqu",$shiqu);
		return $this->fetch();
	}


	public function add_post(){

			$post=isset($_POST['post'])?$_POST['post']:"";
			$fam=isset($_POST['fam'])?$_POST['fam']:"";
			$lr_baseinfo=DB::name('lr_baseinfo');

			$id_card=$post['id_card'];
			// 验证:合法性，重复性，验证是否够60岁
			$reg = "/(^\d{18}$)|(^\d{17}(\d|X|x)$)/";
			$flag=preg_match($reg,$id_card);
			if(!$flag)
			{
				$this->error("身份证号码不合法！");
				exit;
			}
			$sfz_year=substr($id_card,6,4);
			$sfz_month=substr($id_card,10,2);
			$sfz_date=substr($id_card,12,2);
			$sfz_birth=$sfz_year.$sfz_month.$sfz_date;
			$today=date("Ymd");
			$cha_age=$today-$sfz_birth;
			if($cha_age<600000)
			{
				$this->error("身份证年龄未满70周岁！不予登记");
				exit;
			}
			$find_id_card=$lr_baseinfo->where(array('id_card'=>$id_card))->find();
			if($find_id_card)
			{
				$this->error("身份证号码已存在，请勿重复添加！");
				exit;
			}


			$mz_street_office=session('mz_street_office');
			$session_name=session('name');
			$input_person_id=session("ADMIN_ID");
			$shiqu=session('shiqu');
			$juweihui=session('juweihui');


			$street_office=Db::name('street_office');
			$so_name=$street_office->where(array('id'=>$mz_street_office))->field("id,name")->find();

			$street_office_name=$street_office->where(['id'=>$shiqu])->field("id,name")->find();


			// 基本信息数组
			$arr=array();
			$arr['street_office']=$so_name['name'];
			$arr['input_person']=$session_name;
			$arr['street_office_id']=$mz_street_office;
			$arr['input_person_id']=$input_person_id;

			$arr['shiqu']=$shiqu;
			$arr['shiqu_name']=$street_office_name['name'];

			$arr['name']=$post['name'];
			$arr['id_card']=$post['id_card'];
			$arr['sex']=$post['sex'];
			$arr['age']=$post['age'];
			$arr['birthday']=$post['birthday'];
			$arr['nation']=$post['nation'];
			$arr['political']=$post['political'];
			// 由于前台是radio 类型 没有默认或不选择 则不传键值对，后台会报错 post中找不到 education键
			// 而test类型不用判断
			$arr['education']=isset($post['education'])?$post['education']:null;
			$arr['marital']=isset($post['marital'])?$post['marital']:null;

			$arr['xianju_address']=$post['xianju_address'];
			$arr['xianju_address_num']=$post['xianju_address_num'];
			$arr['huji_address']=$post['huji_address'];
			$arr['xianju_address']=$post['xianju_address'];
			$arr['living_state']=isset($post['living_state'])?$post['living_state']:null;
			$arr['children']=isset($post['children'])?$post['children']:null;
			$arr['blood']=$post['blood'];
			$arr['fixed_tel']=$post['fixed_tel'];
			$arr['mobile']=$post['mobile'];
			$arr['neighbor_name']=$post['neighbor_name'];
			$arr['neighbor_tel']=$post['neighbor_tel'];
			$arr['e_contact']=$post['e_contact'];
			$arr['e_contact_tel']=$post['e_contact_tel'];
			$arr['living_ability']=isset($post['living_ability'])?$post['living_ability']:null;
			$arr['work_nature']=isset($post['work_nature'])?$post['work_nature']:null;
			$arr['health']=isset($post['health'])?$post['health']:null;
			$arr['disease']=$post['disease'];
			$arr['other_disease']=$post['other_disease'];
			$arr['living_case']=isset($post['living_case'])?$post['living_case']:null;
			$arr['service_object']=isset($post['service_object'])?$post['service_object']:null;
			$arr['medical_insurance']=isset($post['medical_insurance'])?$post['medical_insurance']:null;
			$arr['remarks']=$post['remarks'];


			$arr['creat_time']=time();
			$arr['edit_time']=$arr['creat_time'];
			$arr['input_year']=date('Y',$arr['creat_time']);

			// 新版本的插入用 insert  成功返回 1 
			// 老版本add成功返回 id
			// 新版本解决 用insertGetId() 方法 或 在写一行 Db::name('user')->getLastInsID();
			// $arr_info=$lr_baseinfo->insert($arr);
			$arr_info=$lr_baseinfo->insertGetId($arr);
			if($arr_info)
			{
				
					$ch=array();
					$ch['old_id']=$arr_info;
					$ch['old_name']=$arr['name'];
					$ch['f_name1']=$fam['f_name1'];
					$ch['f_sex1']=$fam['f_sex1'];
					$ch['f_phone1']=$fam['f_phone1'];
					$ch['f_rela1']=$fam['f_rela1'];
					$ch['f_work1']=$fam['f_work1'];
					$ch['f_remarks1']=$fam['f_remarks1'];
					$ch['f_name2']=$fam['f_name2'];
					$ch['f_sex2']=$fam['f_sex2'];
					$ch['f_phone2']=$fam['f_phone2'];
					$ch['f_rela2']=$fam['f_rela2'];
					$ch['f_work2']=$fam['f_work2'];
					$ch['f_remarks2']=$fam['f_remarks2'];
					$lr_baseinfo_family=Db::name('lr_baseinfo_family');
					$child_arr_info=$lr_baseinfo_family->insert($ch);
					if($child_arr_info)
					{
						$this->success("老人信息 添加成功！");
						exit;
					}else
					{
						$this->error("子女信息添加失败！");exit;
					}
				

				$this->success("老人基本信息 添加成功！");exit;
				
			}else
			{
				$this->error("老人基本信息添加失败！");exit;
			}

	}

	public function edit()
	{
		// array_search() 函数与 in_array() 一样，在数组中查找一个键值。
		// 如果找到了该值，匹配元素的键名会被返回。如果没找到，则返回 false。 

		$id  = $this->request->param('id', 0, 'intval');

		$lr_baseinfo=Db::name('lr_baseinfo');
		$info=$lr_baseinfo->where(array('id'=>$id))->find();
		$post=array();
		$post['street_office']=$info['street_office'];
		$post['input_person']=$info['input_person'];
		$post['shiqu_name']=$info['shiqu_name'];

		$post['id']=$info['id'];
		$post['name']=$info['name'];
		$post['id_card']=$info['id_card'];
		$post['sex']=$info['sex'];
		$post['age']=$info['age'];
		$post['birthday']=$info['birthday'];
		$post['nation']=$info['nation'];
		$post['political']=$info['political'];
		$post['education']=$info['education'];
		$post['marital']=$info['marital'];
		$post['xianju_address']=$info['xianju_address'];
		$post['xianju_address_num']=$info['xianju_address_num'];
		$post['huji_address']=$info['huji_address'];
		$post['living_state']=$info['living_state'];
		$post['children']=$info['children'];
		$post['blood']=$info['blood'];
		$post['fixed_tel']=$info['fixed_tel'];
		$post['mobile']=$info['mobile'];
		$post['neighbor_name']=$info['neighbor_name'];
		$post['neighbor_tel']=$info['neighbor_tel'];
		$post['e_contact']=$info['e_contact'];
		$post['e_contact_tel']=$info['e_contact_tel'];
		$post['living_ability']=$info['living_ability'];
		$post['work_nature']=$info['work_nature'];
		$post['health']=$info['health'];
		$post['disease']=$info['disease'];
		$post['other_disease']=$info['other_disease'];
		$post['living_case']=$info['living_case'];
		$post['service_object']=$info['service_object'];
		$post['medical_insurance']=$info['medical_insurance'];
		$post['remarks']=$info['remarks'];
		$post['creat_time']=date('Y-m-d H:i:s',$info['creat_time']);
		$post['edit_time']=date('Y-m-d H:i:s',$info['edit_time']);
		$post['input_year']=$info['input_year'];
		// print_r(json_encode($post));
		// exit();


		$fam=array();
		$lr_baseinfo_family=Db::name('lr_baseinfo_family');
		$info_family=$lr_baseinfo_family->where(array('old_id'=>$id))->find();
		if($info_family)
		{
			$fam['f_name1']=$info_family['f_name1'];
			$fam['f_sex1']=$info_family['f_sex1'];
			$fam['f_phone1']=$info_family['f_phone1'];
			$fam['f_rela1']=$info_family['f_rela1'];
			$fam['f_work1']=$info_family['f_work1'];
			$fam['f_remarks1']=$info_family['f_remarks1'];
			$fam['f_name2']=$info_family['f_name2'];
			$fam['f_sex2']=$info_family['f_sex2'];
			$fam['f_phone2']=$info_family['f_phone2'];
			$fam['f_rela2']=$info_family['f_rela2'];
			$fam['f_work2']=$info_family['f_work2'];
			$fam['f_remarks2']=$info_family['f_remarks2'];
			$this->assign("fam",$fam);
		}

		
		$this->assign("post",$post);
		
		return $this->fetch();


	}



	public function edit_post()
	{
		$post=$_POST['post'];
		$fam=$_POST['fam'];

		$a_p=array();

		// print_r(json_encode($post));
		// exit;

		$a_p['name']=$post['name'];
		$a_p['sex']=$post['sex'];
		$a_p['age']=$post['age'];
		$a_p['birthday']=$post['birthday'];
		$a_p['nation']=$post['nation'];
		$a_p['political']=$post['political'];

		// 由于前台是radio 类型 没有默认或不选择 则不传键值对，后台会报错 post中找不到 education键
		// 而test类型不用判断

		$a_p['education']=isset($post['education'])?$post['education']:null;
		$a_p['marital']=isset($post['marital'])?$post['marital']:null;
		$a_p['xianju_address']=$post['xianju_address'];
		$a_p['xianju_address_num']=$post['xianju_address_num'];
		$a_p['huji_address']=$post['huji_address'];
		$a_p['living_state']=isset($post['living_state'])?$post['living_state']:null;
		$a_p['children']=isset($post['children'])?$post['children']:null;
		$a_p['blood']=$post['blood'];
		$a_p['fixed_tel']=$post['fixed_tel'];
		$a_p['mobile']=$post['mobile'];
		$a_p['neighbor_name']=$post['neighbor_name'];
		$a_p['neighbor_tel']=$post['neighbor_tel'];
		$a_p['e_contact']=$post['e_contact'];
		$a_p['e_contact_tel']=$post['e_contact_tel'];
		$a_p['living_ability']=isset($post['living_ability'])?$post['living_ability']:null;
		$a_p['work_nature']=isset($post['work_nature'])?$post['work_nature']:null;
		$a_p['health']=isset($post['health'])?$post['health']:null;
		$a_p['disease']=isset($post['disease'])?$post['disease']:null;
		$a_p['other_disease']=$post['other_disease'];
		$a_p['living_case']=isset($post['living_case'])?$post['living_case']:null;
		$a_p['service_object']=isset($post['service_object'])?$post['service_object']:null;
		$a_p['medical_insurance']=isset($post['medical_insurance'])?$post['medical_insurance']:null;
		$a_p['remarks']=$post['remarks'];
		$a_p['edit_time']=time();
		// print_r(json_encode($a_p));
		// exit();

		$id = $post['id'];
		$lr_baseinfo=Db::name('lr_baseinfo');
		$lr_baseinfo_family=Db::name('lr_baseinfo_family');
		$result=$lr_baseinfo->where(array('id'=>$id))->update($a_p);
		// echo $result;
		if ($result!==false) {
			if($fam['f_name1']!=null||$fam['f_name2']!=null)
				{
					$ch=array();
					$ch['f_name1']=$fam['f_name1'];
					$ch['f_sex1']=$fam['f_sex1'];
					$ch['f_phone1']=$fam['f_phone1'];
					$ch['f_rela1']=$fam['f_rela1'];
					$ch['f_work1']=$fam['f_work1'];
					$ch['f_remarks1']=$fam['f_remarks1'];
					$ch['f_name2']=$fam['f_name2'];
					$ch['f_sex2']=$fam['f_sex2'];
					$ch['f_phone2']=$fam['f_phone2'];
					$ch['f_rela2']=$fam['f_rela2'];
					$ch['f_work2']=$fam['f_work2'];
					$ch['f_remarks2']=$fam['f_remarks2'];
					$child_arr_info=$lr_baseinfo_family->where(array('old_id'=>$id))->update($ch);
					
				}
			$this->success("老人信息 修改成功！");
		} else {
			$this->error("老人信息 修改失败！");
		}


	}

	public function show_details()
	{

		// $id = intval(I("get.id"));
		$id = $this->request->param("id",0,"intval");
		$lr_baseinfo=Db::name('lr_baseinfo');
		$info=$lr_baseinfo->where(array('id'=>$id))->find();
		$post=array();
		$post['street_office']=$info['street_office'];
		$post['input_person']=$info['input_person'];

		$post['shiqu_name']=$info['shiqu_name'];
		$post['id']=$info['id'];
		$post['name']=$info['name'];
		$post['id_card']=$info['id_card'];
		$post['sex']=$info['sex'];
		$post['age']=$info['age'];
		$post['birthday']=$info['birthday'];
		$post['nation']=$info['nation'];
		$post['political']=$info['political'];
		$post['education']=$info['education'];
		$post['marital']=$info['marital'];
		$post['xianju_address']=$info['xianju_address'];
		$post['xianju_address_num']=$info['xianju_address_num'];
		$post['huji_address']=$info['huji_address'];
		$post['living_state']=$info['living_state'];
		$post['children']=$info['children'];
		$post['blood']=$info['blood'];
		$post['fixed_tel']=$info['fixed_tel'];
		$post['mobile']=$info['mobile'];
		$post['neighbor_name']=$info['neighbor_name'];
		$post['neighbor_tel']=$info['neighbor_tel'];
		$post['e_contact']=$info['e_contact'];
		$post['e_contact_tel']=$info['e_contact_tel'];
		$post['living_ability']=$info['living_ability'];
		$post['work_nature']=$info['work_nature'];
		$post['health']=$info['health'];
		$post['disease']=$info['disease'];
		$post['other_disease']=$info['other_disease'];
		$post['living_case']=$info['living_case'];
		$post['service_object']=$info['service_object'];
		$post['medical_insurance']=$info['medical_insurance'];
		$post['remarks']=$info['remarks'];
		$post['creat_time']=date('Y-m-d H:i:s',$info['creat_time']);
		$post['edit_time']=date('Y-m-d H:i:s',$info['edit_time']);
		$post['input_year']=$info['input_year'];


		$fam=array();
		$lr_baseinfo_family=Db::name('lr_baseinfo_family');
		$info_family=$lr_baseinfo_family->where(array('old_id'=>$id))->find();
		if($info_family)
		{
			$fam['f_name1']=$info_family['f_name1'];
			$fam['f_sex1']=$info_family['f_sex1'];
			$fam['f_phone1']=$info_family['f_phone1'];
			$fam['f_rela1']=$info_family['f_rela1'];
			$fam['f_work1']=$info_family['f_work1'];
			$fam['f_remarks1']=$info_family['f_remarks1'];
			$fam['f_name2']=$info_family['f_name2'];
			$fam['f_sex2']=$info_family['f_sex2'];
			$fam['f_phone2']=$info_family['f_phone2'];
			$fam['f_rela2']=$info_family['f_rela2'];
			$fam['f_work2']=$info_family['f_work2'];
			$fam['f_remarks2']=$info_family['f_remarks2'];
			$this->assign("fam",$fam);
		}

		$this->assign("post",$post);

		return $this->fetch();

	}


	function delete(){

		$lr_baseinfo=Db::name("lr_baseinfo");
		$lr_baseinfo_family=Db::name("lr_baseinfo_family");
		$id = $this->request->param("id",0,"intval");
		$info=$lr_baseinfo->where(array('id'=>$id))->find();
		$f_info=$lr_baseinfo_family->where(array('old_id'=>$info['id']))->find();
	
		$del_f=$lr_baseinfo_family->where(array('old_id'=>$info['id']))->delete();
		$del=$lr_baseinfo->where(array('id'=>$id))->delete();
		if ($del!=false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}

	}


//  http://localhost/minzheng/public/index.php/baseInfo/Base_Info/export_list
	public function export_list()
	{
		
		// 传入的数据
		$input_year=isset($_POST['input_year'])?$_POST['input_year']:null;// 录入年份
		// $input_year=2018;
		$sel_street_office=isset($_POST['sel_street_office'])?$_POST['sel_street_office']:null;//选择的办事处
		$sel_street_office_shiqu=isset($_POST['sel_street_office_shiqu'])?$_POST['sel_street_office_shiqu']:null;//选择的市区
		// $sel_street_office=7;
		// $sel_street_office_shiqu=1;
		$mz_type=session('mz_type');// 登录账号类型
		$admin_id=session('ADMIN_ID');// 登录账号类型
		$mz_street_office=session('mz_street_office');// 登录账号类型
		$sion_name=session('name');// 登录账号类型
		$shiqu=session('shiqu');
		$lr_b=Db::name('lr_baseinfo');
		$lr_b_f=Db::name('lr_baseinfo_family');
		$street_o=Db::name('street_office');
		$where=array();
		$title_info="  12349 老年档案汇总表";
		$where['input_year']=$input_year;
		// 先预定义 shiqu下拉 与 街道下拉
		$r_name=null;
		$so_name=null;
		if($mz_type==0)
    	{
    		// admin 既要判断 shiqu下拉 也要判断 街道下拉
    		if($sel_street_office_shiqu!=0)//shiqu下拉
    		{
    			$where['shiqu']=$sel_street_office_shiqu;
    			$r_info=$street_o->where(array('id'=>$sel_street_office_shiqu))->find();
				$r_name=$r_info['name'];
    		}
    		if($sel_street_office!=0)//街道下拉
    		{
    			$where['street_office_id']=$sel_street_office;
    			$so_info=$street_o->where(array('id'=>$sel_street_office))->find();
				$so_name=$so_info['name'];//办事处名称
    		}
    		$titleName=$sion_name."统计,".$r_name." ".$so_name.$title_info;
    	}else if($mz_type==1)// shiqu 级别  
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

		$typev=isset($_POST['typev'])?$_POST['typev']:'';
		// $typev=1;
		if ($typev==0){
            $count =$lr_b->where($where)->count();
            echo json_encode($count);
            exit;
        }else{
            //下载
            $limit1 = ($typev-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }

		

		$lists=$lr_b->where($where)->order("creat_time asc")->limit($limit)->select();
		$data = array();
		foreach ($lists as $val) {
			$param = array();
			$param['id']=$val['id'];
			$param['name']=$val['name'];
			$param['id_card']=$val['id_card'];
			$param['sex']=$val['sex']==1?"男":"女";
			$param['age']=$val['age'];
			$param['birthday']=$val['birthday'];
			$param['nation']=$val['nation'];
			$param['political']=$val['political'];
			$param['education']=$val['education'];
			$param['marital']=$val['marital'];
			$param['xianju_address']=$val['xianju_address'];
			$param['xianju_address_num']=$val['xianju_address_num'];
			$param['huji_address']=$val['huji_address'];
			$param['living_state']=$val['living_state'];
			$param['children']=$val['children'];
			$param['blood']=$val['blood'];
			$param['fixed_tel']=$val['fixed_tel'];
			$param['mobile']=$val['mobile'];
			$param['neighbor_name']=$val['neighbor_name'];
			$param['neighbor_tel']=$val['neighbor_tel'];
			$param['e_contact']=$val['e_contact'];
			$param['e_contact_tel']=$val['e_contact_tel'];
			$param['living_ability']=$val['living_ability'];
			$param['work_nature']=$val['work_nature'];
			$param['health']=$val['health'];
			$param['disease']=$val['disease'];
			$param['other_disease']=$val['other_disease'];
			$param['living_case']=$val['living_case'];
			$param['service_object']=$val['service_object'];
			$param['medical_insurance']=$val['medical_insurance'];
			$param['remarks']=$val['remarks'];
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
                'id_card' => '身份证号码',
                'sex' => '性别',
                'age' => '年龄',
                'birthday' => '出生年月',
                'nation' => '民族',
                'political' => '政治面貌',
                'education' => '文化程度',
                'marital' => '婚姻状况',
                'xianju_address' => '现居地址',
                'xianju_address_num' => '年龄',
                'huji_address' => '户籍地址',
                'living_state' => '居住状况',
                'children' => '子女情况',
                'blood' => '血型',
                'fixed_tel' => '固定电话',
                'mobile' => '移动电话',
                'neighbor_name' => '邻居姓名',
                'neighbor_tel' => '邻居电话',
                'e_contact' => '紧急联系人',
                'e_contact_tel' => '紧急联系人电话',
                'living_ability' => '生活能力',
                'work_nature' => '离退休前单位工作性质',
                'health' => '健康状况',
                'disease' => '疾病',
                'other_disease' => '其他疾病',
                'living_case' => '生活照料情况',
                'service_object' => '服务对象类别',
                'medical_insurance' => '医疗保险',
                'remarks' => '备注',
                'creat_time' => '添加/修改时间',
                'shiqu_name' => '所在市区',
                'street_office' => '街道办事处',
                'input_person' => '录入人',
                'input_year' => '录入年份',
                'state' => '状态'
        );
		
		$titleName=$titleName."第".$typev."页";
		$fileName=$titleName;
		
        $r=array();
        $r['url']=$this->exportExcel($title,$data,$fileName,'./down/',false,$titleName);
        $r['size']=sizeof($data);
        $r['limit']=$limit;

        echo json_encode($r);


	}



// 文件下载地址
// http://localhost/minzheng/down/yuyi666.xlsx
	public function exportExcel($title=array(),$data=array(), $fileName='', $savePath='./down/', $isDown=false,$titleName){  
		    // vendor("phpexcel.PHPExcel.Classes.PHPExcel");


		    $ven=vendor("phpoffice.PHPExcel.Classes.PHPExcel");

			$obj = new \PHPExcel();  
		  
		    set_time_limit(0); 

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
		    
		  
		    $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码  
		    $_savePath = $savePath.$_fileName.'.xlsx';  
		    $objWrite->save($_savePath);  
		  
		    return $savePath.$fileName.'.xlsx';  
		    
		}  


	public function check_id_card()
	{
		// print_r("0");
		// 370611190301251513
		// 370602195712090001
		$id_card=$_POST['id_card'];
		$lr_baseinfo=Db::name('lr_baseinfo');
		$id_card_find=$lr_baseinfo->where(array('id_card'=>$id_card))->find();
		if($id_card_find)
		{
			print_r("1");
		}else
		{
			print_r("0");
		}

		
	}




}


