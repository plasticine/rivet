<?php
	    
    class Form implements arrayaccess {
    	private $fields;
    	private $valid = NULL;
    	
    	function __construct(array $fields){
    		$this->fields = array_combine(
    		    array_map(function($f){ return $f->name(); }, $fields),
    		    array_values($fields)
    		);
    		return $this;
    	}
    	
    	function __invoke(array $post){
    		foreach ($post as $name => $data)
    			$this->fields[$name]->setValue($data);
    		
    		return $this;
    	}
    	
    	public function __toString(){
    	    $renderedForm = '';
    		foreach ($this->fields as $field)
    		    $renderedForm .= $field->render();
    		return $renderedForm;
    	}
    	
    	public function is_valid(){
    		foreach ($this->fields as $field){
    		    $validate = $field->validate();
    			if( $validate == false ){
    			    $this->valid = false;
    			}else{
    			    $field->clean();
    			}
    		}
    		if( $this->valid === false )
    		    return false;
    		
    		return true;
    	}
    	
    	public function fields() {
    		return $this->fields;
    	}
    	
    	public function field($name) {
    		return $this->fields[$name];
    	}
    	
    	public function offsetSet($offset, $value) {
    		$this->fields[$offset] = $value;
    	}
    	public function offsetExists($offset) {
    		return isset($this->fields[$offset]);
    	}
    	public function offsetUnset($offset) {}
    	public function offsetGet($offset) {
    	    $field = $this->fields[$offset];
    	    return $field->value();
    	}
    }
    
    class Field {
    	protected $value = '';
    	protected $cleaned = false;
    	protected static $cleanvalue = NULL;
    	public $valid;
    	public $errors;
    	
    	function __construct($name, array $args=array(), array $attrs=array()){
    		// todo: add system setting to allow/deny HTML input types
    		// todo: add validation of field choices here... make sure field choice is sane
    		$this->name = $name;
    		$this->required = (array_key_exists('required', $args)) ? $args['required'] : false;
    		$this->id = (array_key_exists('id', $args)) ? $args['id'] : $this->name;
    		$this->attrs = $attrs;
    	}
    	
    	public function __toString(){
    		return $this->render();
    	}
    	
    	public function setValue($value){
    		$this->value = $value;
    	}
    	
    	public function getValue(){
    		return $this->value;
    	}
    	
    	public function value(){
    		if( ! $this->cleaned )
    			$this->clean();
    		return $this->cleanvalue;
    	}
    	
    	public function getAttrs(){
    	    $attrs = '';
    	    if( count($this->attrs) )
    	        foreach ($this->attrs as $key => $value)
    	            $attrs .= ' '.$key.'="'.$value.'" ';
    	    return $attrs;
    	}
    	
    	public function validate(){
    	    if( $this->required && ! $this->value ){
    	        $this->valid = false;
    	        $this->errors['required'] = 'This field is required!';
    	    }
    	    if( $this->valid === false )
    	        return false;
    	    $this->valid = true;
    		return true;
    	}
    	
    	public function name(){
    		return $this->name;
    	}
    	
    	public function clean(){
    		$this->cleanvalue = new SafeString($this->getValue());
    		$this->cleaned = true;
    	}
    	
    }
    
    
    /* Text Field
    ///////////////////////////// */
    class TextField extends Field{
        private $renderString = '<input type="text" name="%s" id="%s" value="%s" %s%s>';
        
        function __construct($name, array $args=array(), array $attrs=array()){
            parent::__construct($name, $args, $attrs);
        }
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                stripslashes($this->getValue()),
                $this->getAttrs(),
                (Rivet::getInstance()->config['xhtml_tags']) ? '/' : ''
            );
        }
        
        public function clean(){
        	$this->cleanvalue = new SafeString($this->getValue());
        	$this->cleaned = true;
        }
    }
    
    
    class TextBoxField extends TextField{
        private $renderString = '<textarea name="%s" id="%s" %s>%s</textarea>';
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                $this->getAttrs(),
                stripslashes($this->getValue())
            );
        }
    }
    
    
    /* Choice Field
    ///////////////////////////// */
    class ChoiceField extends TextField{
        private $renderString = '<select type="select" name="%s" id="%s" %s>%s</select>';
        private $choices;
        
        function __construct($name, array $choices, array $args=array(), array $attrs=array()){
            parent::__construct($name, $args, $attrs);
            $this->choices = $choices;
        }
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                $this->getAttrs(),
                $this->renderChoices()
            );
        }
        
        public function renderChoices(){
            $choices = sprintf('<option value="">%s</option>', '-------------------------');
            foreach ($this->choices as $value => $display)
                $choices .= sprintf('<option value="%s"%s>%s</option>',
                    $value,
                    ($value == $this->getValue()) ? 'selected="selected"' : '',
                    $display
                );
            return $choices;
        }
        
        public function validate(){
            parent::validate();
            if( $this->getValue() && ! array_key_exists($this->getValue(), $this->choices) ){
                $this->valid = false;
                $this->errors['invalid'] = 'That is not a valid choice!';
            }
            if( $this->valid === false )
                return false;
            $this->valid = true;
            return true;
        }
        
        public function clean(){
            $this->cleanvalue = new SafeString(filter_var($this->getValue(), FILTER_SANITIZE_URL));
        	$this->cleaned = true;
        }
    }
    
    
    /* Number Field
    ///////////////////////////// */
    class NumberField extends Field{
        private $renderString = '<input type="number" name="%s" id="%s" value="%s" %s%s>';
    
        function __construct($name, array $args=array(), array $attrs=array()){
            if( array_key_exists('min', $args) )
                $attrs['min'] = $args['min'];
            if( array_key_exists('max', $args) )
                $attrs['max'] = $args['max'];
            parent::__construct($name, $args, $attrs);
        }
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                stripslashes($this->getValue()),
                $this->getAttrs(),
                (Rivet::getInstance()->config['xhtml_tags']) ? '/' : ''
            );
        }
        
        public function validate(){
            parent::validate();
            if( $this->getValue() && ! filter_var($this->getValue(), FILTER_VALIDATE_INT) ){
                $this->valid = false;
                $this->errors['invalid'] = 'That is not an integer!';
            }
            if( $this->getValue() && $this->attrs['min'] && $this->getValue() < $this->attrs['min'] ){
                $this->valid = false;
                $this->errors['min'] = 'The number is too small.';
            }
            if( $this->getValue() && $this->attrs['max'] && $this->getValue() > $this->attrs['max']){
                $this->valid = false;
                $this->errors['max'] = 'The number is too large.';
            }
            if( $this->valid === false )
                return false;
            $this->valid = true;
            return true;
        }
        
        public function clean(){
        	$this->cleanvalue = new SafeString(filter_var($this->getValue(), FILTER_SANITIZE_NUMBER_INT));
        	$this->cleaned = true;
        }
    }
    
    
    /* Float Field
    ///////////////////////////// */
    class FloatField extends Field{
        private $renderString = '<input type="float" name="%s" id="%s" value="%s" %s%s>';
        
        function __construct($name, array $args=array(), array $attrs=array()){
            if( array_key_exists('min', $args) )
                $attrs['min'] = $args['min'];
            if( array_key_exists('max', $args) )
                $attrs['max'] = $args['max'];
            parent::__construct($name, $args, $attrs);
        }
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                stripslashes($this->getValue()),
                $this->getAttrs(),
                (Rivet::getInstance()->config['xhtml_tags']) ? '/' : ''
            );
        }
        
        public function validate(){
            parent::validate();
            if( $this->getValue() && ! filter_var($this->getValue(), FILTER_VALIDATE_FLOAT) ){
                $this->valid = false;
                $this->errors['invalid'] = 'That is not a valid floating point number!';
            }
            if( $this->getValue() && $this->attrs['min'] && $this->getValue() < $this->attrs['min'] ){
                $this->valid = false;
                $this->errors['min'] = 'The number is too small.';
            }
            if( $this->getValue() && $this->attrs['max'] && $this->getValue() > $this->attrs['max']){
                $this->valid = false;
                $this->errors['max'] = 'The number is too large.';
            }
            if( $this->valid === false )
                return false;
            $this->valid = true;
            return true;
        }
        
        public function clean(){
            $this->cleanvalue = new SafeString(filter_var($this->getValue(), FILTER_SANITIZE_NUMBER_FLOAT));
        	$this->cleaned = true;
        }
    }
    
    
    /* File Field
    ///////////////////////////// */
    class FileField extends Field{
        private $renderString = '<input type="file" name="%s" id="%s" %s%s>';
    
        function __construct($name, array $args=array(), array $attrs=array()){
            parent::__construct($name, $args, $attrs);
        }
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                $this->getAttrs(),
                (Rivet::getInstance()->config['xhtml_tags']) ? '/' : ''
            );
        }
    }
    
    
    /* Checkbox Field
    ///////////////////////////// */
    class RadioField extends Field{
        private $renderString = '<input type="radio" name="%s" value="%s" %s%s>%s';
        private $choices;
        public $subFields = array();
        
        function __construct($name, array $choices, array $args=array(), array $attrs=array()){
            parent::__construct($name, $args, $attrs);
            $this->choices = $choices;
        }
        
        public function render(){
            $choices = '';
            foreach ($this->choices as $value => $display){
                $choice = sprintf($this->renderString,
                    $this->name,
                    $value,
                    ($value == $this->getValue()) ? 'checked="checked"' : '',
                    $this->getAttrs(),
                    $display
                );
                $choices .= $choice;
                $this->subFields[$display] = $choice;
            }
            return $choices;
        }
        
        public function validate(){
            parent::validate();
            if( $this->getValue() && ! filter_var($this->getValue(), FILTER_VALIDATE_BOOLEAN) ){
                $this->valid = false;
                $this->errors['invalid'] = 'That is not a valid choice';
            }
            if( $this->valid === false )
                return false;
            $this->valid = true;
            return true;
        }
    }
    
    
    /* URL Field
    ///////////////////////////// */
    class URLField extends Field{
        private $renderString = '<input type="url" name="%s" id="%s" value="%s" %s%s>';
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                $this->getValue(),
                $this->getAttrs(),
                (Rivet::getInstance()->config['xhtml_tags']) ? '/' : ''
            );
        }
        
        public function validate(){
            parent::validate();
            if( $this->getValue() && ! filter_var($this->getValue(), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ){
                $this->valid = false;
                $this->errors['invalid'] = 'That is not a valid URL!';
            }
            if( $this->valid === false )
                return false;
            $this->valid = true;
            return true;
        }
        
        public function clean(){
            $this->cleanvalue = new SafeString(filter_var($this->getValue(), FILTER_SANITIZE_URL));
        	$this->cleaned = true;
        }
    }
    
    
    /* Email Field
    ///////////////////////////// */
    class EmailField extends Field{
        private $renderString = '<input type="email" name="%s" id="%s" value="%s" %s%s>';
        
        public function render(){
            return sprintf($this->renderString,
                $this->name,
                $this->id,
                stripslashes($this->getValue()),
                $this->getAttrs(),
                (Rivet::getInstance()->config['xhtml_tags']) ? '/' : ''
            );
        }
        
        public function validate(){
            parent::validate();
            if( ! filter_var($this->getValue(), FILTER_VALIDATE_EMAIL) ){
                $this->valid = false;
                $this->errors['invalid'] = 'That is not a valid email address!';
            }
            if( $this->valid === false )
                return false;
            $this->valid = true;
            return true;
        }
        
        public function clean(){
            $this->cleanvalue = new SafeString(filter_var($this->getValue(), FILTER_SANITIZE_EMAIL));
        	$this->cleaned = true;
        }
    }
    