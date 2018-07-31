<?php

/**
yuyi
康泰接口对接

 */
namespace app\kangtai\controller;


use cmf\controller\HomeBaseController;
use think\Db;

class KangtaiController extends HomeBaseController {

	function _initialize() 
	{
		parent::_initialize();
		header('Content-type: text/html; charset=utf-8');
		session_start();


	}

		
/* 	登录

http://localhost/minzheng/public/index.php/kangtai/kangtai/login

*/ 
	public function login()
	{
		$xmldata_=strtr(base64_encode('<?xml version="1.0" encoding="UTF-8"?><request><role>3</role><loginid>FZ201606020801</loginid><pwd>020801</pwd></request>'),"+","*");
		$msg="000000000000000000000000000000001010020000000000000000000000000000000011".$xmldata_;
		$msg_data=array("msg"=>$msg);
		$url = 'https://data2.contec365.com/login.php'; //接收xml数据的文件
		/*
			返回信息 前42位 要验证返回状态  
			536e3105c4bf09daae6b88270a7132a81010000011 
			FZ201606020801 中福网 张智 2016-06-02 
			http://webserver.12349sc.com/dataEntry.aspx 1 1ksct5cd307dbh4nevi2cht71ic6q5k8 
			2018-07-06 14:40:00 
		*/
		$response=$this->get_data_curl($url,$msg_data);

		// $fp=fopen("yuyi.txt", "w");
		// fwrite($fp,$response);
		// fclose($fp);

		// print_r($response);
		// echo "<br><br><br>";

		$check_r=substr($response,34,6);
		if($check_r=='100000')
		{
			// 截取其中的xml数据,并转换成array
			$response_xml= substr($response, 42);
			$xmlarray=$this->xmlToArray($response_xml);
			print_r($xmlarray);
			$sid=$xmlarray['sid'];
			$_SESSION['sid']=$sid;
			echo "<br><br><br>";
		}else
		{
			echo "else"."<br>";
			print_r($response);
		}
		
	}


/*	获取病例信息

http://localhost/minzheng/public/index.php/kangtai/kangtai/getCaseInfo

<?xml version="1.0" encoding="utf-8"?><request><hospitalid>H61100554</hospitalid><caseids>1201700010011684084</caseids></request>
*/ 
	public function getCaseInfo()
	{
		$num=md5("FZ201606020801020801");
		$sid=$_SESSION['sid'];
		if($sid==null)
		{
			echo "请先登录获取 唯一会话号";
			exit;
		}
		$xmldata_=strtr(base64_encode('<?xml version="1.0" encoding="utf-8"?><request><hospitalid>H61100554</hospitalid><caseids>1201700010011684084</caseids></request>'),"+","*");
		$sign=$num."10"."2006".$sid."11".$xmldata_;
		$sign=md5($sign);
		$msg=$sign."10"."2006".$sid."11".$xmldata_;
		$msg_data=array("msg"=>$msg);
		$url = 'http://data2.contec365.com/openapi.php'; 
		$response=$this->get_data_curl($url,$msg_data);
		print_r($response);
		$response=str_replace('&lt;?xml version="1.0" encoding="gb2312"?&gt;','',$response);
		$response=str_replace('&lt;?xml version="1.0" encoding="GBK"?&gt;','',$response);
		$response=str_replace("&lt;/","</",$response);
		$response=str_replace("&lt;","<",$response);
		$response=str_replace("&gt;",">",$response);

		/*
			file_exists()：判断文件是否存在，返回布尔值
			filesize():判断一个文件大小，返回文件的字节数，为整型数字
			unlink():删除一个文件
		*/ 
		$fp=fopen("yuyi.txt", "w+");
		fwrite($fp,$response);
		fclose($fp);


		echo "<br><br><br>";
		// json_encode输出之后停止操作
		// echo json_encode($response);
		// echo "<br><br><br>";
		// exit;
		$check_r=substr($response,34,6);
		echo $check_r."<br>";
		if($check_r=='100000')
		{
			echo "<br><br><br>";
			// 截取其中的xml数据,并转换成array
			$response_xml= substr($response, 42);
			$xmlarray=$this->xmlToArray($response_xml);
			print_r($xmlarray);
			echo "<br><br><br>";
			// echo json_encode($xmlarray);
			// echo "<br><br><br>";
		}


	}

/*  获取医院列表

http://localhost/minzheng/public/index.php/kangtai/kangtai/getDoc_List

*/ 
	 public function getDoc_List(){

		// 密串 md5("FZ201606020801020801"): 0d916b00f69b6397d4a39c8741286604
		// 服务器将终端登录号+密码生成一个MD5码存在session里
		// 密串 = MD5(登录号+密码)
		$num=md5("FZ201606020801020801");
		$sid=$_SESSION['sid'];
		if($sid==null||empty($sid))
		{
			echo "请先登录获取 唯一会话号";
			exit;
		}

		// nextid 参数 可省略 第一个拉取的医院ID，不填默认从头开始拉取
		$xmldata_=strtr(base64_encode('<?xml version="1.0" encoding="UTF-8"?><request><nextid></nextid></request>'),"+","*");

		// 终端发送请求消息的时候将自己的登录号+密码计算出一个MD5值，
		// 然后将该MD5值+请求消息里签名后面的内容计算MD5码，此MD5码放在签名里
		// 签名 = MD5(密串+协议版本+命令代码+唯一会话号+消息体格式+消息体)
		$sign=$num."10"."2002".$sid."11".$xmldata_;
		$sign=md5($sign);
		// echo $sign."<br>";
		$msg=$sign."10"."2002".$sid."11".$xmldata_;
		// echo $msg."<br>";
		// 报错 100004 签名错误
		$msg_data=array("msg"=>$msg);
		// 报错 100001
		// $msg_data=$msg;
		$url = 'http://data2.contec365.com/openapi.php'; 
		$response=$this->get_data_curl($url,$msg_data);

		/*
			返回信息 
			c22679d7fb45ad1ebafe3dfd013f1f3c1010000011 
			3 3 1000454 473 H1303230276 烟台市财商投资有限公司 
			370602 2014-06-18 493 H1303230296 烟台市财商投资有限公司2 
			370601 2014-07-29 1000454 H61100554 御花园 370601 2016-06-02 

		*/ 
		$check_r=substr($response,34,6);
		if($check_r=='100000')
		{
			echo "<br>";
			// 截取其中的xml数据,并转换成array
			$response_xml= substr($response, 42);
			$xmlarray=$this->xmlToArray($response_xml);
			print_r($xmlarray);
			// echo "<br><br><br>";
			
			// json_encode($xmlarray);
			// print_r(json_encode($xmlarray));
			/*
			Array ( [total] => 3 [count] => 3 [nextid] => 1000454 
			[hospitals] => Array ( [hospital] => Array ( 
				[0] => Array ( [id] => 473 [hospitalid] => H1303230276 
				[name] => 烟台市财商投资有限公司 [area] => 370602 
				[regtime] => 2014-06-18 ) [1] => Array ( [id] => 493 [hospitalid] => H1303230296 
				[name] => 烟台市财商投资有限公司2 [area] => 370601 
				[regtime] => 2014-07-29 ) [2] => Array ( [id] => 1000454 [hospitalid] => H61100554 [name] => 御花园 [area] => 370601 [regtime] => 2016-06-02 ) ) ) )
			*/ 

		}else
		{
			print_r($response);
		}

	}


/* 未通知成功病例号获取接口

http://localhost/minzheng/public/index.php/kangtai/kangtai/getUnSuccessCaseId

*/ 
	public function getUnSuccessCaseId()
	{
		$num=md5("FZ201606020801020801");
		$sid=$_SESSION['sid'];
		echo $sid."<br>";
		if($sid==null)
		{
			echo "请先登录获取 唯一会话号";
			exit;
		}

		/*
			casetype 参数 病例类型 多个病例类型用半角逗号隔开 
			参照5.1casetype取值对应表

			hospitalid有三个
			H1303230276
			H1303230296
			H61100554
		*/
		// 20050112the hospitalId you passed is invalid! hospitalId=

		$xmldata_=strtr(base64_encode('<?xml version="1.0" encoding="utf-8"?><request><hospitalid>H1303230276</hospitalid></request>'),"+","*");
		$sign=$num."10"."2005".$sid."11".$xmldata_;
		$sign=md5($sign);
		$msg=$sign."10"."2005".$sid."11".$xmldata_;
		$msg_data=array("msg"=>$msg);
		// 10000112the msg you passed is invalid
		// $msg_data=$msg;
		$url = 'http://data2.contec365.com/openapi.php'; 
		$response=$this->get_data_curl($url,$msg_data);

		// 没有数据 只有一个42  位的验证信息
		print_r($response);

		$check_r=substr($response,34,6);
		if($check_r=='100000')
		{
			echo "<br>";
			// 截取其中的xml数据,并转换成array
			$response_xml= substr($response, 42);
			$xmlarray=$this->xmlToArray($response_xml);
			print_r($xmlarray);
			// echo "<br><br><br>";
		}
	}



	// 配置curl 
	function get_data_curl($url,$msg_data)
	{
		$ch = curl_init ($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		// // 当 strlen($data) > 1024 时，curl_exec函数将返回空字符串
		// // 解决：增加一个HTTP header
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $msg_data);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;

	}


	/*
	将XML转为array
	只解析了一层,二级目录没法解析
	*/
	function xmlToArray($xml)
	{
	    //禁止引用外部xml实体
	    libxml_disable_entity_loader(true);
	    // 先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组
	    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    return $values;
	}






}
