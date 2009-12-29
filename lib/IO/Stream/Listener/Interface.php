<?php

    interface IO_Stream_Listener_Interface {
        public function onStreamRead(IO_Stream $stream, $bytes_read);
        
        public function onStreamWrite(IO_Stream $stream, $bytes_written);
        
        public function onStreamError(IO_Stream $stream, $error);
    }

?>