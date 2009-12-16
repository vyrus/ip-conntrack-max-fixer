<?php

    class IO_Stream_Buffered_Socket extends IO_Stream_Buffered {
        /**
        * @var array
        */
        protected $info = array();
        
        /**
        * @var array
        */
        protected $default_options = array(
            'enable_profiler' => false,
            'transport' => 'tcp',
            'host' => null,
            'port' => null,
            'connect_timeout' => 5,
            'read_write_timeout' => 5
        );

        public function open() {
            return $this->connect();
        }
                            
        public function connect() {
            $url = sprintf('%s://%s:%d', $this->options->transport,
                                         $this->options->host,
                                         $this->options->port);
            $timeout = $this->options->connect_timeout;
                                                  
            /**
            * Сделать подключение асинхронным.
            */
            if (false === ($this->stream = stream_socket_client($url, $errno, $errstr, $timeout /* STREAM_CLIENT_ASYNC_CONNECT */))) {
                throw new IO_Stream_Buffered_Socket_Exception('Error connecting ' . $url . ': ' . $errno . ' - ' . $errstr);
            }
            
            return true;
        } 
        
        public function getName($want_peer = false) {
            return stream_socket_get_name($this->stream, $want_peer);
        }
        
        public function info() {
            if (empty($this->info)) {
                $local  = explode(':', $this->getName(false));
                $remote = explode(':', $this->getName(true));
                
                $this->info = array
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
            
            return $this->info;
        }
    }               

?>