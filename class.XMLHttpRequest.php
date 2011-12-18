<?php
/**
*	Class XMLHttpRequest-oye
*	XMLHttpRequest emulator using  curl.
*
*	@author Moises Lima <moises-l@hotmail.com>
*	@version 0.5 19:08 22/7/2007
*	@link http://www.myopera.com/moises-l   Comments & suggestions
*	@link http://files.myopera.com/moises-l/files/class.XMLHttpRequest.php    Available at
*	@copyright GPL © 2007, Moises Lima
*	@license http://creativecommons.org/licenses/by-nc-sa/3.0/ Released under a Creative Commons License
*/
class XMLHttpRequest{
	/**
	*	String version of data returned from server process.
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-responsetext
	*	@access public
	*	@var string
	*	@name $responseText
	*/
	var $responseText;
	/**
	*	DOM-compatible document object of data returned from server process.
	*	which can be examined and parsed using W3C DOM node tree methods and properties
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-responsexml
	*	@access public
	*	@var object
	*	@name $responseXML
	*/
	var $responseXML;
	/**
	*	The http status code returned by server as a number (e.g. 404 for "Not Found" or 200 for "OK").
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-status
	*	@access public
	*	@var number
	*	@name $status
	*/
	var $status;
	/**
	*	The http status code returned by server as a string (e.g. "Not Found" or "OK")
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-statustext
	*	@access public
	*	@var string
	*	@name $statusText
	*/
	var $statusText;
	/**
	*	The state of the object
	*	0 = uninitialized
	*	1 = loading
	*	2 = loaded
	*	3 = interactive
	*	4 = complete
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-readystate
	*	@access public
	*	@var number
	*	@name $readyState
	*/
	var $readyState;
	/**
	*	The error string
	*	@link http://www.w3.org/TR/XMLHttpRequest/#notcovered
	*	@access public
	*	@var string
	*	@name $error
	*/
	var $error;
	/**
	*	An event handler for an event that fires at every state change
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-onreadystatechange
	*	@access public
	*	@name $onreadystatechange
	*/
	var $onreadystatechange;
	/**
	*	An event handler for an event that fires at finished requisition
	*	@link  http://www.w3.org/TR/XMLHttpRequest/#notcovered
	*	@access public
	*	@name $onload
	*/
	var $onload;
	/**
	*	An event handler for an event that fires at errors
	*	@link  http://www.w3.org/TR/XMLHttpRequest/#notcovered
	*	@access public
	*	@name $onerror
	*/
	var $onerror; // http://www.w3.org/TR/XMLHttpRequest/#notcovered
	/**
	*	cURL handle
	*	@access private
	*	@name $curl
	*/
	var $curl;
	/**	
	*	responseHeaders process
	*	@access private
	*	@name $responseHeaders
	*/
	var $responseHeaders;
	/**
	*	cURL headers
	*	@access private
	*	@name $headers
	*/
	var $headers=array("Connection: Keep-Alive","Keep-Alive: 300");
	/**
	*	Curl info
	*	@access public
	*	@name $curl_version
	*	@var Array
	*/
	var $curl_version;
	/**
	*	TRUE to follow any "Location: " header that the server sends as part of the HTTP header.
	*	@access public
	*	@name $followLocation
	*	@var Bolean
	*/
	var $followLocation;
	
