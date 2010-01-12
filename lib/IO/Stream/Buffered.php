<?php

    /**
    * Класс потока ввода-вывода с буферами: чтения (в который считываются данные
    * из потока) и записи (из которого данные записываются в поток).
    */
    class IO_Stream_Buffered extends IO_Stream implements IO_Stream_Buffered_Interface {
        /**
        * Буфер чтения.
        * 
        * @var IO_Buffer_Interface
        */
        protected $_read_buffer;
        
        /**
        * Буфер записи.
        * 
        * @var IO_Buffer_Interface
        */
        protected $_write_buffer;
        
        /**
        * Создание нового объекта потока.
        * 
        * @param  array|Options $options Опции потока.
        * @return IO_Stream_Buffered
        */
        public static function create(
            IO_Stream_Buffered_Context_Interface $context
        ) {
            return new self($context);
        }
        
        /**
        * Инициализация буферов потока.
        * 
        * @return void
        */
        protected function _init() {
            $this->_read_buffer  = $this->_context->createBuffer();
            $this->_write_buffer = $this->_context->createBuffer();
        }
                   
        /**
        * Возвращает объект буфера чтения.
        * 
        * @return IO_Buffer_Interface
        */
        public function getReadBuffer() {
            return $this->_read_buffer;
        }
        
        /**
        * Возвращает объект буфера записи.
        * 
        * @return IO_Buffer_Interface
        */
        public function getWriteBuffer() {
            return $this->_write_buffer;
        }
        
        /**
        * Считывает данные из потока в буфер чтения.
        * 
        * @param  int $length Размер в байтах блока данных, который надо прочитать из потока.
        * @return int Количество байт, прочитанных из потока и записанных в буфер чтения.
        */
        public function read($length) {
            /* Считываем блок из потока */
            $data = parent::read($length);
            /* И записываем его в буфер */
            return $this->_read_buffer->write($data);                
        }     
        
        /**
        * Записывает данные в поток из буфера записи.
        * 
        * @param  int $length Размер в байтах блока данных для записи в поток.
        * @return int Количество записанных байт.
        */
        public function write($length) {
            /* Если в буфере ничего нет, */
            if ($this->_write_buffer->getLength() <= 0) {
                /* то мы ничего и не делаем :) */
                return 0;
            }
            
            /* Переходим к началу буфера */
            $this->_write_buffer->rewind();
            
            /* Считываем блок данных необходимого размера */
            $data = $this->_write_buffer->read($length);
            /* И записываем его в поток */
            $bytes_written = parent::write($data);
            
            /* Если чего-то записалось, */
            if ($bytes_written > 0) {
                /* то удаляем из буфера записанный блок */
                $this->_write_buffer->release($bytes_written);
            }
            
            return $bytes_written;
        }
    }

?>