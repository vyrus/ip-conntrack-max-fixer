<?php

    /**
    * Интерфейс контекста искры.
    */
    interface IO_Stream_Spark_Context_Interface {
        /**
        * Возвращает новый объект настроек
        * 
        * @return Options_Interface
        */
        public function createOptions();
    }

?>