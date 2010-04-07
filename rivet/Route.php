<?php

	class Route {
		
		private $request;
		private $name;
		private $url;
		private $view;
		private $args;
		
		function __construct($request, $name, $url_pattern, $view_callback){
			$this->request = $request;
			$this->name = $name;
			$this->url = $url_pattern;
			$this->view = $view_callback;
			$this->args = array();
		}
		
		public function __toString() {
			return "< Route: '$this->url' - '$this->name' >";
		}
		
		public function match($request_url)	{
			if ( preg_match($this->url, $request_url, $matches) ) {
				$this->args = array_slice($matches, 1);
				array_unshift($this->args, $this->request);
				return TRUE;
			}
			return FALSE;
		}
		
		public function run() {
			ob_start();
			return call_user_func_array($this->view, $this->args);
			ob_end_flush();
		}
		
	}
	