<?php

	abstract class Model{
		protected $id = NULL;
	
	    // Force Extending class to define this method
	    //abstract protected function getValue();
		
		public function __construct($db, $field_values=array()){
			if( count($field_values) ){
				foreach ($field_values as $key => $value){
					$this->$key = new SafeString($value);
				}
			}
		}
		
		function __toString(){
			return (string)$this->id;
		}
		
		public function order_by(){
			return $this->id;
		}
		
	    public function fields() {
	    	$fields = array();
	        foreach($this as $field => $value)
	            if( $field != 'id' )
	        	    array_push($fields, $field);
            return $fields;
	    }
	    
	    public function data(){
	        $data = array();        
	        foreach(get_object_vars($this) as $name => $field)
	            if( $name != 'id' )
	        	    array_push($data, $field);
	        return $data;
	    }
	}