<?php

    /**
    * Интерфейс контекста буферизованного потока.
    */
    interface IO_Stream_Buffered_Context_Interface extends IO_Stream_Context_Interface {
        /**
        * Возвращает новый объект буфера ввода/вывода.
        * 
        * @return Options_Interface
        */
        public function createBuffer();
    }

?>