<?php

/**
yuyi
品源接口对接

 */
namespace app\pinyuan\controller;


use cmf\controller\HomeBaseController;
use think\Db;


class PinyuanController extends HomeBaseController {

	function _initialize() 
	{
		parent::_initialize();
		header('Content-type: text/html; charset=utf-8');
		header('Access-Control-Allow-Origin:*');//注意！跨域要加这个头
	}


/*
	接受品源传过来的数据
 	http://localhost/minzheng/public/index.php/Pinyuan/pinyuan/getBmdInfo
*/

	public function getBmdInfo(){
		header('Content-type: text/html; charset=utf-8');
		header('Access-Control-Allow-Origin:*');//注意！跨域要加这个头


		$data = json_decode(file_get_contents('php://input'), true);
		$post=$data;
		if($post==null)
		{
			$post=$_POST;
		}
		// print_r($data);
		
		$post="svOtgxGuT8uvhi+YC/IBXt+XTMIKF1Gvd05ZYkmmPIR/3Ug9zfPs3/nO2EIeXoIS4JoJBIF5g+ibrXOyggws8Qisf8wvo6lrnxYtw7ZFixobC3e7JgmzLWn3zf0EnPn1zJtnGH9XxW5D9q6Rsib1Jdsvh5SWsUnRjRrqkLBAUUZtFCU8EZRGEoG1bz061VCJGycU998uNDlxvNTmSgBnZxeEQ88uNnszMMpEpl4Yq1aLheomEbmKQI9eNlgHr8Q8GCv6S7poUhiIp4gn2VEIM4wf1MkRwgZdJtlJv6rDoTaYoqt9iS/YRC1aWF6pQYlU5uSW341DNEWZFE+UnPMUqipxChYU9yTXLDg/e2uxa9v+ctVQW1+oLW146wxh7Ao/aEG6dLzs+9moabCY2MCyVodhM1cjCwkrYUG8hjfrooBMki+4qHi6b1OwUi1v/Wjw7PNQMg6uJvX7vVc4rRwpYvqaXM+m6r0D455bz8B/XgClaX8E0270sMYG5KJgMsljXkbNLoM4r2iJpopfaMG8eVFxowcHhVx4KiJXU1HX6tzg8HsQXRLMgOiru7bgSrrDldTDHu5zCbqLybF9S8FDql+e/OWR6249VAUeV3NUJjSiAJJ1SCGk/66am4RJ16i+pBpxzD6HKmq5PTUaKnlcISmwvT58Bv3lHstvjzc9xeJ5KIRcfvZCVGzs++k0RbcmtBNqiRSoBk7+nCmi5k+bGgNmYitnkSuDWADleJgDLcKTRA9g8VgAjCYCZd6DgnTaGmyiPcqdtqstfsHm39O0SVtbxM2XINoa+4HQzYwXKz2GBvYubO39srp8gQyZbCMuoKIcHgWs7lYorbQeDt4jPA==";
// $post='d9si6J92e7ZaXNaYm2twv/EdBkW5WeLXL8H/WhIAwKtQKsAQblVXhXN 9rC9ZtryPPvwZ1aN2CNNFdge5HKn2NTF7 LsAsPsaMFIz25RBlEZsFq3LLD1xigncY6M0Zq4f4Ecg7t0Bz5j1ovj8jw9z18T3P3fKxhFUqT1IrHCOBSxU1JGZr2x9 z8O6mUfCnwnCa6Q KXLNgYo4zXwMmv2jka8Je3Aa 9HF3Ik5T13XJ/couTUJQnkANUrpheo0AH9796pXRGDPC5UNdNpnyurftNNjbnjv orlsqQzWliqwicSBlUiV SFXD0tJ1nuFxaKkZlMwlRvvmhokHgVL4lVJpoaPcO0Owa2FkBmYDVv1tGJskaDprshhAEISafxEID0D2UwGTrbyZSe9tHcIzC f15HL2o/FEwsHqfjUdM3uX2ZT/KEghD gsntdqftvlJJAvKK3y/UcpEvTJd/ YqKDeSCSmKzR6Ng/4n yCnL16m0Chp4d 7vz CV4oIGLMSX3WaU1c7Iq9zwyOqQ2Wqti34MYZis5fkc4YFDl8Vw2UEZpcTqjdLMDbJzOKrd9hsoRfxBUMDyi99psUenTMEb1ENUOsXmLbFhz2AdgJ 6aDRK2yaHvEf06BkRvbT/gcEU SxfWb2YNnGOHJIs85cxDgT3U8U9/XBM7HsG/mSd0=';
$post=str_replace(" ","+",$post);
		// // 测试接受
		// $post=$_GET;
		// print_r($post);
		// exit;

		// $post=array('data'=>$post);

		if($post){
			// print_r($post);
			// echo "<br>";
			$fp=fopen(CMF_ROOT. "app/pinyuan/rsa/test.txt", "a+");
			$time=date('Y-m-d H:i:s',time());
			fwrite($fp,$time);
			fwrite($fp,"\r\n");
			// 是数组就转换成字符串
			if(is_array($post)){
				$post=implode(',',$post);
				fwrite($fp,"数组");
				// echo $post;

				// echo "is_array";
				// echo "<br>";

			}
			fwrite($fp,"post数据为：".$post);
			// fwrite($fp,"post数据为：".implode(',',$post));
			fwrite($fp,"\r\n");

		}else
		{
			$fp=fopen(CMF_ROOT. "app/pinyuan/rsa/test.txt", "a+");
			$time=date('Y-m-d H:i:s',time());
			fwrite($fp,$time);
			fwrite($fp,"\r\n");
			fwrite($fp,"未接收到post数据");
			fwrite($fp,"\r\n---------------------------------------------\r\n");
			fclose($fp);
			return $this->ret_form('300','未接收到post数据');
			exit;
		}
		
		// 公钥进行数据加密，服务端使用私钥进行数据解密
		$private_key = file_get_contents(CMF_ROOT . "app/pinyuan/rsa/rsa_private_key.pem");
		$public_key = file_get_contents(CMF_ROOT . "app/pinyuan/rsa/rsa_public_key.pem");
		$pi_key=openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id 
		$pu_key=openssl_pkey_get_public($public_key );//这个函数可用来判断公钥是否是可用的

		$encrypted=$post;

		$decrypted = ""; 
		$crypto = '';
		foreach (str_split(base64_decode($encrypted), 128) as $chunk) {
		    openssl_private_decrypt($chunk, $decrypted, $pi_key);
		    $crypto .= $decrypted;
		}
		$decrypted=$crypto;
		$decrypted_arr=json_decode($decrypted,true);

		// print_r($decrypted_arr);
		// exit;



		$CheckNum=$decrypted_arr['CheckNum'];
		$pinyuan=Db::name('pinyuan_bmd');
		$CheckNum_f=$pinyuan->where(array('CheckNum'=>$CheckNum))->find();
		// if(!$CheckNum_f)
		// {
			$decrypted_arr['add_time']=time();
			$arr_info=$pinyuan->insert($decrypted_arr);
			if($arr_info)
			{
				fwrite($fp,"返回:"."200");
				fwrite($fp,"\r\n---------------------------------------------\r\n");
				fclose($fp);
				return $this->ret_form('200','添加成功');
			}else
			{
				fwrite($fp,"返回:"."添加失败");
				fwrite($fp,"\r\n---------------------------------------------\r\n");
				fclose($fp);
				return $this->ret_form('400','添加失败');
			}

		// }else
		// {
		// 	fwrite($fp,"返回:"."已添加");
		// 	fwrite($fp,"\r\n---------------------------------------------\r\n");
		// 	fclose($fp);
		// 	return $this->ret_form('500','已添加');
		// }


	}


/*
	模拟发送curl传递的数据
 	http://localhost/minzheng/public/index.php/Pinyuan/pinyuan/post_curl
*/
	public function post_curl()
	{
		$url="http://localhost/minzheng/public/index.php/Pinyuan/pinyuan/getBmdInfo";
		// $url="http://12349.yanglao99.cn/index.php/Pinyuan/pinyuan/getBmdInfo";
		$msg_data=array('data'=>"svOtgxGuT8uvhi+YC/IBXt+XTMIKF1Gvd05ZYkmmPIR/3Ug9zfPs3/nO2EIeXoIS4JoJBIF5g+ibrXOyggws8Qisf8wvo6lrnxYtw7ZFixobC3e7JgmzLWn3zf0EnPn1zJtnGH9XxW5D9q6Rsib1Jdsvh5SWsUnRjRrqkLBAUUZtFCU8EZRGEoG1bz061VCJGycU998uNDlxvNTmSgBnZxeEQ88uNnszMMpEpl4Yq1aLheomEbmKQI9eNlgHr8Q8GCv6S7poUhiIp4gn2VEIM4wf1MkRwgZdJtlJv6rDoTaYoqt9iS/YRC1aWF6pQYlU5uSW341DNEWZFE+UnPMUqipxChYU9yTXLDg/e2uxa9v+ctVQW1+oLW146wxh7Ao/aEG6dLzs+9moabCY2MCyVodhM1cjCwkrYUG8hjfrooBMki+4qHi6b1OwUi1v/Wjw7PNQMg6uJvX7vVc4rRwpYvqaXM+m6r0D455bz8B/XgClaX8E0270sMYG5KJgMsljXkbNLoM4r2iJpopfaMG8eVFxowcHhVx4KiJXU1HX6tzg8HsQXRLMgOiru7bgSrrDldTDHu5zCbqLybF9S8FDql+e/OWR6249VAUeV3NUJjSiAJJ1SCGk/66am4RJ16i+pBpxzD6HKmq5PTUaKnlcISmwvT58Bv3lHstvjzc9xeJ5KIRcfvZCVGzs++k0RbcmtBNqiRSoBk7+nCmi5k+bGgNmYitnkSuDWADleJgDLcKTRA9g8VgAjCYCZd6DgnTaGmyiPcqdtqstfsHm39O0SVtbxM2XINoa+4HQzYwXKz2GBvYubO39srp8gQyZbCMuoKIcHgWs7lYorbQeDt4jPA==");

		echo sizeof($msg_data['data']);
		$msg_data=json_encode($msg_data);
		exit;

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
		// print_r($response);
		return $response;

	}

