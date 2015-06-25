<?php
require_once './wechat.php';
class index
{
	private $token;
	private $data=array();
	private $reg_url = 'http://61.155.173.229:8080/myoa/oa/appRegister?username=s%&password=s%&idStr=s%';
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
		$this->token = $this->_get('token', "htmlspecialchars");
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
        	
        }else if (isset($data['MsgType'])) {
        	$msgtype = strtolower($data['MsgType']);
        	switch ($msgtype) {
        		case 'text':
        			$this->data['Content'] = $data['Content'];
        			$this->to_register_user($this->data['Content']);
        			break;
        		default:
        			break;
        	}
        }
    }
    
    private function to_register_user( $content = '' ){
    	if (1) {
    		$msg = '';
    		$status = 'success';
    		switch ($status){
    			case 'exist':
    				$msg = '账号已存在！';
    				break;
    			case 'fathernull':
    				$msg = '推荐人不存在！';
    				break;
    			case 'success':
    				$msg = '注册成功！';
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
		return json_decode($result);
    }
    
}