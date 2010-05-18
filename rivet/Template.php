<?php
	
	require_once('Twig/lib/Twig/Autoloader.php');
	Twig_Autoloader::register();
	
	// Fix this... eww...
	require_once('TwigExtensions/rivet-url/twig-url.php');
	
	class Template {
		protected static $_instance;
		private function __construct(){}
		private function __clone(){}
		private $twig = NULL;
		private $body = '';
		
		private static function init(){
		    self::$_instance = new self();
		    $loader = new Twig_Loader_Filesystem(BASE_PATH.'/templates');
		    self::$_instance->twig = new Twig_Environment($loader, array(
		    	'debug' => TRUE,
		    	'cache' => BASE_PATH.'/rivet/cache'
		    ));
		    self::$_instance->twig->addExtension(new URL_Extension());
		}
		
		public static function getInstance(){
		    return self::$_instance;
		}
		
		public static function render($path, array $args=array()){
		    if( self::$_instance === NULL )
		        self::init();
		    
		    $template = self::$_instance->twig->loadTemplate($path);
		    $args['request'] = Request::getInstance();
		    $args['static_url'] = Rivet::getInstance()->config['static_url'];
		    self::$_instance->body = $template->render($args);
		    return self::$_instance->body;
		}
		
		function __toString(){
			return $this->body;
		}
	}