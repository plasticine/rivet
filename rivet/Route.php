<?php

	class Route {
		
		public $name;
		private $rivet;
		private $request;
		private $pattern;
		private $view;
		private $args;
		
		function __construct($rivet, $name, $pattern, $view_callback){
			$this->rivet = $rivet;
			$this->name = $name;
			$this->pattern = "%".$pattern."%";
			$this->view = $view_callback;
			$this->args = array();
		}
		
		public function __toString() {
			return "< Route: '$this->url' - '$this->name' >";
		}
		
		public function match($request_url)	{
			if ( preg_match($this->pattern, $request_url, $matches) ) {
				$this->args = array_slice($matches, 1);
				array_unshift($this->args, $this->request);
				return TRUE;
			}
			return FALSE;
		}
		
		public function run() {
			ob_start();
			array_unshift($this->args, $this->rivet);
			return call_user_func_array($this->view, $this->args);
			ob_end_flush();
		}
		
		public function isNamed() {
			if( $this->name ){
				return TRUE;
			}
		}
		
		public function reverse($args=array()){
			
			echo "<pre>";
			print_r($args);	
			echo "</pre>";
			
			
			$url = '/';
			$pathSegments = explode('/', trim(substr($this->pattern, strpos($this->pattern, '/'), strrpos($this->pattern, '/')-1), '/'));
			foreach ($pathSegments as $pattern) {
				if( $pattern != '' ){
					if( preg_match("%^\((.*)\)$%", $pattern) ){
						$arg = array_shift($args);
						if( preg_match("%^$pattern$%", $arg) ){
							$segment = $arg.'/';
						}else{
							throw new Exception("Supplied arg: '$arg' does not match '$pattern' in Route pattern: '$this->pattern'");
						}
					}else{
						$segment = $pattern.'/';
					}
					$url .= $segment;
				}
			}
			if( substr($url, -1) != '/' ){
				$url.'/';
			}
			return $url;
		}
		
		
		
	}
	