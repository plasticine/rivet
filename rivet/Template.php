<?php
	
	require_once('Twig/lib/Twig/Autoloader.php');
	Twig_Autoloader::register();
	
	class Template {
		
		private $template = NULL;
		private $path = NULL;
		private $args = array();
		private $body = '';
		
		function __construct($path, array $args=array()) {
			$loader = new Twig_Loader_Filesystem(BASE_PATH.'/templates');
			$twig = new Twig_Environment($loader, array(
				'debug' => TRUE, // TODO: set to settings value
				'cache' => BASE_PATH.'/rivet/cache'
			));
			$twig->addExtension(new URL_Extension());
			
			$this->path = $path;
			$this->args = $args;
			$this->template	= $twig->loadTemplate($this->path);
			$this->body = $this->template->render($this->args);
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
			
			global $rivet;
			
			$route = $rivet->getRoute($route_name);
			$this->url = $route->reverse($params);
		}

		public function compile($compiler) {
			$compiler
				->addDebugInfo($this)
				->write("echo '".$this->url."';")
				->raw("\n")
			;
		}
	}
	
	
	
	