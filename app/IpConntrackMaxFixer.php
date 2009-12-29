<?php

    class IpConntrackMaxFixer implements Telnet_Listener_Interface {
        /**
        * //
        * 
        * @var const
        */
        const LOGIN_PROMT = 'login: ';
        
        /**
        * //
        * 
        * @var const
        */
        const PASSWD_PROMT = 'Password: ';
        
        /**
        * //
        * 
        * @var const
        */
        const CMD_PROMT = '# ';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_START = 'start';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_IN_FOLDER = 'in-folder';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_FOLDER_CHECK = 'folder-check';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_CHECK_VALUE = 'check-value';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_VALUE_SET = 'value-set';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_VALUE_IS_SET = 'value-is-set';
        
        /**
        * //
        * 
        * @var const
        */
        const STATE_FINISH = 'finish';
        
        /**
        * //
        * 
        * @var const
        */
        const FOLDER = '/proc/sys/net/ipv4';
        
        /**
        * //
        * 
        * @var Network
        */
        protected $_network;
        
        /**
        * //
        * 
        * @var Telnet
        */
        protected $_telnet;
        
        /**
        * //
        * 
        * @var string
        */
        protected $_host;
        
        /**
        * //
        * 
        * @var string
        */
        protected $_login = 'admin';
        
        /**
        * //
        * 
        * @var string
        */
        protected $_passwd = 'admin';
        
        /**
        * //
        * 
        * @var int
        */
        protected $_ip_conntrack_max = 4096;
        
        /**
        * //
        * 
        * @var boolean
        */
        protected $_logged_in = false;
        
        /**
        * //
        * 
        * @var mixed
        */
        protected $_state = self::STATE_START;
        
        /**
        * //
        * 
        * @var string
        */
        protected $_result;
        
        /**
        * //
        * 
        * @var boolean
        */
        protected $_continue = true;
        
        public static function create() {
            return new self();
        }
        
        public function setNetwork(Network $net) {
            $this->_network = $net;
            return $this;
        }
        
        public function setTelnet(Telnet $telnet) {
            $this->_telnet = $telnet;
            return $this;
        }
        
        public function setHost($host) {
            $this->_host = $host;
            return $this;
        }
        
        public function setLogin($login) {
            $this->_login = $login;
            return $this;
        }
        
        public function setPasswd($passwd) {
            $this->_passwd = $passwd;
            return $this;
        }
        
        public function setConntrackMax($max) {
            $this->_ip_conntrack_max = $max;
            return $this;
        }                
        
        public function fixIt() {
            $this->_telnet->setListener($this);
            $this->_telnet->connect($this->_host);
            
            while($this->_continue) {
                $this->_network->dispatchStreams();
            }
        }
        
        public function onTelnetConnected(Telnet $telnet,
                                          IO_Stream_Abstract $stream) {
            $this->_network->registerStream($stream);
        }
        
        public function onTelnetPromt(Telnet $telnet, $promt) {
            if ($this->_stringEnds(self::CMD_PROMT, $promt))
            {
                $pos = strlen($promt) - strlen(self::CMD_PROMT);
                $this->_result = substr($promt, 0, $pos);
                $this->_result = rtrim($this->_result, CRLF);
                
                $this->_fixIt($telnet);
            }
            elseif ($this->_stringEnds(self::LOGIN_PROMT, $promt)) 
            {
                $this->_print('Sending login...', true);
                
                $telnet->sendString($this->_login);
            }
            elseif ($this->_stringEnds(self::PASSWD_PROMT, $promt)) 
            {
                $this->_print('Sending password...', true);
                
                $telnet->sendString($this->_passwd, false);
            }
            else
            {
                return Telnet_Listener_Interface::FAILURE;
            }
            
            return Telnet_Listener_Interface::SUCCESS;
        }
        
        public function onTelnetDisconnected(Telnet $telnet,
                                             IO_Stream_Abstract $stream) {
            $this->_network->unregisterStream($stream);
                 
            if (self::STATE_FINISH !== $this->_state) {
                $msg = 'Something went wrong :(';
            } else {
                $msg = 'Everything is ok :)';
            }
            
            $this->_print($msg);
            $this->_continue = false;
        }
        
        protected function _fixIt(Telnet $telnet) {
            switch ($this->_state)
            {
                case self::STATE_START:
                    $this->_print('Changing folder...');
                    
                    $telnet->sendString('cd ' . self::FOLDER);
                    $this->_state = self::STATE_IN_FOLDER;
                    break;
                    
                case self::STATE_IN_FOLDER:
                    $telnet->sendString('pwd');
                    $this->_state = self::STATE_FOLDER_CHECK;
                    break;
                    
                case self::STATE_FOLDER_CHECK:
                    if (self::FOLDER != $this->_result)
                    {
                        $msg = 'Couldn\'t change folder to ' . self::FOLDER;
                        $this->_print(CRLF . $msg, true);
                        
                        $this->_telnet->disconnect();
                        return;
                    }
                    
                    $this->_print(' Ok', true);
                    $this->_print('Checking ip_conntrack_max value...');
                                              
                    $telnet->sendString('cat ip_conntrack_max');
                    $this->_state = self::STATE_CHECK_VALUE;
                    break;
                    
                case self::STATE_CHECK_VALUE:
                    if ($this->_result >= $this->_ip_conntrack_max)
                    {
                        $msg = 'The value bigest enough is set already: ';
                        $this->_print(CRLF . $msg . $this->_result, true);
                        
                        $this->_telnet->disconnect();
                        return;
                    }
                    
                    $this->_print(' Ok', true);
                    $this->_print('Fixing ip_conntrack_max value...');
                    
                    $cmd = 'echo ' . $this->_ip_conntrack_max .
                           ' > ip_conntrack_max';
                           
                    $telnet->sendString($cmd);
                    $this->_state = self::STATE_VALUE_SET;
                    break;
                    
                case self::STATE_VALUE_SET:
                    $telnet->sendString('cat ip_conntrack_max');
                    $this->_state = self::STATE_VALUE_IS_SET;
                    break;
                    
                case self::STATE_VALUE_IS_SET:
                    if ($this->_result != $this->_ip_conntrack_max)
                    {
                        $msg = 'Couldn\'t change value ' . $this->_result .
                               ' to ' . $this->_ip_conntrack_max;
                        $this->_print(CRLF . $msg, true);
                        
                        $this->_telnet->disconnect();
                        return;
                    }
                    
                    $this->_print(' Ok', true);
                    
                    $telnet->sendString('exit');
                    $this->_state = self::STATE_FINISH;
                    break;
            }
        }
        
        protected function _print($msg, $new_line = false) {
            echo $msg . ($new_line ? CRLF : '');
        }
        
        protected function _stringEnds($needle, $haystack) {
            $len = -strlen($needle);
            $test = substr($haystack, $len);
            
            return ($needle === $test);
        }
    }

?>