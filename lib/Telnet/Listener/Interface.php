<?php

    interface Telnet_Listener_Interface {
        const SUCCESS = 'success';
        
        const FAILURE = 'failure';
        
        public function onTelnetConnected(Telnet $telnet, IO_Stream_Abstract $stream);
        
        public function onTelnetPromt(Telnet $telnet, $promt);
        
        public function onTelnetDisconnected(Telnet $telnet, IO_Stream_Abstract $stream);
    }

?>