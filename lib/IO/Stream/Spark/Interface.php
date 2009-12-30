<?php
    
    /**
    * Интерфейс искры потока.
    */
    interface IO_Stream_Spark_Interface {
        /**
        * Открытие потока.
        * 
        * @return boolean
        */
        public function ignite();
        
        /**
        * Возвращает "сырой" поток.
        * 
        * @return resource
        */
        public function getStream();
    }
    
?>