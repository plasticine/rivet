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
	        
	        // trim start & end slashes from pattern
	        if( substr($url, 0, 1) == '/' )
	            $url = substr($url, 1);
	        if( substr($url, -1) == '/' )
	            $url = substr($url, 0, -1);
	        $url_sub_patterns = explode('/', $url);
	        
	        foreach ($this->routes as $route) {
	        	$matched_url = '/';
	        	$count = 0;
	        	$pattern = $route->pattern;
	            // trim start & end slashes from pattern
	            if( substr($pattern, 0, 1) == '/' )
	                $pattern = substr($pattern, 1);
	            if( substr($pattern, -1) == '/' )
	                $pattern = substr($pattern, 0, -1);
	            $sub_pattern = explode('/', $pattern);
	            
	            // Root page
	            if( $pattern == '' AND $url_sub_patterns[0] == '' )
	            	return $route;
	            
	            foreach ($sub_pattern as $sub) {
	            	$sub_match = false;
	                if( $url_sub_patterns[$count] == $sub ){
	                    $matched_url .= $sub;
	                    $sub_match = true;
	                }else{
	                    if( strstr($sub, '{') ){
	                        if( preg_match("%^{(?P<optional>\?)?(?P<type>int|float|slug)?:?(?P<name>[\w]+)}$%", $sub, $matches) ){
	                            $sub_match = false;
	                            $optional = ($matches['optional']) ? true : false;
	                            $type = ($matches['type']) ? $matches['type'] : false;
	                            $name = $matches['name'];
	
	                            if( $type ){
	                                switch ($type) {
	                                    case 'int':
	                                        if( preg_match("%^([\d]+)$%", $url_sub_patterns[$count]) ){
	                                            $matched_url .= '/'.$url_sub_patterns[$count];
	                                            $sub_match = true;
	                                        }
	                                        break;
	                                    case 'float':
	                                        if( preg_match("%^([\d\.]+)$%", $url_sub_patterns[$count]) ){
	                                            $matched_url .= '/'.$url_sub_patterns[$count];
	                                            $sub_match = true;
	                                        } 
	                                        break;
	                                    case 'slug':
	                                        if( preg_match("%^([\w-]+)$%", $url_sub_patterns[$count]) ){
	                                            $matched_url .= '/'.$url_sub_patterns[$count];
	                                            $sub_match = true;
	                                        }
	                                        break;
	                                }
	                            }else if( preg_match("%^([\w-]+)$%", $url_sub_patterns[$count]) ){
	                                $matched_url .= '/'.$url_sub_patterns[$count];
	                                $sub_match = true;
	                            }
	                            if( $sub_match )
	                            	$route->args[] = $url_sub_patterns[$count];
	                            
	                            if( $optional === false AND !$sub_match )
	                                break;
	                            if( $optional AND !$sub_match ){
	                                $sub_match = true;
	                                $route->args[] = false;
	                            }
	                        }
	                    }
	                }
	                if( ! $sub_match )
	                    break;
	                if( $sub_match AND $count+1 == count($sub_pattern) )
	               		return $route;
	                $count++;
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
                $param_iterator = 0;
                $pattern = $route->pattern;
                // trim start & end slashes from pattern
                if( substr($pattern, 0, 1) == '/' )
                    $pattern = substr($pattern, 1);
                if( substr($pattern, -1) == '/' )
                    $pattern = substr($pattern, 0, -1);
                $sub_pattern = explode('/', $pattern);
                
                foreach ($sub_pattern as $sub) {
                	if( $sub != '' ){
                		if( preg_match("%^{(?P<optional>\?)?(?P<type>int|float|slug)?:?(?P<name>[\w]+)}$%", $sub, $matches) ){
                		    $sub_match = false;
                		    $current_param = $routeArgs[$param_iterator];
                		    $optional = ($matches['optional']) ? true : false;
                		    $type = ($matches['type']) ? $matches['type'] : false;
                		    $name = $matches['name'];
                		    
                			if( $type ){
                			    switch ($type) {
                			        case 'int':
                			            if( preg_match("%^([\d]+)$%", $current_param) )
                			            	$url .= "$current_param/";
                			            break;
                			        case 'float':
                			            if( preg_match("%^([\d\.]+)$%", $current_param) )
                			            	$url .= "$current_param/";
                			            break;
                			        case 'slug':
                			            if( preg_match("%^([\w-]+)$%", $current_param) )
                			            	$url .= "$current_param/";
                			            break;
                			    }
                			}
                			$param_iterator++;
                		}else{
                			$url .= "$sub/";
                		}
                	}
                }
                return $url;
			}
		}
	}
	
	
	function reverse(){
		$routes = Routes::getInstance();
		return $routes->reverse(func_get_args());
	}
	