	public function ret_form($status,$msg)
	{
		$ret_form=array();
		$ret_form['status']=$status;
		$ret_form['msg']=$msg;
		return json_encode($ret_form);
	}







/*
	接受品源传过来的数据 原来测试接口
	http://localhost/minzheng/public/index.php/Pinyuan/pinyuan/getBmdInfo_ceshi

*/ 
	public function getBmdInfo_ceshi(){
		header('Content-type: text/html; charset=utf-8');
		header('Access-Control-Allow-Origin:*');//注意！跨域要加这个头
		// 模拟提交数据
		$str='{
		    "CheckNum":"201800601008",
		    "CheckTime":"2018-06-01 13:10:20",
		    "PatientName":"王一飞",
			"PatientGender":"男",
			"PatientBirthDate":"1985-09-01",
			"PatientAge":"33",
			"PatientRace":"中国",
			"PatientFatherHeight":"173",
			"PatientMotherHeight":"163",
			"CheckPosition":"左侧桡骨远端1/3处",
			"SOS":"3801",
			"TScore":"-1.2",
			"ZScore":"-3.1",
			"BMI":"22.3",
			"PAB":"35",
			"BQI":"6",
			"AdtPct":"98",
			"AgePct":"99",
			"EOA":"67",
			"Risk":"0.8",
			"Suggestion":"补充维生素"
		}';
		print_r($str);
		echo "<br>";
		
