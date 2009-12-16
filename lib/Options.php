<?php

    /**
    * Класс для хранения различных опций или параметров.
    * 
    * @todo Class_Options?
    */
    class Options extends Zend_Config {
        /**
        * //
        * 
        * @param  array $options
        * @return void
        */
        public function __construct(array $options = array()) {
            parent::__construct($options, true);
        }
        
        /**
        * //
        * 
        * @param  array $options
        * @return Options
        */
        public static function create(array $options = array()) {
            return new self($options);
        }
        
        /**
        * //
        * 
        * @param  mixed|array|Zend_Config|Options
        * @return void
        */
        public function apply($options) {
            if ( !($options instanceof Zend_Config) )
            {
                if (!is_array($options)) {
                    $options = array($options);
                }
                
                $options = new Options($options);
            }
            
            $this->merge($options);
        }
        
        /**
        * //
        * 
        * @param  string $option
        * @param  mixed  $default
        * @return
        */
        public function get($option, $default = null) {
            $result = parent::get($option, $default);
            
            if ($result instanceof Zend_Config) {
                $result = $result->toArray();
            }
            
            return $result;
        }
    }
    
?>
