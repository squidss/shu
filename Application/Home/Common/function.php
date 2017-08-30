<?php
/**
 * 访问一个货物的详情数据接口
 * @param unknown $gid
 * @return mixed
 */
function sq_good_pics($gid) {
	$url = "http://2017.ycqpmall.com/app/index.php?i=1&c=entry&m=ewei_shopv2&do=mobile&r=goods.detail.get_detail&id=0";
	$url = str_replace('id=0', 'id=' . $gid, $url);
	
	return sq_curl_ajax($url);
}

/**
 * 访问获取所有货物的列表接口
 * @param unknown $page
 * @return mixed
 */
function sq_get_goods_list($page) {
	$url = 'http://2017.ycqpmall.com/app/index.php?i=1&c=entry&m=ewei_shopv2&do=mobile&r=goods.get_list&keywords=&isrecommand=&ishot=&isnew=&isdiscount=&issendfree=&istime=&cate=&order=&by=&merchid=&page=0&frommyshop=0&';
	$url = str_replace('page=0', 'page=' . $page, $url);
	
	return sq_curl_ajax($url);
}

/**
 * 发送请求
 * @param unknown $url
 * @return mixed
 */
function sq_curl_ajax($url) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	// curl_setopt ( $ch, CURLOPT_POSTFIELDS, $str );
	$return = curl_exec ( $ch );
	curl_close ( $ch );
	
	return $return;
}

/**
 * 组装可访问货物页面的url
 * @param unknown $gid
 */
function sq_good_url($gid) {
	$url = 'http://2017.ycqpmall.com/app/index.php?i=1&c=entry&m=ewei_shopv2&do=mobile&r=goods.detail&id=0';
	return str_replace('id=0', 'id=' . $gid, $url);
}

/**
 *功能：php完美实现下载远程图片保存到本地
 *参数：文件url,保存文件目录,保存文件名称，使用的下载方式
 *当保存文件名称为空时则使用远程文件原来的名称
 *@param string $url 文件url
 *@param string $save_dir 保存文件目录
 *@param string $filename 保存文件名称
 *@param int $type 使用的下载方式，默认0为ob，1为curl
 */
function get_image($url,$save_dir='',$filename='',$type=0){
	if(trim($url)==''){
		return array('file_name'=>'','save_path'=>'','error'=>1);
	}
	if(trim($save_dir)==''){
		$save_dir='./';
	}
	if(trim($filename)==''){//保存文件名
		/* 原来的判断
		 $ext=strrchr($url,'.');
		 if($ext!='.gif'&&$ext!='.jpg'&&$ext!='.png'){
			return array('file_name'=>'','save_path'=>'','error'=>3);
			}
			$filename=time().$ext;
		 */
		// 保存原来的文件名
		if(!preg_match('/\/([^\/]+\.[a-z]{3,4})$/i', $url, $matches)){
			return array('file_name'=>'','save_path'=>'','error'=>3);
		}
		$filename = strToLower($matches[1]);
	}
	if(0!==strrpos($save_dir,'/')){
		$save_dir.='/';
	}
	//创建保存目录
	if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
		return array('file_name'=>'','save_path'=>'','error'=>5);
	}
	//获取远程文件所采用的方法
	if($type){
		$ch=curl_init();
		$timeout=5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		$img=curl_exec($ch);
		curl_close($ch);
	}else{
		ob_start();
		readfile($url);
		$img=ob_get_contents();
		ob_end_clean();
	}

	//$size=strlen($img);
	//文件大小
	$fp2=@fopen($save_dir.$filename,'a');
	fwrite($fp2,$img);
	fclose($fp2);
	unset($img,$url);
	return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
}

/**
 * 将字符串转换为数组
 * @param	string	$data	字符串
 * @return	array	返回数组格式，如果，data为空，则返回空数组
 */
function string2array($data) {
	if(is_array($data)) return $data;
	if($data == '') return array();
	if(!strexists(strtolower($data), 'array')) return array();
	@ini_set('display_errors', 'on');
	@eval("\$array = $data;");
	@ini_set('display_errors', 'off');
	$array = isset($array)?$array:array();
	return is_array($array)?$array:array();
}
/**
 * 将数组转换为字符串
 * @param	array	$data		数组
 * @param	int 	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return	string	返回字符串，如果，data为空，则返回空
 */
function array2string($data, $isformdata = 1) {
	if($data == '') return '';
	if($isformdata) $data = new_stripslashes($data);
	return var_export($data, TRUE);
}

/**
 * 判断字符串存在(包含)
 * @param string $string
 * @param string $find
 * @return bool
 */
function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param array|string $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}