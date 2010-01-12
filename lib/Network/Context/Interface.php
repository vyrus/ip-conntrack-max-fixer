<?php

    /**
    * Интерфейс контекста службы сети.
    */
    interface Network_Context_Interface extends Context_Optable_Interface {
        /**
        * Возвращает новый объект селектора потоков.
        * 
        * @return IO_Stream_Selector_Interface
        */
        public function createStreamSelector();
    }

?>