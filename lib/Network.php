<?php
    
    /**
    * Класс службы сети, которая занимается обработкой потоков и вызовом 
    * соответствующих callback'ов в обработчиках событий потоков.
    */
    class Network {
        /**
        * Селектор потоков.
        * 
        * @var IO_Stream_Selector_Interface
        */
        protected $_selector;
        
        /**
        * Опции сети.
        * 
        * @var Options
        */            
        protected $_opts;
        
        /**
        * Значения опций по умолчанию.
        * 
        * @var array
        */
        protected $_default_options = array(
            'stream.read_at_once'  => 4096,
            'stream.write_at_once' => 4096,
        );
        
        /**
        * Создание нового объекта сети.
        * 
        * @param  Network_Context_Interface $context Контекст службы сети.
        * @return Network
        */
        public function __construct(Network_Context_Interface $context) {
            $this->_opts = $context->createOptions();
            $this->setOptions($this->_default_options);
            
            $this->_selector = $context->createStreamSelector();
        }
        
        /**
        * Создание нового объекта сети.
        * 
        * @param  Network_Context_Interface $context Контекст службы сети.
        * @return Network
        */
        public static function create(Network_Context_Interface $context) {
            return new self($context);
        }
        
        /**
        * Установка опций сети.
        * 
        * @param  array|Options $options
        * @return void
        */
        public function setOptions($options = array()) {
            $this->_opts->apply($options);
        }
        
        /**
        * Регистрация потока для обработки.
        * 
        * @param  IO_Stream_Interface $stream
        * @return void
        */
        public function registerStream(IO_Stream_Interface $stream) {
            $this->_selector->register($stream);
        }
        
        /**
        * Отмена регистрации потока.
        * 
        * @param  IO_Stream_Interface $stream
        * @return void
        */
        public function unregisterStream(IO_Stream_Interface $stream) {
            $this->_selector->unregister($stream);
        }
        
        /**
        * Обработка потоков. Выбирает потоки, готовые к обработке, производит 
        * чтение/запись и вызывает обработчики событий для потоков.
        * 
        * @return void
        */
        public function dispatchStreams() {
            /* Выбираем готовые к обработке потоки */
            $streams = $this->_selector->select();
            
            if (sizeof($streams) <= 0) {
                return;
            }
            
            /* Пробегаемся по выбранным потокам и производим нужны операции */
            foreach ($streams as $stream)
            {
                if (!($stream instanceof IO_Stream_Buffered_Interface)) {
                    $e  = 'Обработка небуферизованных потоков ещё не '; 
                    $e .= 'реализована :(';
                    throw new Network_Exception($e);
                }
                
                $this->_dispatchBufferedStream($stream);
            }
        }
        
        /**
        * Обработка буферизованного потока.
        * 
        * @param  IO_Stream_Buffered_Interface $stream
        * @return void
        */
        protected function _dispatchBufferedStream(
            IO_Stream_Buffered_Interface $stream
        ) {
            $listener = $stream->getListener();
           
            /**
            * @todo А всегда ли селектор будет выбирать закрывшийся поток на 
            * обработку?
            */
            /* Проверяем, не закрылся ли поток */
            if (!$stream->isOpen()) {
                /* Вызываем обработчик */
                $listener->onStreamClose($stream);
                
                return;
            }
           
            $read_at_once  = $this->_opts->get('stream.read_at_once');
            $write_at_once = $this->_opts->get('stream.write_at_once');
            
            $total_read = 0;
            $total_written = 0;
            
            try {
                /* Если поток готов к чтению, */
                if ($stream->getReady(IO_Stream_Interface::OPERATION_READ))
                {
                    /* то считываем данные в буфер */
                    while ($read = $stream->read($read_at_once)) {
                        $total_read += $read;
                    }
                }
                
                /* Если поток готов к записи, */
                if ($stream->getReady(IO_Stream_Interface::OPERATION_WRITE))
                {
                    /* то записываем данные из буфера */
                    while ($written = $stream->write($write_at_once)) {
                        $total_written += $written;
                    }
                }
            }
            /* Если при осуществлении произошла ошибка, */
            catch (IO_Stream_Exception $e) {
                /* то вызываем обработчик ошибок */
                $listener->onStreamError($stream, $e->getMessage());
                return;
            }
            
            /**
            * Если удалось что-нибудь прочитать или записать, то вызываем 
            * соответствующие обработчики.
            */
            if ($total_read > 0) {                    
                $listener->onStreamRead($stream, $total_read);
            }
            
            if ($total_written > 0) {
                $listener->onStreamWrite($stream, $total_written);
            }
        }
    }
    
?>