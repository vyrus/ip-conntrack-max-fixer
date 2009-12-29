<?php

    /**
    * @todo Вынести сюда все общие атрибуты и методы дочерних классов.
    */
    class IO_Stream {
        /**
        * @var const
        */
        const OPERATION_READ = 'read';
        
        /**
        * @var const
        */
        const OPERATION_WRITE = 'write';
        
        /**
        * @var const
        */
        const OPERATION_ACCEPT = 'accept';
        
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
        * Массив флагов потока, указывающих, в каких операциях он заинтересован.
        * 
        * @var array
        */
        protected $_ops_interest;
        
        /**
        * Массив флагок потока, показывающих, к каким операциям он готов.
        * 
        * @var array
        */
        protected $_ops_ready;
        
        /**
        * Объект, обрабатывающий события потока.
        * 
        * @var IO_Stream_Listener_Interface
        */
        protected $_listener;
        
        /**
        * Опции потока.
        * 
        * @var Options
        */
        protected $_opts;
        
        /**
        * Значения опций потока по умолчанию.
        * 
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
            
            $this->resetAllInterest();
            $this->resetAllReady();
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
        
        /**
        * Возведение флажка о заинтересованности в операции.
        * 
        * @param mixed $operation
        * @return void
        */
        public function setInterest($operation) {
            $this->_ops_interest[$operation] = true;     
        }
        
        /**
        * Получения значения флажка операции - интересует ли она поток или нет.
        * 
        * @param  mixed $operation
        * @return boolean
        */
        public function getInterest($operation) {
            return (true === $this->_ops_interest[$operation]);
        }
        
        public function resetInterest($operation) {
            $this->_ops_interest[$operation] = false;
        }
        
        public function resetAllInterest() {
            $this->_ops_interest = array(self::OPERATION_READ   => false,
                                         self::OPERATION_WRITE  => false,
                                         self::OPERATION_ACCEPT => false);
        }
        
        /**
        * Возведение флажка о готовности к операции.
        * 
        * @param mixed $operation
        * @return void
        */
        public function setReady($operation) {
            $this->_ops_ready[$operation] = true;
        }
        
        /**
        * Получения значения флажка операции - готов поток к осуществлению такой
        * операции или нет.
        * 
        * @param  mixed $operation
        * @return boolean
        */
        public function getReady($operation) {
            return (true === $this->_ops_ready[$operation]);
        }
        
        public function resetAllReady() {
            $this->_ops_ready = array(self::OPERATION_READ   => false,
                                      self::OPERATION_WRITE  => false,
                                      self::OPERATION_ACCEPT => false);
        }
    }

?>