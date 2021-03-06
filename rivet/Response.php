<?php

	class Response {
		private static $status_codes = array(
	        '200' => 'OK',
	        '301' => 'Moved Permanently',
	        '302' => 'Found',
	        '303' => 'See Other',
	        '304' => 'Not Modified',
	        '400' => 'Bad Request',
	        '403' => 'Forbidden',
	        '404' => 'Not Found',
	        '405' => 'Method Not Allowed',
	        '410' => 'Gone',
	        '500' => 'Internal Server Error',
	    );
		
		private $status = NULL;
		private $body = '';
		private $headers = array(
			// 'Content-Type'	=>	'text/html', // TODO this should fetch the actual content-type from the config var...
		);
		
		function __construct($body='', $status='200', array $headers=array()) {
			$this->status = $status;
			$this->headers = array_merge($this->headers, $headers);
			$this->body = $body; // TODO wrap this up in a method to string-ify the return value safely...
		}
		
		function __toString(){
			if( $this->setHeaders() ){
				return (string)$this->body;
			}
		}
		
		private function setHeaders() {
			// Set the status header
			header("HTTP/1.1 ".$this->status." ".$this->status_codes[$this->status]);
			//header('Content-type: text/html; charset=UTF-8');
			
			foreach ($this->headers as $header => $value) {
				header("$header: $value");
			}
			return TRUE;
		}
	}
	
	
	function notfound() {
		return new Response(Template::render('404.html'), '404');
	}

	function redirect($url) {
		$redirect = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$url;
		if(substr($url, 0, 1) == '/')
		    $redirect = 'http://'.$_SERVER['HTTP_HOST'].$url;
		
		return new Response('', '301', array(
			'Location' => $redirect
		));
	}

	function error() {
		$body = new Template('500.html');
		return new Response($body, '500');
	}