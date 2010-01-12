<?php
    
    /**
    * Интерфейс службы сети.
    */
    class Network_Interface {
        /**
        * Регистрация потока для обработки.
        * 
        * @param  IO_Stream_Interface $stream
        * @return void
        */
        public function registerStream(IO_Stream_Interface $stream);
        
        /**
        * Отмена регистрации потока.
        * 
        * @param  IO_Stream_Interface $stream
        * @return void
        */
        public function unregisterStream(IO_Stream_Interface $stream);
        
        /**
        * Обработка потоков. Выбирает потоки, готовые к обработке, производит 
        * чтение/запись и вызывает обработчики событий для потоков.
        * 
        * @return void
        */
        public function dispatchStreams();
    }
    
?>