<?php
    
    /**
    * Интерфейс контекста телнета.
    */
    interface Telnet_Context_Interface extends Context_Bufferable_Interface {
        /**
        * Создание нового буферизованного потока.
        *
        * @var IO_Stream_Buffered_Interface
        */
        public function createBufferedStream();
        
        /**
        * Создание новой искры сокета.
        *
        * @var IO_Stream_Spark_Interface
        */
        public function createSocketSpark();
    }
    
?>