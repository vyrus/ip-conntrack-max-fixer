<?php

    /**
    * Класс-обёртка для работы с потоками. Инкапсулирует общие функции (чтение,
    * запись, смена режима блокировки, закрытие). Функции, специфичные для
    * разных типов потоков, помещаются в искры (IO_Stream_Spark_*).
    */
    class IO_Stream implements IO_Stream_Interface {
        /**
        * Контекст объекта.
        * 
        * @var IO_Stream_Context_Interface
        */
        protected $_context;
        
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
        
        /**
        * Объект, обрабатывающий события потока.
        * 
        * @var IO_Stream_Listener_Interface
        */
        protected $_listener;
        
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
        * Флаг, обозначающий, закрыт ли поток или нет.
        * 
        * @var boolean
        */
        protected $_closed = false;
        
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
        protected $_default_options = array(
            'enable_profiler' => false
        );
        
        /**
        * Создание нового объекта потока.
        * 
        * @param  IO_Stream_Context_Interface $context Контекст потока.
        * @return IO_Stream
        */
        public function __construct(IO_Stream_Context_Interface $context) {
            $this->_context = $context;
            
            $this->_opts = $context->createOptions();
            $this->setOptions($this->_default_options);
            
            /* Инициализируем флаги операций */
            $this->resetAllInterest();
            $this->resetAllReady();
            
            /* Производим дополнительную инициализацию (if any) */
            $this->_init();
        }
        
        /**
        * Метод для дополнительной инициализации дочерних классов.
        * 
        * @return void
        */
        protected function _init() {/*_*/}
        
        /**
        * Закрытие потока при уничтожении объекта.
        * 
        * @return void
        */
        public function __destruct() {
            try {
                $this->close();
            }
            catch (Exception $e) {/*_*/}
        }
        
        /**
        * Создание нового объекта потока.
        * 
        * @param  IO_Stream_Context_Interface $context Контекст потока.
        * @return IO_Stream
        */
        public static function create(IO_Stream_Context_Interface $context) {
            return new self($context);
        }
        
        /**
        * Установка опций потока.
        * 
        * @param  array|Options $options
        * @return void
        */
        public function setOptions($options = array()) {
            $this->_opts->apply($options);
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
        
        /**
        * Сброс флага заинтересованности в операции.
        * 
        * @param mixed $operation
        * @return void
        */
        public function resetInterest($operation) {
            $this->_ops_interest[$operation] = false;
        }
        
        /**
        * Сброс флагов заинтересованности для всех операций (чтение, запись и
        * приём входящих соединений).
        * 
        * @return void
        */
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
        
        /**
        * Сброс всех флагов готовости к операциям.
        * 
        * @return void
        */
        public function resetAllReady() {
            $this->_ops_ready = array(self::OPERATION_READ   => false,
                                      self::OPERATION_WRITE  => false,
                                      self::OPERATION_ACCEPT => false);
        }
        
        /**
        * Возвращает "сырой" ресурс потока.
        * 
        * @return resource
        */
        public function getRawStream() {
            return $this->_stream;
        }
        
        /**
        * Возвращает номер ресурса потока.
        * 
        * @return int 
        */
        public function getId() {
            return (int) $this->_stream;
        }
        
        /**
        * Возвращает true, если поток открыт, иначе false.
        * 
        * @return boolean
        */
        public function isOpen() {
            if ($this->_closed) {
                $open = false;
            } else {
                $open = is_resource($this->_stream) && !$this->eof();
            }
            
            return $open;
        }
        
        /**
        * Закрытие потока.
        * 
        * @return void
        * @throws IO_Stream_Exception Если возникла ошибка при закрытии потока.
        */
        public function close() {
            if ($this->isOpen())
            {
                if (false !== fclose($this->_stream))
                {
                    $e = 'Ошибка при закрытии потока';
                    throw new IO_Stream_Exception($e);
                }
            }
            
            $this->_closed = true;
        }
        
        /**
        * Возвращает true, если достигнут конец потока или если произошла 
        * ошибка (включая таймаут для сокетов), иначе false. Обёртка для feof().
        * 
        * @return boolean
        */
        public function eof() {
            return feof($this->_stream);
        }
        
        /**
        * Чтение данных из потока. Обёртка для fread().
        * 
        * @param  int $length Количество байт, которые надо прочитать.
        * @return string Блок данных.
        * @throws IO_Stream_Exception При ошибке чтения или если поток закрыт.
        */
        public function read($length) {
            if (!$this->isOpen()) {
                $e = 'Попытка чтения из закрытого потока';
                throw new IO_Stream_Exception($e);
            }
            
            if (false === ($data = fread($this->_stream, $length))) {
                throw new IO_Stream_Exception('Ошибка при чтении из потока');
            }
            
            return $data;
        }
        
        /**
        * Запись данных в поток. Обёртка для fwrite().
        * 
        * @param  string $data Блок данных.
        * @return int Количество записанных байт.
        * @throws IO_Stream_Exception При ошибке записи или если поток закрыт.
        */
        public function write($data) {
            if (!$this->isOpen()) {           
                $e = 'Попытка записи в закрытый поток';
                throw new IO_Stream_Exception($e);
            }
            
            if (false === ($bytes_written = fwrite($this->_stream, $data))) {
                throw new IO_Stream_Exception('Ошибка при записи в поток');
            }
            
            return $bytes_written;
        }
        
        /**
        * Установка блокирующегося/неблокирующегося режима для потока.
        * 
        * @param  int $mode self::MODE_BLOCKING/self::MODE_NONBLOCKING.
        * @return boolean
        * @throws IO_Stream_Exception При ошибке установки режима.
        */
        public function setBlockingMode($mode) {
            $result = stream_set_blocking($this->_stream, $mode);
            
            if (false === $result) {
                $e = 'Ошибка при установке режима блокировки: ' . $mode;
                throw new IO_Stream_Exception($e);
            }
            
            return $result;
        }
    }

?>