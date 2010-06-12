<?php
    
    class SafeString {
    	private static $value;
    	
    	function __construct($value){
    	    $this->value = utf8_encode($value);
    	}
    	
    	public function __toString() {
    	    return utf8_decode($this->value);
    	    return htmlentities(stripslashes(utf8_decode($this->value)), ENT_NOQUOTES, 'UTF-8');
    	}
    	
    	public function utf8(){
    	    return $this->value;
    	}
    }