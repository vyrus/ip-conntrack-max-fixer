<?php

    /* $Id: $ */
    
    class IO_Selector {
        /**
        * @var array
        */
        protected $stream_lookup = array();
        
        /**
        * @var Options
        */
        protected $options;
        
        /**
        * @var array
        */
        protected $default_options = array(
            'enable_profiler' => false,
            //
        );                                         
        
        public function __construct($options = null) {
            $this->options = Options::create($this->default_options);
            if (null !== $options) {
                $this->options->apply($options);
            }
        }
        
        public static function create($options = null) {
            return new self($options);
        }
        
        public function register(IO_Stream_Abstract $stream) {
            /**
            * @todo Проверять, закрыт ли поток? Как среагирует select() на закрытый поток?
            * @todo Проверять на добавление потоков-дубликатов?
            */
            $offset = $stream->id();
            $this->stream_lookup[$offset] = $stream;
        }
        
        public function unregister(IO_Stream_Abstract $stream) {
            $offset = $stream->id();
            if (array_key_exists($offset, $this->stream_lookup)) {
                unset($this->stream_lookup[$offset]);
            }
        }
        
        public function getStreams() {
            return $this->stream_lookup;
        }
        
        public function select(& $streams, $tv_usec = 100000) {
            if (empty($this->stream_lookup)) {
                return false;
            }
            
            $streams = array();
            $read = $write = $except = array();
            
            foreach ($this->stream_lookup as $stream)
            {
                $stream->resetReady();
                    
                if ($stream->interestedIn(IO_Stream_Abstract::OPERATION_READ | IO_Stream_Abstract::OPERATION_ACCEPT)) {
                    $read[] = $stream->stream();
                }
                
                if ($stream->interestedIn(IO_Stream_Abstract::OPERATION_WRITE)) {
                    $write[] = $stream->stream();
                }
            }
            
            /**
            * Если в результате ни одного массива для stream_select() не сформировано, выходим.
            * Функция stream_select() сгенерирует оишибку, если передать ей пустые массивы.
            */
            if (sizeof($read) == 0 && sizeof($write) == 0) {
                return false;
            }
            
            if (false === ($num_changed = stream_select($read, $write, $except, 0, $tv_usec))) {
                throw new IO_Selector_Exception('Ошибка при выполнении вызова stream_select()');
            }
            
            if ($num_changed > 0) {
                $this->_selectStreams($read,  $streams, IO_Stream_Abstract::OPERATION_READ | IO_Stream_Abstract::OPERATION_ACCEPT);
                $this->_selectStreams($write, $streams, IO_Stream_Abstract::OPERATION_WRITE);
            }
            
            return sizeof($streams);
        }
        
        protected function _selectStreams($raw_streams, & $streams, $operations) {
            foreach ($raw_streams as $raw_stream) {
                $offset = (int) $raw_stream;
                $stream = $this->stream_lookup[$offset];
                
                $stream->setReady($operations);
                /**
                * Сохраняем поток по уникальному offset'у, чтобы один и тот же поток, но
                * с двумя разными интересами (на чтение и на запись) не оказался дважды
                * в одном массиве.
                */
                $streams[$offset] = $stream;
            }
        }
    }

?>