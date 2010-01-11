<?php
    
    class Fixer_Context implements Fixer_Context_Interface {
        /**
        * //
        * 
        * @var Network_Interface
        */
        protected $_network;
        
        /**
        * //
        * 
        * @var Telnet
        */
        protected $_telnet;
        
        public function getNetwork() {
            if (!($this->_network instanceof Network_Interface)) {
                $this->_network = Network::create($this);
            }
            
            return $this->_network;
        }
        
        public function getTelnet() {
            if (!($this->_telnet instanceof Telnet)) {
                $this->_telnet = Telnet::create($this);
            }
            
            return $this->_telnet;
        }
        
        /**
        * Возвращает новый объект настроек.
        * 
        * @return Options_Interface
        */
        public function createOptions() {
            return Options::create();
        }
        
        /**
        * Возвращает новый объект селектора потоков.
        * 
        * @return IO_Stream_Selector_Interface
        */
        public function createStreamSelector() {
            return IO_Stream_Selector::create($this);
        }
        
        /**
        * Создание нового буферизованного потока.
        *
        * @var IO_Stream_Buffered_Interface
        */
        public function createBufferedStream() {
            return IO_Stream_Buffered::create($this);
        }
        
        /**
        * Создание новой искры сокета.
        *
        * @var IO_Stream_Spark_Interface
        */
        public function createSocketSpark() {
            return IO_Stream_Spark_Socket::create($this);
        }
        
        /**
        * Создание нового объекта буфера ввода/вывода.
        * 
        * @var IO_Buffer_Interface
        */
        public function createBuffer() {
            return IO_Buffer::create($this);
        }
    }
    
?>