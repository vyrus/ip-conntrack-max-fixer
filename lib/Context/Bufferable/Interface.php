<?php
    
    /**
    * Интерфейс контекста объекта, запрашивающего создание буфера.
    */
    interface Context_Bufferable_Interface {
        /**
        * Создание нового объекта буфера ввода/вывода.
        * 
        * @var IO_Buffer_Interface
        */
        public function createBuffer();
    }
    
?>