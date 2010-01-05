<?php

    /**
    * Интерфейс селектора потоков.
    */
    interface IO_Stream_Selector_Interface {
        /**
        * Регистрация потока в селекторе.
        * 
        * @param IO_Stream_Interface $stream
        * @return void
        */
        public function register(IO_Stream_Interface $stream);
        
        /**
        * Удаление потока из списка зарегистрированных.
        * 
        * @param IO_Stream_Interface $stream
        * @return void
        */
        public function unregister(IO_Stream_Interface $stream);
        
        /**
        * Осуществляет выбор готовых к обработке потоков из списка 
        * зарегистрированных и возвращает их в первом аргументе-массиве. 
        * Максимальное время ожидания задаёт верхний предел времени, в течение 
        * которого метод будет ждать изменения состояния потоков (но он вернёт 
        * результат сразу же, как изменится состояние хотя бы одного потока).
        * 
        * @param mixed $streams Выходной массив потоков.
        * @param mixed $tv_usec Максимальное время ожидания (в миллисекундах).
        * @return int Количество готовых к обработке потоков.
        */
        public function select(& $streams, $tv_usec = 100000);
    }

?>