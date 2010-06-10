<?php
	
	require_once('Twig/lib/Twig/Autoloader.php');
	Twig_Autoloader::register();
	
	// todo: Fix this... eww...
	require_once('TwigExtensions/rivet-url/twig-url.php');
	
	class Template {
		protected static $_instance;
		private function __construct(){}
		private function __clone(){}
		private $twig = NULL;
		private $body = '';
		
		private static function init(){
		    self::$_instance = new self();
		    self::$_instance->twig = new Twig_Environment(new Twig_Loader_Filesystem(BASE_PATH.'/templates'), array(
		    	'debug' => TRUE,
		    	'base_template_class' => Base_Template,
		    	'cache' => BASE_PATH.'/rivet/cache'
		    ));
		    self::$_instance->twig->addExtension(new URL_Extension());
		    self::$_instance->twig->addExtension(new Twig_Extension_Escaper(true));
		}
		
		public static function getInstance(){
		    return self::$_instance;
		}
		
		public static function render($path, array $args=array()){
		    if( self::$_instance === NULL )
		        self::init();
		    
		    $args['request'] = Request::getInstance();
		    $args['static_url'] = Rivet::getInstance()->config['static_url'];
		    
		    $template = self::$_instance->twig->loadTemplate($path);
		    $template->routes = Routes::getInstance();
		    
		    self::$_instance->body = $template->render($args);
		    return self::$_instance->body;
		}
		
		function __toString(){
			return $this->body;
		}
	}
	
	
	abstract class Base_Template extends Twig_Resource implements Twig_TemplateInterface{
	
		private $data = array();
		
		public function __set($name, $value) {
	        $this->data[$name] = $value;
	    }
	
	    public function __get($name) {
	        if (array_key_exists($name, $this->data)) {
	            return $this->data[$name];
	        }
	        $trace = debug_backtrace();
	        trigger_error(
	            'Undefined property via __get(): ' . $name .
	            ' in ' . $trace[0]['file'] .
	            ' on line ' . $trace[0]['line'],
	            E_USER_NOTICE);
	        return null;
	    }
	    
	    public function render(array $context){
	        ob_start();
	        try{
		          $this->display($context);
	        }catch (Exception $e){
		          ob_end_clean();
		          throw $e;
	        }
	        return ob_get_clean();
		}
		
		abstract protected function getName();
	}
	
	