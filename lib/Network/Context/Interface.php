<?php

    /**
    * Интерфейс контекста службы сети.
    */
    interface Network_Context_Interface {
        /**
        * Возвращает новый объект настроек.
        * 
        * @return Options_Interface
        */
        public function createOptions();
        
        /**
        * Возвращает новый объект селектора потоков.
        * 
        * @return IO_Stream_Selector_Interface
        */
        public function createStreamSelector();
    }

?>