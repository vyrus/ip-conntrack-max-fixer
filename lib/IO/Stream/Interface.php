<?php
    
    /**
    * Интерфейс потока ввода/вывода.
    */
    interface IO_Stream_Interface {
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
        * Режим потока: неблокирующийся.
        * 
        * @var int
        */
        const MODE_NONBLOCKING = 0;
        
        /**
        * Режим потока: блокирующийся.
        * 
        * @var int
        */
        const MODE_BLOCKING = 1;
        
        /**
        * Установка искры потока.
        * 
        * @param IO_Stream_Spark_Interface $spark
        * @return void
        */
        public function setSpark(IO_Stream_Spark_Interface $spark);
        
        /**
        * Возвращает искру потока.
        * 
        * @return IO_Stream_Spark_Interface
        */
        public function getSpark();
        
        /**
        * Установка слушателя событий потока.
        * 
        * @param IO_Stream_Listener_Interface $listener
        * @return void
        */
        public function setListener(IO_Stream_Listener_Interface $listener);
        
        /**
        * Получение слушателья событий потока.
        * 
        * @return IO_Stream_Listener_Interface
        */
        public function getListener();
        
        /**
        * Возведение флажка о заинтересованности в операции.
        * 
        * @param mixed $operation
        * @return void
        */
        public function setInterest($operation);
        
        /**
        * Получения значения флажка операции - интересует ли она поток или нет.
        * 
        * @param  mixed $operation
        * @return boolean
        */
        public function getInterest($operation);
        
        /**
        * Сброс флага заинтересованности в операции.
        * 
        * @param mixed $operation
        * @return void
        */
        public function resetInterest($operation);
        
        /**
        * Сброс флагов заинтересованности для всех операций (чтение, запись и
        * приём входящих соединений).
        * 
        * @return void
        */
        public function resetAllInterest();
        
        /**
        * Возведение флажка о готовности к операции.
        * 
        * @param mixed $operation
        * @return void
        */
        public function setReady($operation);
        
        /**
        * Получения значения флажка операции - готов поток к осуществлению такой
        * операции или нет.
        * 
        * @param  mixed $operation
        * @return boolean
        */
        public function getReady($operation);
        
        /**
        * Сброс всех флагов готовости к операциям.
        * 
        * @return void
        */
        public function resetAllReady();
        
        /**
        * Возвращает "сырой" ресурс потока.
        * 
        * @return resource
        */
        public function getRawStream();
        
        /**
        * Возвращает номер ресурса потока.
        * 
        * @return int 
        */
        public function getId();
        
        /**
        * Возвращает true, если поток открыт, иначе false.
        * 
        * @return boolean
        */
        public function isOpen();
        
        /**
        * Закрытие потока.
        * 
        * @return void
        */
        public function close();
        
        /**
        * Возвращает true, если достигнут конец потока или если произошла 
        * ошибка (включая таймаут для сокетов), иначе false.
        * 
        * @return boolean
        */
        public function eof();
        
        /**
        * Чтение данных из потока.
        * 
        * @param  int $length Количество байт, которые надо прочитать.
        * @return string Блок данных.
        */
        public function read($length);
        
        /**
        * Запись данных в поток.
        * 
        * @param  string $data Блок данных.
        * @return int Количество записанных байт.
        */
        public function write($data);
        
        /**
        * Установка блокирующегося/неблокирующегося режима для потока.
        * 
        * @param  int $mode self::MODE_BLOCKING/self::MODE_NONBLOCKING.
        * @return boolean
        */
        public function setBlockingMode($mode);
    }
    
?>