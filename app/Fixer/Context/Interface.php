<?php
    
    interface Fixer_Context_Interface extends Network_Context_Interface,
                                              Telnet_Context_Interface,
                                              IO_Stream_Selector_Context_Interface,
                                              IO_Stream_Buffered_Context_Interface,
                                              IO_Stream_Spark_Context_Interface,
                                              IO_Buffer_Context_Interface {
        public function getNetwork();
        
        public function getTelnet();
    }
    
?>