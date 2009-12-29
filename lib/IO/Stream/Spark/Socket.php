<?php
    
    /**
    * Класс искры с сокетами для обёртки потоков.
    * @todo Нормально откомментить.
    */
    class IO_Stream_Spark_Socket implements IO_Stream_Spark_Interface {
        /**
        * Поток сокета.
        * 
        * @var resource
        */
        protected $_stream;
        
        /**
        * @var array
        */
        protected $_info = array();
        
        /**
        * Опции объекта.
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
            'enable_profiler' => false,
            'transport' => 'tcp',
            'host' => null,
            'port' => null,
            'connect_timeout' => 5
        );
        
        /**
        * Инициализация искры.
        * 
        * @param array|Options $options Опции объекта.
        * @return IO_Stream_Spark_Socket
        */
        public function __construct($options = null) {
            $this->_opts = Options::create($this->_default_options);
            
            if (null !== $options) {
                $this->_opts->apply($options);
            }
        }
        
        /**
        * Создание новой искры.
        * 
        * @param array|Options $options Опции объекта.
        * @return IO_Stream_Spark_Socket Fluent interface.
        */
        public static function create($options = null) {
            return new self($options);
        }
        
        /**
        * Открытие сокета.
        * 
        * @return boolean
        * @throws IO_Stream_Spark_Socket_Exception
        */
        public function ignite() {
            $url = sprintf('%s://%s:%d', $this->_opts->transport,
                                         $this->_opts->host,
                                         $this->_opts->port);
            $timeout = $this->_opts->connect_timeout;
                                                  
            /**
            * @todo Попробовать сделать подключение асинхронным
            * (STREAM_CLIENT_ASYNC_CONNECT).
            */
            $this->_stream = stream_socket_client($url, $errno, $errstr,
                                                  $timeout);
            
            if (false === $this->_stream)
            {
                $e = 'Error connecting %s: %s - %s';
                $e = sprintf($url, $errno, $errstr, $e);
                             
                throw new IO_Stream_Spark_Socket_Exception($e);
            }
            
            return true;
        }
        
        /**
        * Возвращает "сырой" поток.
        * 
        * @return resource
        */
        public function getStream() {
            return $this->_stream;
        }
        
        /**
        * Возвращает информацию о локальном и удалённом сокетах. Обёртка для
        * stream_socket_get_name().
        * 
        * @param mixed $want_peer Передать true для получения данных об удалённом сокете.
        * @return string
        */
        public function getName($want_peer = false) {
            return stream_socket_get_name($this->_stream, $want_peer);
        }
        
        /**
        * Получение информации о локальном и удалённом сокетах. В отличие от 
        * getName() возвращает данные сразу о двух сокетах в виде массива, плюс
        * кеширует их.
        * 
        * @return array
        */
        public function getInfo() {
            if (empty($this->_info))
            {
                $local  = explode(':', $this->getName(false));
                $remote = explode(':', $this->getName(true));
                
                $this->_info = array
                (
                    'local' => array(
                        'host' => $local[0],
                        'port' => $local[1]
                    ),
                    
                    'remote' => array(
                        'host' => $remote[0],
                        'port' => $remote[1]
                    )
                );
            }
            
            return $this->_info;
        }
    }
    
?>
