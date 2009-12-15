<?php

    /* $Id: $ */
    
    class IO_Buffer {
        /**
        * @var string
        */
        protected $buffer;
        
        /**
        * @var int
        */
        private $offset = 0;
        
        protected $options = array(
            'enable_profiler' => false,
            'copy_on_write' => null
        );
        
        public static function create() {
            return new self();
        }
        
        public function setOption($name, $value) {
            $this->options[$name] = $value;
        }                      
        
        public function getOption($name) {
            if (!array_key_exists($name, $this->options)) {
                return null;
            }
            
            return $this->options[$name];
        }
        
        public function read($bytes = null) {
            $bytes = (null === $bytes ? strlen($this->buffer) : $bytes);
            $block = substr($this->buffer, $this->offset, $bytes);
            $this->offset += $bytes;
            
            return $block;
        }
        
        public function release($bytes = null) {
            $start = (null === $bytes ? $this->offset : $bytes);
            $this->buffer = substr($this->buffer, $start);
            
            return $start;
        }
        
        public function write($data) {
            if (($copy_buffer = $this->getOption('copy_on_write')) instanceof IO_Buffer) {
                $copy_buffer->write($data);
            }
            
            $data_len = strlen($data);
            
            $this->buffer .= $data;
            $this->offset += $data_len;
            
            return $data_len;
        }     
        
        public function rewind() {
            $this->offset = 0;
        }           
        
        public function length() {
            return strlen($this->buffer);
        }
        
        public function offset() {
            return $this->offset;
        }
        
        public function seek($offset) {
            /**
            * @todo Может каких-нибудь проверок сделать?
            */
            $this->offset = $offset;
        }
        
        public function __toString() {
            $raw_buffer = $this->buffer;
            $this->buffer = base64_encode($this->buffer);
            $string = print_r($this, true);
            $this->buffer = $raw_buffer;
            
            return $string;
        }
    }

?>