		// 当该参数为 TRUE 时，将返回 array 而非 object 。
		$yy_d=json_decode($str,true);
		print_r($yy_d);
		echo "<br>";
		print_r($yy_d['CheckNum']);
		echo "<br>";
		$yy_e=json_encode($yy_d);
		print_r($yy_e);
		echo "<br>";


		// 公钥进行数据加密，服务端使用私钥进行数据解密
		// CMF_ROOT . "app/pinyuan/rsa/rsa_private_key.pem"
		$private_key = file_get_contents(CMF_ROOT . "app/pinyuan/rsa/rsa_private_key.pem");
		$public_key = file_get_contents(CMF_ROOT . "app/pinyuan/rsa/rsa_public_key.pem");


		$pi_key=openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id 
		$pu_key=openssl_pkey_get_public($public_key );//这个函数可用来判断公钥是否是可用的

		// echo "<br>";
		// print_r($pi_key);echo "pi_key\n"; echo "<br>";
		// print_r($pu_key);echo "pu_key\n"; echo "<br>";
		echo "<hr>";
		// // 最大长度117
		// $data='sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss';
		// 小于117位
		$data='php ras加密算法';
		// 由数组转成的json格式
		$data=$yy_e;
		// 原来json格式
		$data=$str;
		$encrypted = ""; 
		$decrypted = ""; 
		echo "加密的源数据:".$data."\n"; echo "<br><br><br>";

