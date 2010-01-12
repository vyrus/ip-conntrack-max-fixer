<?php
    
    /**
    * Интерфейс буферизованного потока ввода-вывода.
    */
    interface IO_Stream_Buffered_Interface extends IO_Stream_Interface {
        /**
        * Возвращает объект буфера чтения.
        * 
        * @return IO_Buffer_Interface
        */
        public function getReadBuffer();
        
        /**
        * Возвращает объект буфера записи.
        * 
        * @return IO_Buffer_Interface
        */
        public function getWriteBuffer();
    }
    
?>