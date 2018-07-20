<?php

class Sdk {
	//应用ID
	public $appId;
	
	//私钥文件路径
	public $rsaPrivateKeyFilePath;

	//私钥值
	public $rsaPrivateKey;

	//网关
	public $gatewayUrl = "https://openapi.alipay.com/gateway.do";
	//返回数据格式
	public $format = "json";
	//api版本
	public $apiVersion = "1.0";

	// 表单提交字符集编码
	public $postCharset = "UTF-8";

	//使用文件读取文件格式，请只传递该值
	public $alipayPublicKey = null;

	//使用读取字符串格式，请只传递该值
	public $alipayrsaPublicKey;


	public $debugInfo = false;

	private $fileCharset = "UTF-8";

	private $RESPONSE_SUFFIX = "_response";

	private $ERROR_RESPONSE = "error_response";

	private $SIGN_NODE_NAME = "sign";


	//加密XML节点名称
	private $ENCRYPT_XML_NODE_NAME = "response_encrypted";

	private $needEncrypt = false;


	//签名类型
	public $signType = "RSA";


	//加密密钥和类型

	public $encryptKey;

	public $encryptType = "AES";

	protected $alipaySdkVersion = "alipay-sdk-php-20161101";


	public function md5Sign($params, $key) {
		$string = $this->getSignContent($params);
		$string = $string.$key;
		$sign 	= strtoupper(md5($string));
		return $sign;
	}

