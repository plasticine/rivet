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
				'cache' => BASE_PATH.'/cache'
			));
			
			$this->path = $path;
			$this->args = $args;
			$this->template	= $twig->loadTemplate($this->path);
			$this->body = $this->template->render($this->args);
		}
		
		function __toString(){
			return $this->body;
		}
		
	}