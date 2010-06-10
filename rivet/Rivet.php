<?php

	if (!defined('BASE_PATH'))
		define('BASE_PATH', realpath('.'));
	
	require_once('Routes.php');
	require_once('Route.php');
	require_once('Response.php');
	require_once('Request.php');
	require_once('Http.php');
	require_once('Template.php');
	require_once('Db.php');
	require_once('Form.php');
	
	final class Rivet {
		protected static $_instance;
		private function __construct(){}
		private function __clone(){}
		public $config = array();
		
		public static function getInstance(array $config=array()){
		    if( self::$_instance === NULL ){
		    	self::$_instance = new self();
		    	self::$_instance->config = array(
		    		// character encoding
		    		'charset'		=>    'utf-8',
		    		'content_type'	=>    'text/html',
		    		'database'		=>    array(
		    			'models'	=>    BASE_PATH.'/models/',
		    			'engine'	=>    'sqlite3',
		    			'db_name'	=>    BASE_PATH.'/test.db'
		    		),
		    		'static_url'	=>    '/static',
		    		'xhtml_tags'    =>    false
		    	);
		    	Request::create();
		    }
		    date_default_timezone_set('Australia/ACT');
		    return self::$_instance;
		}
		
		public function __toString() {
			return (string)self::$_instance->response;
		}
		
		public function dispatch() {
			if( self::$_instance === NULL )
			    self::getInstance();
			
		    $routes = Routes::getInstance();
		    $route = $routes->match();
		    
			if( $route ){
				$view = $route->run();
				if( $view instanceof Response )
					self::$_instance->response = $view; // allow returning a response straight from the view
				self::$_instance->response = new Response($view);
				return self::$_instance->response;
			}
			return notfound();
		}
	}	