		echo "public key encrypt:\n"; echo "<br>";
		$crypto = '';
		foreach (str_split($data, 117) as $chunk) {
		    openssl_public_encrypt($chunk,$encrypted,$pu_key);//公钥加密 
		    $crypto .= $encrypted;
		}
		$crypto = base64_encode($crypto);
		echo $crypto; 
		echo "<br><br><br>";
		 // $fp=fopen("test.txt", "w");
			// fwrite($fp,$crypto);
			// fclose($fp);

		echo "private key decrypt:\n"; echo "<br>";
		// 自己加密的 demo
		$encrypted=$crypto;
		// 张工给的公钥加密后的 demo
		// $encrypted="svOtgxGuT8uvhi+YC/IBXt+XTMIKF1Gvd05ZYkmmPIR/3Ug9zfPs3/nO2EIeXoIS4JoJBIF5g+ibrXOyggws8Qisf8wvo6lrnxYtw7ZFixobC3e7JgmzLWn3zf0EnPn1zJtnGH9XxW5D9q6Rsib1Jdsvh5SWsUnRjRrqkLBAUUZtFCU8EZRGEoG1bz061VCJGycU998uNDlxvNTmSgBnZxeEQ88uNnszMMpEpl4Yq1aLheomEbmKQI9eNlgHr8Q8GCv6S7poUhiIp4gn2VEIM4wf1MkRwgZdJtlJv6rDoTaYoqt9iS/YRC1aWF6pQYlU5uSW341DNEWZFE+UnPMUqipxChYU9yTXLDg/e2uxa9v+ctVQW1+oLW146wxh7Ao/aEG6dLzs+9moabCY2MCyVodhM1cjCwkrYUG8hjfrooBMki+4qHi6b1OwUi1v/Wjw7PNQMg6uJvX7vVc4rRwpYvqaXM+m6r0D455bz8B/XgClaX8E0270sMYG5KJgMsljXkbNLoM4r2iJpopfaMG8eVFxowcHhVx4KiJXU1HX6tzg8HsQXRLMgOiru7bgSrrDldTDHu5zCbqLybF9S8FDql+e/OWR6249VAUeV3NUJjSiAJJ1SCGk/66am4RJ16i+pBpxzD6HKmq5PTUaKnlcISmwvT58Bv3lHstvjzc9xeJ5KIRcfvZCVGzs++k0RbcmtBNqiRSoBk7+nCmi5k+bGgNmYitnkSuDWADleJgDLcKTRA9g8VgAjCYCZd6DgnTaGmyiPcqdtqstfsHm39O0SVtbxM2XINoa+4HQzYwXKz2GBvYubO39srp8gQyZbCMuoKIcHgWs7lYorbQeDt4jPA==";
		$crypto = '';
		foreach (str_split(base64_decode($encrypted), 128) as $chunk) {
		    openssl_private_decrypt($chunk, $decrypted, $pi_key);
		    $crypto .= $decrypted;
		}
		echo $crypto;
		$decrypted=$crypto;
		echo "<br>";
		// exit;

		// 当该参数为 TRUE 时，将返回 array 而非 object 。
		$decrypted_arr=json_decode($decrypted,true);
		$CheckNum=$decrypted_arr['CheckNum'];
		$pinyuan=Db::name('pinyuan_bmd');
		$CheckNum_f=$pinyuan->where(array('CheckNum'=>$CheckNum))->find();
		if(!$CheckNum_f)
		{
			$decrypted_arr['add_time']=time();
			$arr_info=$pinyuan->insert($decrypted_arr);
			if($arr_info)
			{
				return '200';
				// $this->success("添加成功！");
				exit;
			}else
			{
				// $this->error("添加失败！");
				return '添加失败';
			}

		}else
		{
			return '已添加';
		}


		/*私钥加密,公钥解密*/ 
		// echo "private key encrypt:\n"; echo "<br>";
		// openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密 
		// $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的 
		// echo '私钥加密后：'.$encrypted."\n"; echo "<br>";;
		// echo "public key decrypt:\n"; echo "<br>";
		// openssl_public_decrypt(base64_decode($encrypted),$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来 
		// echo '公钥解密后：'.$decrypted."\n"; echo "<br>";
		// echo "<hr><br><br>";



	}





}


