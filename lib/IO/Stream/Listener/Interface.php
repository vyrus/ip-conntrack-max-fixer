<?php
    
    /**
    * Интерфейс обработчика событий потока.
    */
    interface IO_Stream_Listener_Interface {
        /**
        * Обработчик события чтения данных из потока.
        * 
        * @param  IO_Stream_Interface $stream Поток, для которого вызывается обработчик.
        * @param  int $bytes_read Размер считанного блока данных в байтах.
        * @return void
        */
        public function onStreamRead(IO_Stream_Interface $stream, $bytes_read);
        
        /**
        * Обработчик события записи данных в поток.
        * 
        * @param  IO_Stream_Interface $stream Поток, для которого вызывается обработчик.
        * @param  int $bytes_written Размер записанного блока данных в байтах.
        * @return void 
        */
        public function onStreamWrite(IO_Stream_Interface $stream,
                                      $bytes_written);
        
        /**
        * Обработчик события закрытия потока.
        * 
        * @param  IO_Stream_Interface $stream Поток, для которого вызывается обработчик.
        * @return void
        */
        public function onStreamClose(IO_Stream_Interface $stream);
        
        /**
        * Обработчик события ошибки при работе с потоком.
        * 
        * @param  IO_Stream_Interface $stream Поток, для которого вызывается обработчик.
        * @param  string $error Текст ошибки.
        * @return void
        */
        public function onStreamError(IO_Stream_Interface $stream, $error);
    }

?>