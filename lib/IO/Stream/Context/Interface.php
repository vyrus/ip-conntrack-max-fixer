<?php

    /**
    * Интерфейс контекста потока.
    */
    interface IO_Stream_Context_Interface {
        /**
        * Возвращает новый объект настроек.
        * 
        * @return Options_Interface
        */
        public function createOptions();
    }

?>