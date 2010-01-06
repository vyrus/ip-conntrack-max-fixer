<?php

    class Telnet implements IO_Stream_Listener_Interface {
        /**
        * //
        * 
        * @var Telnet_Context_Interface
        */
        protected $_context;
        
        /**
        * //
        * 
        * @var IO_Stream_Abstract
        */
        protected $_stream;
        
        /**
        * //
        * 
        * @var IO_Buffer
        */
        protected $_read_buffer;
        
        /**
        * //
        * 
        * @var IO_Buffer
        */
        protected $_write_buffer;
        
        /**
        * //
        * 
        * @var IO_Buffer
        */
        protected $_echo_buffer;
        
        /**
        * //
        * 
        * @var Telnet_Listener_Interface
        */
        protected $_listener;
        
        public function __construct(Telnet_Context_Interface $context) {
            $this->_context = $context;
        }
        
        public static function create(Telnet_Context_Interface $context) {
            return new self($context);
        }
        
        public function setListener(Telnet_Listener_Interface $listener) {
            $this->_listener = $listener;
        }
        
        public function connect($host, $port = 23) {
            $opts = array(
                'host' => $host,
                'port' => $port
            );
            
            $spark = $this->_context->createSocketSpark();
            $spark->setOptions($opts);
            $spark->ignite();
            
            $stream = $this->_context->createBufferedStream();
            $stream->setSpark($spark);
            $stream->setListener($this);
            $stream->setBlockingMode(0);
            $stream->setInterest(IO_Stream_Interface::OPERATION_READ);
            
            $this->_stream = $stream;
            $this->_listener->onTelnetConnected($this, $stream);
            
            $this->_read_buffer  = $stream->getReadBuffer();
            $this->_write_buffer = $stream->getWriteBuffer();
            $this->_echo_buffer  = $this->_context->createBuffer();
        }
        
        public function disconnect() {
            $this->_stream->close();
            $this->_listener->onTelnetDisconnected($this, $this->_stream);
        }
        
        public function sendString($data = '', $supress_echo = true) {
            $data .= CRLF;
            
            if ($supress_echo) {
                $this->_echo_buffer->write($data);
            }
            
            $this->_write_buffer->write($data);
            $this->_stream->setInterest(IO_Stream_Interface::OPERATION_WRITE);
            
            return $this->_write_buffer->getOffset();
        }
        
        public function onStreamRead(IO_Stream_Interface $stream, $bytes_read) {
            $this->_read_buffer->rewind();
            $data = $this->_read_buffer->read();
            
            $echo_len = $this->_echo_buffer->getLength();
            if ($echo_len)
            {
                $data_len = strlen($data);
                if ($data_len < $echo_len) {
                    $echo_len = $data_len;
                }
                
                $this->_echo_buffer->rewind();
                $echo = $this->_echo_buffer->read($echo_len);
                $possible_echo = substr($data, 0, $echo_len);
                
                if ($possible_echo == $echo) {
                    $data = substr($data, $echo_len);
                    
                    $this->_read_buffer->release($echo_len);
                    $this->_echo_buffer->release($echo_len);
                }
            }
            
            $success = Telnet_Listener_Interface::SUCCESS;
            
            if ($success === $this->_listener->onTelnetPromt($this, $data))
            {
                $length = strlen($data);
                $this->_read_buffer->release($length);
            }
        }
        
        public function onStreamWrite(IO_Stream_Interface $stream, $bytes_written) {
            if ($this->_write_buffer->getLength() > 0)
                return;
            
            $this->_stream->resetInterest(IO_Stream_Interface::OPERATION_WRITE);
        }
        
        public function onStreamClose(IO_Stream_Interface $stream) {
            $this->disconnect();
        }
        
        public function onStreamError(IO_Stream_Interface $stream, $error) {
            $this->disconnect();
        }
    }

?>