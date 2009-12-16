<?php

    /**
    * Основа для реализации шаблона проектирования "Factory".
    * @todo Зачем от этого класс нужно наследоваться?
    */
    abstract class FactoryClass {
        /**
        * Создание нового экземпляра дочернего (для $base_class) класса.
        * 
        * @param  string $type       Конец имени дочернего класса.
        * @param  array  $params     Параметры, передаваемые в конструктор дочернего класса.
        * @param  string $base_class Имя родительского класса.
        * @return object Новый экземпляр дочернего класса.
        * @throws FactoryException Если не найдено объявление дочернего класса.
        * @todo Переориентировать на использование Options.
        */
        public static function factory($type, array $params = array(), $base_class) {
            $base_class = str_replace('_Abstract', '', $base_class);
            $derived_class = $base_class . '_' . ucfirst($type);
            
            if (!class_exists($derived_class /* $autoLoad = true */)) {
                throw new FactoryClass_Exception('Class "' . $derived_class . '" not found');
            } 
            
            return new $derived_class($params);
        }
    }

?>
