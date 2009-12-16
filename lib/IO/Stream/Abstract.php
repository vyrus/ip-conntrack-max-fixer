<?php

    /**
    * @todo Вынести сюда все общие атрибуты и методы дочерних классов.
    */
    abstract class IO_Stream_Abstract extends FactoryClass {
        /**
        * @var const
        * @todo Заменить стринговые значения для отладки на цифровые?
        */
        const OPERATION_NONE = 0 /*'none'*/;
        
        /**
        * @var const
        */
        const OPERATION_READ = 1 /* 'read' */;
        
        /**
        * @var const
        */
        const OPERATION_WRITE = 2 /* 'write' */;
        
        /**
        * @var const
        */
        const OPERATION_ACCEPT = 4 /* 'accept' */;
        
        /**
        * @var resource
        */
        protected $stream;
        
        protected $closed = false;
        
        /**
        * @var int
        */
        protected $blocking_mode;
        
        /**
        * @todo Заюзать BitFlags?
        */
        /**
        * @var BitFlags
        * @todo Перевести на обычный массив. 
        */
        protected $interest_ops;
        
        /**
        * @var BitFlags
        * @todo Перевести на обычный массив.
        */
        protected $ready_ops;
        
        /**
        * @var array
        */
        protected $attachments = array();
        
        /**
        * Объект, обрабатывающий события потока.
        * 
        * @var IO_Stream_Listener_Interface
        */
        protected $_listener;
        
        /**
        * @var Options
        */
        protected $options;
        
        /**
        * @var array
        */
        protected $default_options = array(
            'enable_profiler' => false
        );
        
        public function __construct($options = null) {
            $this->options = Options::create($this->default_options);
            
            if (null !== $options) {
                $this->options->apply($options);
            }
            
            /**
            * @todo Переделать для использования массивов вместо BitFlags.
            */
            $this->interest_ops = BitFlags::create(self::OPERATION_NONE);
            $this->ready_ops    = BitFlags::create(self::OPERATION_NONE);
            
            $this->init();
        }
        
        public static function create($type, $options = null) {
            return self::factory($type, $options, __CLASS__);
        }
        
        public function init() {/*_*/}
        
        public function stream() {
            return $this->stream;
        }
        
        /**
        * @todo Проверить на уникальность, так как потом это значение используется в массивах в качестве уникальных ключей. 
        */
        public function id() {
            return (int) $this->stream;
        }
        
        public function isOpen() {
            if ($this->closed) {
                $open = false;
            } else {
                $open = is_resource($this->stream) && !$this->eof();
            }
            
            return $open;
        }
        
        abstract function open();
        
        public function read($length) {
            if (!$this->isOpen()) {
                throw new IO_Stream_Exception('Попытка чтения из закрытого потока');
            }
            
            if (false === ($data = fread($this->stream, $length))) {
                throw new IO_Stream_Exception('Ошибка при чтении из потока');
            }
            
            return $data;
        }
        
        public function write($data) {
            if (!$this->isOpen()) {
                throw new IO_Stream_Exception('Попытка записи в закрытый поток');
            }
            
            if (false === ($bytes_written = fwrite($this->stream, $data))) {
                throw new IO_Stream_Exception('Ошибка при записи в поток');
            }
            
            return $bytes_written;
        }
        
        public function setBlockingMode($mode) {
            if (false === ($result = stream_set_blocking($this->stream, $mode))) {
                throw new IO_Stream_Exception('Ошибка при установке режима блокировки: ' . $mode);
            }
            
            $this->blocking_mode = $mode;
            
            return $result;
        }
        
        public function eof() {
            return feof($this->stream);
        }
        
        public function close() {
            if ($this->isOpen()) {
                /**
                * @todo Зачем нам тут бросать эксепшн? Мы лучше по-тихому... :-)
                */
                //throw new IO_Stream_Exception('Попытка закрытия уже закрытого потока');
                fclose($this->stream);
            }
            
            $this->closed = true;
        }
        
        public function __destruct() {
            if (!$this->closed) {
                try {
                    $this->close();
                } catch (Exception $e) {/*_*/}
            }
        }
        
        public function setInterest($operations) {
            $this->interest_ops->set($operations);     
        }
        
        public function interestedIn($operations) {
            return $this->interest_ops->is_set($operations);
        }
        
        public function resetInterest($operations) {
            return $this->interest_ops->reset($operations);
        }
        
        public function setReady($operations) {
            $this->ready_ops->set($operations);
        }
        
        public function isReady($operations) {
            return $this->ready_ops->is_set($operations);
        }
        
        public function resetReady() {
            $this->ready_ops->reset();
        }
        
        public function attach($key, $attachment) {
            $this->attachments[$key] = $attachment;
        }
        
        public function attachment($key) {
            if (!array_key_exists($key, $this->attachments)) {
                return null;
            }
            
            return $this->attachments[$key];
        }
        
        public function setListener(IO_Stream_Listener_Interface $listener) {
            $this->_listener = $listener;
        }
        
        public function getListener() {
            return $this->_listener;
        }
    }

?>