<?php
    
    /**
    * @todo Доработать интерфейс и сам класс опций.
    */
    interface Options_Interface {
        /**
        * //
        * 
        * @param  array|Zend_Config|Options
        * @return void
        */
        public function apply($options);
        
        /**
        * //
        * 
        * @param  string $option
        * @param  mixed  $default
        * @return
        */
        public function get($option, $default = null);
    }
    
?>