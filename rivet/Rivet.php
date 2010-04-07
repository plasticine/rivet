<?php
	define('BASE_PATH', realpath('.'));
	
	include('Route.php');
	include('Response.php');
	include('Template.php');
	include('Http.php');
	
	class Rivet	{
		
		public static $request = array();
		public static $routes = array();
		private static $config = array(
			// character encoding
			'charset' 		=>	'utf-8',
			'content_type'	=>	'text/html',
		);
		
		function __construct() {
			// new Request()
		}
		
		public function dispatch() {
			$url = $_SERVER['REQUEST_URI']; // TODO Use the global request object
			foreach ($this::$routes as $route) {
				if( $route->match($url) ){
					$view = $route->run();
					if( $view instanceof Response ){
						return $view; // allow returning a response straight from the view
					}
					return new Response($view);
				}
			}
			return notfound();
		}
		
		public function route($url_pattern, Closure $view_callback, $name='')	{
			$request &= $this->request;
			$route = new Route($request, $name, $url_pattern, $view_callback);
			array_push($this::$routes, $route); 
		}
		
	}
	
	$rivet = new Rivet();