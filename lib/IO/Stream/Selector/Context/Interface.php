<?php

    /**
    * Интерфейс контекста селектора.
    */
    interface IO_Stream_Selector_Context_Interface {
        /**
        * Возвращает новый объект настроек.
        * 
        * @return Options_Interface
        */
        public function createOptions();
    }

?>