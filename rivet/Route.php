<?php
    
    require_once('rivet/Template.php');
    
    class Route {
    	
    	public $name;
    	public $pattern;
    	public $args;
    	private $callback;
    	
    	function __construct($pattern, $name, $callback){
    		$this->pattern = $this->cleanPattern($pattern);
    		$this->name = $name;
    		$this->callback = $callback;
    		$this->args = array();
    	}
    	
    	public function __toString() {
    		return '< Route: "'.$this->name.'" >';
    	}
    	
    	private function cleanPattern($pattern){
    		/*
    		if( substr($pattern, -1) == '$' ){
    			$pattern = substr($pattern, 0, (strlen($pattern)-1));
    		}
    		if( substr($pattern, 0, 1) == '^' ){
    			$pattern = substr($pattern, 1, strlen($pattern));
    		}
    		
    		$pattern = '('.$pattern.')';
    		//$pattern = $pattern.'()';
    		*/
    		
    		//$pattern = '%^'.$pattern.'$%';
    		return $pattern;
    	}
    	
    	public function run() {
    		ob_start();
    		return call_user_func_array($this->callback, $this->args);
    		ob_end_flush();
    	}
    	
    	public function isNamed() {
    		if( $this->name ){
    			return TRUE;
    		}
    	}
    }