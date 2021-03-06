<?php
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
require_once './wechat.php';
class Wxpai
{
	private $token;
	private $data=array();
	private $reg_url = 'http://61.155.173.229:8080/myoa/oa/appRegister?username=%s&password=%s&idStr=%s';
	
    public function index()  {
    	$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
   		$rt = explode('index.php', $thisurl);
		$arg = isset($rt[1]) ? $rt[1] : '';
		if(!empty($arg)){
			$rt = explode('/',$arg);
			$arg = isset($rt[1]) ? $rt[1] : '';
			if(!empty($arg)){
				$this->token = trim($arg);
			}
		}
    	
    	if (!class_exists('SimpleXMLElement')){
			exit('SimpleXMLElement class not exist');
		}
		if (!function_exists('dom_import_simplexml')){
			exit('dom_import_simplexml function not exist');
		}
		if(!preg_match("/^[0-9a-zA-Z]{3,42}$/", $this->token)){
			exit('error token');
		}
		$weixin = new Wechat($this->token);
		$this->data = $weixin->request();
		if ($this->data) {
			list($content, $type) = $this->reply($this->data);
			$weixin->response($content, $type);
		}
    }
    
    private function reply($data){
        if (isset($data['Event'])) {
        	$event = strtolower($data['Event']);
        	switch ($event) {
        		case 'subscribe': //关注后
        			return array('感谢您的关注，如需注册，请输入以#R#开头，包括用户名、密码、推荐人编号三项并且用英文逗号进行分割，如#R#test123,123456,my20150606！', 'text');exit;
        			break;
        		case 'unsubscribe'://取消关注
        			break;
        		case 'location'://自动获取位置回复
        			break;
        		case 'scan':
        			break;
        		case 'masssendjobfinish':
        			break;
        		case 'click':
        			break;
        		default:
        			break;
        	}
        }else if (isset($data['MsgType'])) {
        	$msgtype = strtolower($data['MsgType']);
        	switch ($msgtype) {
        		case 'text':
        			$this->data['Content'] = $data['Content'];
        			return $this->to_register_user($this->data['Content']);
        			break;
        		default:
        			break;
        	}
        }
    }
    
    //#R#test123,123456,my2015155816
    private function to_register_user( $content = '' ){
    	if (!preg_match('/^#R#/', $content)) {
    		return array('输入的信息必须以"#R#"为前缀！', 'text');exit;
    	}else{
    		$content = preg_replace('/^#R#/', '', $content);
    		$content = str_replace('，', ',', $content);
    		$content = str_replace(array("\r\n", "\n", "\r"), '', $content);
    		$user = explode(',', $content);
    		if (count($user) != 3) {
    			return array('输入的信息必须是用户名，密码，推荐人编号三项且以逗号进行分割！', 'text');exit;
    		}
    	}
    	$username = trim($user[0]);
    	$pwd = trim($user[1]);
    	$idStr = trim($user[2]);
    	if (empty($username) ||empty($pwd) || empty($idStr) ) {
    		return array('输入的信息必须是用户名，密码，推荐人编号三项！', 'text');exit;
    	}
    	$url = sprintf($this->reg_url, $username, $pwd, $idStr);
  		$result = $this->http_request($url);
  		file_put_contents('/tmp/registeruser', print_r($result, 1), FILE_APPEND);
    	if ($result) {
    		$msg = '';
    		switch ($result){
    			case 'exist':
    				$msg = '账号已存在！';
    				break;
    			case 'fathernull':
    				$msg = '推荐人不存在！';
    				break;
    			case 'success':
    				$msg = '注册成功，请用手机下载“名饮店”软件进行安装，然后用注册账号及密码进行登录，即可领取千元优惠券！';
    				break;
    			default:
    				$msg = '您的注册未成功！';
    				break;
    		}
    		return array($msg, 'text');
    	}
    }
    
    function http_request($url, $param = array()) {
    	$curl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		if ($param) {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
		}
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
    }
}
$wechat = new Wxpai();
$wechat->index();
