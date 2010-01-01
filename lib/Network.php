<?php

    class Network {
        /**
        * @var IO_Selector
        */
        protected $selector;
        
        /**
        * @var Options
        */            
        protected $options;
        
        /**
        * @var array
        */
        protected $default_options = array(
            'stream.read_at_once'  => 4096,
            'stream.write_at_once' => 4096,
        );
        
        public function __construct($options = null) {
            $this->options = Options::create($this->default_options);
            if (null !== $options) {
                $this->options->apply($options);
            }
            
            $this->selector = IO_Selector::create();
        }
        
        public static function create($options = null) {
            return new self($options);
        }
        
        public function registerStream(IO_Stream_Buffered $stream) {
            $this->selector->register($stream);
        }
        
        public function unregisterStream(IO_Stream_Buffered $stream) {
            $this->selector->unregister($stream);
        }
        
        public function dispatchStreams() {
            $streams = array();
            if ($this->selector->select($streams) <= 0) {
                return;
            }
                   
            foreach ($streams as $stream) {
                $this->_dispatchBufferedStream($stream);
            }
        }
        
        protected function _dispatchBufferedStream(IO_Stream_Buffered $stream) {
            $listener = $stream->getListener();
           
            /**
            * @todo А всегда ли селектор будет выбирать закрывшийся поток на обработку?
            */
            /* Проверяем, не разорвалось ли соединение */
            if (!$stream->isOpen()) {
                $this->selector->unregister($stream);
                /**
                * @todo $listener->onStreamClose()?
                */
                $listener->onStreamError($stream, 'Поток закрыт');
                
                return;
            }
           
            $read_at_once  = $this->options->get('stream.read_at_once');
            $write_at_once = $this->options->get('stream.write_at_once');
            
            $total_read = 0;
            $total_written = 0;
            
            try {
                if ($stream->isReady(IO_Stream_Abstract::OPERATION_READ))
                {
                    while ($read = $stream->read($read_at_once)) {
                        $total_read += $read;
                    }
                }
                
                if ($stream->isReady(IO_Stream_Abstract::OPERATION_WRITE))
                {
                    while ($written = $stream->write($write_at_once)) {
                        $total_written += $written;
                    }
                }
            }
            catch (IO_Stream_Exception $e) {
                $listener->onStreamError($stream, $e->getMessage());
                return;
            }
            
            if ($total_read > 0) {                    
                $listener->onStreamRead($stream, $total_read);
            }
            
            if ($total_written > 0) {
                $listener->onStreamWrite($stream, $total_written);
            }
        }
    }
    
?>