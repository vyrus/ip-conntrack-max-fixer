<?php

    /**
    * @todo Вынести сюда все общие атрибуты и методы дочерних классов.
    */
    class IO_Stream {
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
        * Режим потока: блокирующийся.
        * 
        * @var int
        */
        const MODE_BLOCKING = 1;
        
        /**
        * Режим потока: неблокирующийся.
        * 
        * @var int
        */
        const MODE_NONBLOCKING = 0;
        
        /**
        * Искра потока.
        * 
        * @vaк IO_Stream_Spark_Interface
        */
        protected $_spark;
        
        /**
        * Сырой поток.
        * 
        * @var resource
        */
        protected $_stream;
        
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
        protected $_opts;
        
        /**
        * @var array
        */
        protected $default_options = array(
            'enable_profiler' => false
        );
        
        /**
        * Создание нового объекта потока.
        * 
        * @param  array|Options $options Опции объекта.
        * @return IO_Stream
        */
        public function __construct($options = null) {
            $this->_opts = Options::create($this->default_options);
            
            if (null !== $options) {
                $this->_opts->apply($options);
            }
            
            /**
            * @todo Переделать для использования массивов вместо BitFlags.
            */
            $this->interest_ops = BitFlags::create(self::OPERATION_NONE);
            $this->ready_ops    = BitFlags::create(self::OPERATION_NONE);
        }
        
        /**
        * Создание нового объекта потока.
        * 
        * @param  array|Options $options Опции объекта
        * @return IO_Stream
        */
        public static function create($options = null) {
            return new self($options);
        }
        
        /**
        * Установка искры потока.
        * 
        * @param IO_Stream_Spark_Interface $spark
        * @return void
        */
        public function setSpark(IO_Stream_Spark_Interface $spark) {
            $this->_spark = $spark;
            $this->_stream = $spark->getStream();
        }
        
        /**
        * Возвращает искру потока.
        * 
        * @return IO_Stream_Spark_Interface
        */
        public function getSpark() {
            return $this->_spark;
        }
        
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
            $result = stream_set_blocking($this->_stream, $mode);
            
            if (false === $result) {
                $e = 'Ошибка при установке режима блокировки: ' . $mode;
                throw new IO_Stream_Exception($e);
            }
            
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
        
        /**
        * Установка слушателя событий потока.
        * 
        * @param IO_Stream_Listener_Interface $listener
        * @return void
        */
        public function setListener(IO_Stream_Listener_Interface $listener) {
            $this->_listener = $listener;
        }
        
        /**
        * Получение слушателья событий потока.
        * 
        * @return IO_Stream_Listener_Interface
        */
        public function getListener() {
            return $this->_listener;
        }
    }

?>