	public function getSignContent($params) {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v)) {

				// 转换成目标字符集
				$v = $this->characet($v, $this->postCharset);

				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . "$v";
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . "$v";
				}
				$i++;
			}
		}

		unset ($k, $v);
		return $stringToBeSigned;
	}


	//此方法对value做urlencode
	public function getSignContentUrlencode($params) {
		ksort($params);

		$stringToBeSigned = "";
		$i = 0;
		foreach ($params as $k => $v) {
			if (false === $this->checkEmpty($v)) {

				// 转换成目标字符集
				$v = $this->characet($v, $this->postCharset);

				if ($i == 0) {
					$stringToBeSigned .= "$k" . "=" . urlencode($v);
				} else {
					$stringToBeSigned .= "&" . "$k" . "=" . urlencode($v);
				}
				$i++;
			}
		}

		unset ($k, $v);
		return $stringToBeSigned;
	}

	public function curl($url,$body='',$method='POST',$headers=array()){
		$httpinfo=array();
		$ci=curl_init();
		/* Curl settings */
		curl_setopt($ci,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
		curl_setopt($ci,CURLOPT_CONNECTTIMEOUT,30);
		curl_setopt($ci,CURLOPT_TIMEOUT,30);
		curl_setopt($ci,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ci,CURLOPT_ENCODING,'');
		curl_setopt($ci,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ci,CURLOPT_HEADER,FALSE);
		curl_setopt($ci,CURLOPT_POST,TRUE);
		if(!empty($body)){
			curl_setopt($ci,CURLOPT_POSTFIELDS,$body);
		}
		curl_setopt($ci,CURLOPT_URL,$url);
		curl_setopt($ci,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ci,CURLINFO_HEADER_OUT,TRUE);
		$response=curl_exec($ci);
		$httpcode=curl_getinfo($ci,CURLINFO_HTTP_CODE);
		$httpinfo=array_merge($httpinfo,curl_getinfo($ci));
		curl_close($ci);
		return $response;
	}



	public function execute($request, $authToken = null, $appInfoAuthtoken = null) {

		$this->setupCharsets($request);

		//		//  如果两者编码不一致，会出现签名验签或者乱码
		if (strcasecmp($this->fileCharset, $this->postCharset)) {

			// writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
			throw new Exception("文件编码：[" . $this->fileCharset . "] 与表单提交编码：[" . $this->postCharset . "]两者不一致!");
		}

		$iv = null;

		if (!$this->checkEmpty($request->getApiVersion())) {
			$iv = $request->getApiVersion();
		} else {
			$iv = $this->apiVersion;
		}


		//组装系统参数
		$sysParams["app_id"] = $this->appId;
		$sysParams["version"] = $iv;
		$sysParams["format"] = $this->format;
		$sysParams["sign_type"] = $this->signType;
		$sysParams["method"] = $request->getApiMethodName();
		$sysParams["timestamp"] = date("Y-m-d H:i:s");
		$sysParams["auth_token"] = $authToken;
		$sysParams["alipay_sdk"] = $this->alipaySdkVersion;
		$sysParams["terminal_type"] = $request->getTerminalType();
		$sysParams["terminal_info"] = $request->getTerminalInfo();
		$sysParams["prod_code"] = $request->getProdCode();
		$sysParams["notify_url"] = $request->getNotifyUrl();
		$sysParams["charset"] = $this->postCharset;
		$sysParams["app_auth_token"] = $appInfoAuthtoken;


		//获取业务参数
		$apiParams = $request->getApiParas();

			if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			$sysParams["encrypt_type"] = $this->encryptType;

			if ($this->checkEmpty($apiParams['biz_content'])) {

				throw new Exception(" api request Fail! The reason : encrypt request is not supperted!");
			}

			if ($this->checkEmpty($this->encryptKey) || $this->checkEmpty($this->encryptType)) {

				throw new Exception(" encryptType and encryptKey must not null! ");
			}

			if ("AES" != $this->encryptType) {

				throw new Exception("加密类型只支持AES");
			}

			// 执行加密
			$enCryptContent = encrypt($apiParams['biz_content'], $this->encryptKey);
			$apiParams['biz_content'] = $enCryptContent;

		}


		//签名
		$sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams), $this->signType);


		//系统参数放入GET请求串
		$requestUrl = $this->gatewayUrl . "?";
		foreach ($sysParams as $sysParamKey => $sysParamValue) {
			$requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, $this->postCharset)) . "&";
		}
		$requestUrl = substr($requestUrl, 0, -1);


		//发起HTTP请求
		try {
			$resp = $this->curl($requestUrl, $apiParams);
		} catch (Exception $e) {

			$this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_ERROR_" . $e->getCode(), $e->getMessage());
			return false;
		}

		//解析AOP返回结果
		$respWellFormed = false;


		// 将返回结果转换本地文件编码
		$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);



		$signData = null;

		if ("json" == $this->format) {

			$respObject = json_decode($r);
			if (null !== $respObject) {
				$respWellFormed = true;
				$signData = $this->parserJSONSignData($request, $resp, $respObject);
			}
		} else if ("xml" == $this->format) {

			$respObject = @ simplexml_load_string($resp);
			if (false !== $respObject) {
				$respWellFormed = true;

				$signData = $this->parserXMLSignData($request, $resp);
			}
		}


		//返回的HTTP文本不是标准JSON或者XML，记下错误日志
		if (false === $respWellFormed) {
			$this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_RESPONSE_NOT_WELL_FORMED", $resp);
			return false;
		}

		// 验签
		$this->checkResponseSign($request, $signData, $resp, $respObject);

		// 解密
		if (method_exists($request,"getNeedEncrypt") &&$request->getNeedEncrypt()){

			if ("json" == $this->format) {


				$resp = $this->encryptJSONSignSource($request, $resp);

				// 将返回结果转换本地文件编码
				$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
				$respObject = json_decode($r);
			}else{

				$resp = $this->encryptXMLSignSource($request, $resp);

				$r = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);
				$respObject = @ simplexml_load_string($r);

			}
		}

		return $respObject;
	}

	/**
	 * 转换字符集编码
	 * @param $data
	 * @param $targetCharset
	 * @return string
	 */
	function characet($data, $targetCharset) {
		
		if (!empty($data)) {
			$fileType = $this->fileCharset;
			if (strcasecmp($fileType, $targetCharset) != 0) {
				$data = mb_convert_encoding($data, $targetCharset, $fileType);
			}
		}
		return $data;
	}


	/**
	 * 校验$value是否非空
	 *  if not set ,return true;
	 *    if is null , return true;
	 **/
	protected function checkEmpty($value) {
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (trim($value) === "")
			return true;

		return false;
	}

	


}