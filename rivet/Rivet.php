<?php
	define('BASE_PATH', realpath('.'));
	
	require_once('Route.php');
	require_once('Response.php');
	require_once('Template.php');
	require_once('Http.php');
	
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
					if( $view instanceof Response )
						return $view; // allow returning a response straight from the view
					return new Response($view);
				}
			}
			return notfound();
		}
		
		public function route($url_pattern, Closure $view_callback, $name='')	{
			$route = new Route(&$this, $name, $url_pattern, $view_callback);
			array_push($this::$routes, $route); 
		}
		
		public function getRoute($name) {
			$namedRoutes = array();
			foreach ($this::$routes as $route) {
				if( $route->isNamed() ){
					$namedRoutes[$route->name] = $route;
				}
			}
			if($name){
				if( array_key_exists((string)$name, $namedRoutes) ){
					return $namedRoutes[$name];
				}
				throw new Exception("Named Route '$name' Does Not Exist");
			}
			return $namedRoutes;
		}
		
	}
	
	$rivet = new Rivet();