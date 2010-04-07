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
			'Content-Type'	=>	'text/html', // TODO this should fetch the actual content-type from the config var...
		);
		
		function __construct($body='', $status='200', array $headers=array()) {
			$this->status = $status;
			$this->headers = array_merge($this->headers, $headers);
			$this->body = $body; // TODO wrap this up in a method to string-ify the return value safely...
			$this->setHeaders();
		}
		
		function __toString(){
			return (string)$this->body;
		}
		
		private function setHeaders() {
			// Set the status header
			header("HTTP/1.1 ".$this->status." ".$this::$status_codes[$this->status]);
			
			foreach ($this->headers as $header => $value) {
				header("$header: $value");
			}
			return TRUE;
		}
	}