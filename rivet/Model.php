<?php

	abstract class Model{
		protected $id = NULL;
	
	    // Force Extending class to define this method
	    //abstract protected function getValue();
		
		public function __construct($db, $field_values=array()){
			if( count($field_values) ){
				foreach ($field_values as $key => $value){
					$this->$key = $value;
				}
			}
		}
		
		function __toString(){
			return (string)$this->id;
		}
		
	    public function fields() {
	    	$fields = array();
	        foreach($this as $field => $value) {
	        	array_push($fields, $field);
            }
            return $fields;
	    }
	    
	    private function save(){
	    	
	    }
	}