<?php
    
    /**
    * Интерфейс контекста объекта, который запрашивает создание объекта 
    * настроек.
    */
    interface Context_Optable_Interface {
        /**
        * Возвращает новый объект настроек.
        * 
        * @return Options_Interface
        */
        public function createOptions();
    } 
    
?>