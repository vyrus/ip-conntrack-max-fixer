<?php

    class Telnet implements IO_Stream_Listener_Interface {
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
        
        public static function create() {
            return new self();
        }
        
        public function setListener(Telnet_Listener_Interface $listener) {
            $this->_listener = $listener;
        }
        
        public function connect($host, $port = 23) {
            $opts = array(
                'host' => $host,
                'port' => $port
            );
            
            $stream = IO_Stream_Buffered::create('socket', $opts);
            $stream->setListener($this);
            $stream->connect();
            $stream->setBlockingMode(0);
            $stream->setInterest(IO_Stream_Abstract::OPERATION_READ);
            
            $this->_stream = $stream;
            $this->_listener->onTelnetConnected($this, $stream);
            
            $this->_read_buffer  = $stream->getReadBuffer();
            $this->_write_buffer = $stream->getWriteBuffer();
            $this->_echo_buffer = IO_Buffer::create();
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
            $this->_stream->setInterest(IO_Stream_Abstract::OPERATION_WRITE);
            
            return $this->_write_buffer->offset();
        }
        
        public function onStreamRead(IO_Stream_Abstract $stream, $bytes_read) {
            $this->_read_buffer->rewind();
            $data = $this->_read_buffer->read();
            
            $echo_len = $this->_echo_buffer->length();
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
        
        public function onStreamWrite(IO_Stream_Abstract $stream, $bytes_written) {
            if ($this->_write_buffer->length() > 0)
                return;
            
            $this->_stream->resetInterest(IO_Stream_Abstract::OPERATION_WRITE);
        }
        
        public function onStreamError(IO_Stream_Abstract $stream, $error) {
            $this->disconnect();
        } 
    }

?>