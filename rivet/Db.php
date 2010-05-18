<?php
	require_once('Model.php');
	
    final class DB{
    
        protected static $_instance;
        protected $modelPath;
        private function __construct(){}
        private function __clone(){}
        private $db = NULL;
        private $model = NULL;
        private $model_name = NULL;
        private $query = array();
        
        public static function getInstance($model_name){
        	if( self::$_instance === NULL ) {
        		self::$_instance = new self();
        		self::$_instance->model = $model;
        		
        		// get pointer to config
        		$config = Rivet::getInstance()->config;
        		
        		self::$_instance->db = new SQLite3($config['database']['db_name']);
        		self::$_instance->modelPath = $config['database']['models'];
        		self::$_instance->model_name = $model_name;
        		self::$_instance->model = self::$_instance->newModelObject($model_name);
        		
        		self::$_instance->query['from'] = strtolower(get_class(self::$_instance->model));
        		self::$_instance->query['select'] = '*';
        		self::$_instance->query['where'] = array();
        		
        	}
        	return self::$_instance;
        }
        
        public function newModelObject($name, $parameters = array()) {
            if ( ! class_exists($name) && ! $this->loadModel($name) )
            	die('Library File "'.$this->modelPath.'/'.$name.'.php" not found.');
            return new $name($this, $parameters);
        }
    
        public function loadModel($name) {
            $file = $this->modelPath.'/'.$name.'.php';
            if ( file_exists($file) ){
            	return include($file);
            }else{
            	return false;
            }  
        }
        
        public function get($id){
        	$query = "SELECT * FROM ".$this->db->escapeString($this->query['from'])." WHERE id='".$this->db->escapeString($id)."'";
        	return $this->db->querySingle($query, true);
        }
        
        public function select($fields){
        	if( is_array($fields) ){
        		$fields_sql = '';
        		foreach ($fields as $field){
        			if( in_array($field, $this->model->fields()) )
        				$fields_sql .= $field.', ';
        		}
        		$fields_sql .= "id"; // always fetch the id
        	}else{
        		$fields_sql = $fields;
        	}
        	$this->query['select'] = trim($fields_sql);
        	return $this;
        }
        
        public function _or(){
        	array_push($this->query['where'], 'OR'); 
        	return $this;
        }
        
        public function where($field, $condition){
        	array_push($this->query['where'], "$field = '$condition'");
        	return $this;
        }
        
        public function limit($start, $end=NULL){
        	if( $end === NULL )
        		$this->query['limit'] = "$start";
        	else
        		$this->query['limit'] = "$start, $end";
        	return $this;
        }
        
        public function order_by($field, $direction='ASC'){
        	if( in_array($field, $this->model->fields()) )
        		$this->query['order_by'] = "$field $direction";
        	return $this;
        }
        
        public function save(object $object){
        	
        }
        
        public function validate(){
        	
        }
        
        public function all(){
        	return $this->exec();
        }
        
        public function exec(){
        	$query = "SELECT " . $this->query['select'] . " FROM " . $this->query['from'];
        	
        	// Where
        	if( count($this->query['where']) ){
        		$counter = 0;
	        	foreach ($this->query['where'] as $where){
	        		if( ! $counter )
	        			$query .= ' WHERE ';
	        		if( $where == 'OR' ){
	        			$query .= ' OR ';
	        		}else{
	        			if( $counter AND $this->query['where'][$counter-1] != 'OR' )
	        				$query .= ' AND ';
	        			$query .= $where;
	        		}
	        		$counter++;
	        	}
        	}
        	
        	// Order by
        	if( array_key_exists('order_by', $this->query) ){
        		$query .= " ORDER BY ".$this->query['order_by'];
        	}
        	
        	// Limit
        	if( array_key_exists('limit', $this->query) ){
        		$query .= " LIMIT ".$this->query['limit'];
        	}
        	
        	// Clean out the query stuff
        	$this->query['select'] = '*';
        	$this->query['where'] = array();
        	
        	$result = $this->db->query($query);
        	$query_results = array();
        	while ($row = $result->fetchArray(SQLITE3_ASSOC))
        	    array_push($query_results, $this->newModelObject($this->model_name, $row));
        	
        	return $query_results;
        }
        
    }