	/**
	*	Class constructor (compatibility with PHP 4).
	*/
    function XMLHttpRequest(){
		$this->open="function open() { [native code] }";
		$this->setRequestHeader="function setRequestHeader() { [native code] }";
		$this->getAllResponseHeaders="function getAllResponseHeaders() { [native code] }";
		$this->getResponseHeader="function getResponseHeader() { [native code] }";
		$this->send="function send() { [native code] }";
		$this->readyState = 0;
		$this->curl = curl_init();
		$this->curl_version = curl_version();
		$this->followLocation=false;
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			curl_setopt($this->curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		}else{
			curl_setopt($this->curl, CURLOPT_USERAGENT, "XMLHttpRequest/0.2");
		}
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, 1000);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 300);
	}
	/**
	*	@access private
	*/
	function __toString(){
		return "[object XMLHttpRequest]";
	}
	/** 
	*	Specifies the method, URL, and other optional attributes of a request.
	*	@access public 
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-open
	*	@param String $method HTTP Methods defined in section 5.1.1 of RFC 2616 http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
	*	@param String $url Specifies either the absolute or a relative URL of the data on the Web service.
	*	@param Bolean $async FakeSauro Erectus.
	*	@param String $user specifies the name of the user for HTTP authentication.
	*	@param String $password specifies the password of the user for HTTP authentication.
	*	@return void 
	*/ 
	function open($method, $url, $async=true, $user="", $password=""){
		$this->readyState = 1; 
		if(!empty($method) && !empty($url)){
			$method=strtoupper(trim($method));
			/*
			if(!ereg("^(GET|POST|HEAD|PUT|DELETE|OPTIONS)$",$method)){
				throw new Exception("Unknown HTTP request method [$method]");
			}
			*/
			if(isset($_SERVER['HTTP_REFERER']) && empty($this->url) ){
				curl_setopt($this->curl,  CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
			}elseif(isset($this->url)){
				curl_setopt($this->curl,  CURLOPT_REFERER, $this->url);
			}
			$this->url = $url;
			curl_setopt($this->curl, CURLOPT_URL, $this->url);
			if($method=="POST"){
				curl_setopt($this->curl, CURLOPT_POST, 1);
			}elseif($method=="GET"){
				curl_setopt($this->curl, CURLOPT_POST, 0);
			}else{
				curl_setopt($this->curl, CURLOPT_POST, 0);
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method); 
			}
		}
		if(ereg("^(https)",$url)){
			curl_setopt($this->curl,CURLOPT_SSL_VERIFYPEER,false);
		}
		if(!empty($user) && !empty($password)){
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($this->curl,CURLOPT_USERPWD,$user.":". $password);
		}
	}
	/** 
	*	Assigns a label/value pair to the header to be sent with a request.
	*	@access public 
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-setrequestheader
	*	@param String $label Specifies the header label.
	*	@param String $value Specifies the header value.
	*	@return void 
	*/ 
	function setRequestHeader($label, $value){
		$this->headers[] = "$label: $value";
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
	}
	/** 
	*	Returns complete set of headers (labels and values) as a string.
	*	@access public 
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-getallresponseheaders
	*	@return string Complete set of headers (labels and values) as a string 
	*/ 
	function getAllResponseHeaders(){
		return $this->responseHeaders;
	}
	/** 
	*	Returns the value of the specified http header.
	*	@access public 
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-getresponseheader.
	*	@param String $label
	*	@return String|null The string value of a single header label.
	*/ 
	function getResponseHeader($label){
		$value=array();
		preg_match_all('/(?s)'.$label.': (.*?)\s\n/i', $this->responseHeaders , $value);
		if(count($value ) > 0){
			return implode(', ' , $value[1]);
		}
		return null;
	}
	/** 
	*	Returns the value of the specified http header (alternative method)
	*	@access public 
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-getresponseheader.
	*	@param String $label
	*	@return String|null The string value of a single header label.
	*/
	function getResponseHeader2($label){
		$value=array();
		preg_match('/(?s)'.$label.': (.*?)\s\n/i', $this->responseHeaders , $value);
		if(count($value ) > 0){
			return $value[1];
		}
		return null;
	}
	/** 
	*	Transmits the request, optionally with postable string or DOM object data.
	*	@access public 
	*	@link http://www.w3.org/TR/XMLHttpRequest/#dfn-getresponseheader
	*	@param String $data
	*	@return void
	*/ 
	function send($data=null){
		$sT=array();
		if(isset($this->onreadystatechange))eval($this->onreadystatechange);
		if($data){
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		}
		$this->response= curl_exec($this->curl);
		$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		$this->responseHeaders  = substr($this->response, 0, $header_size - 4);
		if($this->followLocation){
			$location=array();
			while(preg_match('/Location:(.*?)\n/', $this->responseHeaders, $location)){
				curl_setopt($this->curl,  CURLOPT_REFERER, $this->url);
				$url = @parse_url(trim(array_pop($location)));
				if (!$url){
					break;
				}
				$last_url = parse_url(curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL));
				if (!isset($url['scheme']))$url['scheme'] = $last_url['scheme'];
				if (!isset($url['host']))$url['host'] = $last_url['host'];
				if (!isset($url['path']))$url['path'] = $last_url['path'];
				$this->url = $url['scheme'] . '://' . $url['host'] . $url['path'] . (isset($url['query'])?'?'.$url['query']:'');
				curl_setopt($this->curl, CURLOPT_POST, 0);
				//curl_setopt($this->curl, CURLOPT_POSTFIELDS,0);
				curl_setopt($this->curl, CURLOPT_URL, $this->url);
				$this->response= curl_exec($this->curl);
				$header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
				$this->responseHeaders  = substr($this->response, 0, $header_size - 4);
			}
		}
		$this->error = curl_error($this->curl);
		if ($this->error) {
			if(isset($this->onerror))eval($this->onerror);
		}
		$this->readyState = 2;
		if(isset($this->onreadystatechange))eval($this->onreadystatechange);
		$this->responseText = substr($this->response, $header_size);
		preg_match('/^HTTP\/\d\.\d\s+(\d{3}) (.*)\s\n/i', $this->responseHeaders , $sT);
		if(count($sT ) > 2){
			$this->responseHeaders = ereg_replace ($sT[0], "", $this->responseHeaders);
			$this->status = $sT[1];
			$this->statusText = $sT[2];
		}
		if(version_compare(PHP_VERSION , "5", ">=")){
			if (preg_match('/(application|text)\/[\w+\+]?xml/i', $this->getResponseHeader("Content-Type"))){ 
				libxml_use_internal_errors(true);
				$this->responseXML = new DOMDocument();
				$this->responseXML->loadXML($this->responseText);
				$errors = libxml_get_errors();
				if (!empty($errors)){
					$this->responseXML=null;
					$error=$errors[0];
					$this->error= trim($error->message) ." in <b>$this->url</b> on line <b>$error->line</b> column: $error->column <br />";
					if(isset($this->onerror))eval($this->onerror);
				}
				libxml_clear_errors();
			}
		}
		$this->readyState = 3;
		if(isset($this->onreadystatechange))eval($this->onreadystatechange);
		$this->headers=Array();
		$this->readyState = 4;
		if(isset($this->onreadystatechange))eval($this->onreadystatechange);
		if(isset($this->onload))eval($this->onload);
	}
	/** 
	*	Closes a cURL session and frees all resources.
	*	@name close
	*	@access public
	*	@return void
	*/ 
	function close(){
		curl_close($this->curl);
	}
}
?>