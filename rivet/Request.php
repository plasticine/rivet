<?php

	final class Request implements arrayaccess {
		
		protected static $_instance;
		private function __construct(){}
		private function __clone(){}
		private $request = array();
		
		public static function create(){
			if( self::$_instance === NULL ) {
				self::$_instance = new self();
				self::$_instance->request = self::$_instance->clean();
			}
			return self::$_instance;
		}
		
		public static function getInstance(){
			return self::$_instance;
		}
		
		public function __invoke($key){
			return $this->offsetGet($key);
		}
		
		public function __toString() {
			return '<pre>'.print_r($this->request).'</pre>';
		}
		
		private function clean(){
			$httpRequest = array();
			$httpRequest['host'] = $_SERVER['HTTP_HOST'];
			$httpRequest['path'] = $_SERVER['REQUEST_URI'];
			$httpRequest['method'] = strtolower($_SERVER['REQUEST_METHOD']);
			$httpRequest['encoding'] = $_SERVER['REQUEST_METHOD'];
			$httpRequest['get'] = $_GET;
			$httpRequest['post'] = $_POST;
			$httpRequest['files'] = $_FILES;
			$httpRequest['cookies'] = $_COOKIES;
			$httpRequest['server'] = $_SERVER;
			
			if( count($_FILES) )
			    $httpRequest['post'] = array_merge($httpRequest['post'], $httpRequest['files']);
			
			return $httpRequest;
		}
		
		public function offsetSet($offset, $value) {
			$this->request[$offset] = $value;
		}
		public function offsetExists($offset) {
			return isset($this->request[$offset]);
		}
		public function offsetUnset($offset) {
			unset($this->request[$offset]);
		}
		public function offsetGet($offset) {
			return isset($this->request[$offset]) ? $this->request[$offset] : null;
		}
		
	}