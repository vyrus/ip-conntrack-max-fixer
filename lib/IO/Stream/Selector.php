<?php
    
    /**
    * Класс селектора потоков, позволяет осуществлять асинхронный ввод/вывод на 
    * неблокирующихся потоках.
    * 
    * @todo Или всё же IO_Selector? :)
    */
    class IO_Stream_Selector implements IO_Stream_Selector_Interface {
        /**
        * Список зарегистрированных потоков.
        * 
        * @var array
        */
        protected $_streams = array();
        
        /**
        * Опции селектора.
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
            'enable_profiler' => false
        );                                         
        
        /**
        * Создание нового объекта селектора.
        * 
        * @param  array|Options $options Опции объекта.
        * @return IO_Stream_Selector
        */
        public function __construct($options = null) {
            $this->_opts = Options::create($this->_default_options);
            
            if (null !== $options) {
                $this->_opts->apply($options);
            }
        }
        
        /**
        * Создание нового объекта селектора.
        * 
        * @param  array|Options $options Опции потока.
        * @return IO_Stream_Selector
        */
        public static function create($options = null) {
            return new self($options);
        }
        
        /**
        * Регистрация потока в селекторе.
        * 
        * @param IO_Stream_Interface $stream
        * @return void
        */
        public function register(IO_Stream_Interface $stream) {
            /**
            * @todo Проверять, закрыт ли поток? Как среагирует select() на
            * закрытый поток?
            */
            $idx = $stream->getId();
            
            if (isset($this->_streams[$idx])) {
                $e = 'Поток уже зарегистрирован';
                throw new IO_Stream_Selector_Exception($e);
            }
            
            $this->_streams[$idx] = $stream;
        }
        
        /**
        * Удаление потока из списка зарегистрированных.
        * 
        * @param IO_Stream_Interface $stream
        * @return void
        */
        public function unregister(IO_Stream_Interface $stream) {
            $idx = $stream->getId();
            
            if (!isset($this->_streams[$idx])) {
                $e = 'Попытка удаления незарегистрированного потока';
                throw new IO_Stream_Selector_Exception($e);
            }
            
            unset($this->_streams[$idx]);
        }
        
        /**
        * Осуществляет выбор готовых к обработке потоков из списка 
        * зарегистрированных и возвращает их в первом аргументе-массиве. 
        * Максимальное время ожидания задаёт верхний предел времени, в течение 
        * которого метод будет ждать изменения состояния потоков (но он вернёт 
        * результат сразу же, как изменится состояние хотя бы одного потока).
        * 
        * @param  mixed $tv_usec Максимальное время ожидания (в миллисекундах).
        * @return array Список потоков, готовых к обработке.
        */
        public function select($tv_usec = 100000) {
            /* Удостоверяемся, что список потоков не пуст */
            if (empty($this->_streams)) {
                $e = 'Нет ни одного зарегистрированного потока';
                throw new IO_Stream_Selector_Exception($e);
            }
            
            $read = $write = $except = array();
            
            $op_read   = IO_Stream_Interface::OPERATION_READ;
            $op_write  = IO_Stream_Interface::OPERATION_WRITE;
            $op_accept = IO_Stream_Interface::OPERATION_ACCEPT;
            
            /**
            * Раскладываем потоки по разным массивам в зависимости от 
            * интересующих операций
            */
            foreach ($this->_streams as $s)
            {
                /* Сбрасываем все флаги готовности */
                $s->resetAllReady();
                    
                if ($s->getInterest($op_read) || $s->getInterest($op_accept)) {
                    $read[] = $s->getRawStream();
                }
                
                if ($s->getInterest($op_write)) {
                    $write[] = $s->getRawStream();
                }
            }
            
            /**
            * Если в результате ни одного массива для функции stream_select() не 
            * сформировано, выходим. Она сгенерирует ошибку, если передать ей 
            * пустые массивы. Поэтому выходим.
            */
            if (0 == sizeof($read) && 0 == sizeof($write)) {
                return array();
            }
            
            /* Выполняем выбор потоков, готовых к обработке */
            $num_changed = stream_select($read, $write, $except, 0, $tv_usec);
            
            if (false === $num_changed) {
                $e = 'Ошибка при выполнении вызова stream_select()';
                throw new IO_Selector_Exception($e);
            }
            
            /**
            * Если есть готовые к обработке потоки, то устанавливаем флаги
            * готовности и собираем все потоки в один массив.
            */
            $streams = array();
            
            if ($num_changed > 0) {
                $this->_select($streams, $read,  array($op_read, $op_accept));
                $this->_select($streams, $write, array($op_write));
            }
            
            return $streams;
        }
        
        /**
        * По сырым потокам находит соответствующие объекты-потоки в списке 
        * зарегистрированных потоков и устанавливает флаги готовности к 
        * операциям.
        * 
        * @param mixed $streams     Выходной список объектов-потоков.
        * @param mixed $raw_streams Список сырых потоков.
        * @param array $ops         Список операций, к которым готов поток.
        */
        protected function _select(& $streams, $raw_streams, array $ops) {
            /* Пробегаемся по переданным потокам */
            foreach ($raw_streams as $raw_stream)
            {
                /* Получаем идентификатор потока */
                $idx = (int) $raw_stream;
                /* По нему находим объект потока */
                $stream = $this->_streams[$idx];
                
                /* Устанавливаем флаги готовности к операциям */
                foreach ($ops as $op) {
                    $stream->setReady($op);
                }
                
                /**
                * Сохраняем поток по уникальному индексу, чтобы один и тот же 
                * поток, но с двумя разными интересами (на чтение и на запись, 
                * например) не оказался дважды в одном массиве.
                */
                $streams[$idx] = $stream;
            }
        }
    }

?>