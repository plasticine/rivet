<?php
		
	final class Routes {
		
		protected static $_instance;
		private function __construct(){}
		private function __clone(){}
		private $routes = array();
		
		public static function create(array $routes=NULL){
		    if( self::$_instance === NULL ) {
		        self::$_instance = new self();
		        self::$_instance->routes = $routes;
		    }
		    return self::$_instance;
		}
		
		public static function getInstance(){
		    return self::$_instance;
		}
		
		public function match()	{
			$url = $_SERVER['REQUEST_URI'];
			if( strpos($url, '?') )
				$url = strstr($url, '?', TRUE);
			
			foreach ($this->routes as $route){
				if ( preg_match($route->pattern, $url, $matches) ) {
					$route->args = array_slice($matches, 1);
					return $route;
				}
			}
			return FALSE;
		}
		
		public function getRoute($name) {
			$namedRoutes = array();
			foreach ($this->routes as $route) {
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
		
		public function reverse(){
		    $params = func_get_args();
		    $params = $params[0];
		    if( ! count($params) )
		        throw new Exception("Cannot get reverse with no arguments!");
		    
		    // handle different reverse inputs.
		    // you can do a URL reverse based on:
		    // - URL params only, as an array in the correct order
		    // - on route name only
		    // - on both route name & route params
		    $routeArgs = array();
		    $routesToReverse = array();
		    if( count($params) == 1 ){
		        if( is_array($params[0]) ){
		            $routeArgs = $params[0];
		            $routesToReverse = $this->routes;
		        }else if( is_string($params[0]) ){
		            $routesToReverse = array($this->getRoute($params[0]));
		        }
		    }else if( count($params) == 2 AND (is_string($params[0]) OR is_numeric($params[0])) AND is_array($params[1]) ){
		        $routeArgs = $params[1];
		        $routesToReverse = array($this->getRoute($params[0]));
		    }
            
            foreach ($routesToReverse as $route) {
                $url = '/';
                $pathSegments = explode('/', trim(substr($route->pattern, strpos($route->pattern, '/'), strrpos($route->pattern, '/')-1), '/'));
                foreach ($pathSegments as $pattern) {
                	if( $pattern != '' ){
                	    // Capture and regexes in the URL pattern and
                	    // attempt to match them to the $routeArgs
                		if( preg_match("%^\((.*)\)$%", $pattern) ){
                			$arg = array_shift($routeArgs);
                			if( preg_match("%^$pattern$%", $arg) ){
                				$segment = $arg.'/';
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
            }
            return $url;
		}
	}
	
	
	function reverse(){
		$routes = Routes::getInstance();
		return $routes->reverse(func_get_args());
	}
	