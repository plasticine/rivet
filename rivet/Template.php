<?php
	
	require_once('Twig/lib/Twig/Autoloader.php');
	Twig_Autoloader::register();
	
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
	
	
	// ======================================================
	
	
	class URL_Extension extends Twig_Extension {
		public function getName() { 
			return 'url';
		}
		public function getTokenParsers() {
			return array(new URL_TokenParser());
		}
	}
	
	class URL_TokenParser extends Twig_TokenParser {
		public function parse(Twig_Token $token) {
			$lineno = $token->getLine();
		    $route_name = $this->parser->getStream()->next()->getValue();
			// allow for hyphenated route names
			if( $this->parser->getStream()->getCurrent()->getValue() == '-' ){
				$route_name .= $this->parser->getStream()->expect(Twig_Token::OPERATOR_TYPE)->getValue();
				$route_name .= $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
			}
			
			$params = array();
			$item = $this->parser->getStream()->next();
			while( $item->getType() != Twig_Token::BLOCK_END_TYPE ) {
				array_push($params, $item->getValue());
				$item = $this->parser->getStream()->next();
			}
		    return new URL_Node($route_name, $params, $lineno, $this->getTag());
		}

	  	public function getTag() {
	  		return 'url';
		}
	}
	
	class URL_Node extends Twig_Node {
		protected $url;
		
		public function __construct($route_name, $params, $lineno) {
			parent::__construct($lineno);
			$routes = Routes::getInstance();
			$this->url = $routes->reverse(array($route_name, $params));
		}

		public function compile($compiler) {
			$compiler
				->addDebugInfo($this)
				->write("echo '".$this->url."';")
				->raw("\n")
			;
		}
	}
	
	
	
