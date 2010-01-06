<?php

    /**
    * Интерфейс контекста буфера ввода/вывода.
    */
    interface IO_Buffer_Context_Interface {
        /**
        * Возвращает новый объект настроек.
        * 
        * @return Options_Interface
        */
        public function createOptions();
    }

?>