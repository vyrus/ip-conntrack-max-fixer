<?php

    /* $Id: $ */
    
    /**
    * @todo Rename to IO_Stream_Buffered_Abstract?
    */
    abstract class IO_Stream_Buffered extends IO_Stream_Abstract {
        /**
        * @var IO_Buffer
        */
        protected $read_buffer;
        
        /**
        * @vaк IO_Buffer
        */
        protected $write_buffer;
        
        public static function create($type, $options = null) {
            return self::factory($type, $options, __CLASS__);
        }
        
        public function init() {
            $this->read_buffer  = IO_Buffer::create();
            $this->write_buffer = IO_Buffer::create();
        }
                   
        public function getReadBuffer() {
            return $this->read_buffer;
        }
        
        public function getWriteBuffer() {
            return $this->write_buffer;
        }
        
        /**
        * Считывает данные из потока в буфер чтения.
        * 
        * @param  int $length Размер в байтах блока данных, который надо прочитать из потока.
        * @return int Количество байт прочитанных из потока и записанных в буфер чтения.
        */
        public function read($length) {
            $data = parent::read($length);
            
            return $this->read_buffer->write($data);                
        }     
        
        /**
        * Записывает данные в поток из буфера записи.
        * 
        * @param  int $length Размер в байтах блока данных для записи в поток.
        * @return int Количество записанных байт.
        */
        public function write($length) {
            if ($this->write_buffer->length() <= 0) {
                return false;
            }
            
            $this->write_buffer->rewind();
            $data = $this->write_buffer->read($length);
            $written = parent::write($data);

            if ($written > 0) {
                $this->write_buffer->release($written);
            }
            
            return $written;
        }
    }

?>