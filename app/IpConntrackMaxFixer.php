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
        const FOLDER = '/proc/sys/net/ipv4';
        
        /**
        * //
        * 
        * @var const
        */
        const IP_CONNTRACK_MAX = 4096;
        
        /**
        * //
        * 
        * @var Network
        */
        protected $_network;
        
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
        
        public static function create() {
            return new self();
        }
        
        public function setNetwork(Network $net) {
            $this->_network = $net;
        }
        
        public function onTelnetConnected(Telnet $telnet, IO_Stream_Abstract $stream) {
            $this->_network->registerStream($stream);
        }
        
        public function onTelnetPromt(Telnet $telnet, $promt) {
            if ($this->_stringEnds(self::CMD_PROMT, $promt))
            {
                $pos = strlen($promt) - strlen(self::CMD_PROMT);
                $this->_result = substr($promt, 0, $pos);
                $this->_result = rtrim($this->_result, CRLF);
                
                $this->fixIt($telnet);
            }
            elseif ($this->_stringEnds(self::LOGIN_PROMT, $promt)) 
            {
                $telnet->sendString('admin');
            }
            elseif ($this->_stringEnds(self::PASSWD_PROMT, $promt)) 
            {
                $telnet->sendString('admin', false);
            }
            else
            {
                return Telnet_Listener_Interface::FAILURE;
            }
            
            return Telnet_Listener_Interface::SUCCESS;
        }
        
        public function onTelnetDisconnected(Telnet $telnet, IO_Stream_Abstract $stream) {
            $this->_network->unregisterStream($stream);
            throw new Exception('Telnet disconnected');
        }
        
        public function fixIt(Telnet $telnet) {
            switch ($this->_state)
            {
                case self::STATE_START:
                    $telnet->sendString('cd ' . self::FOLDER);
                    $this->_state = self::STATE_IN_FOLDER;
                    break;
                    
                case self::STATE_IN_FOLDER:
                    $telnet->sendString('pwd');
                    $this->_state = self::STATE_FOLDER_CHECK;
                    break;
                    
                case self::STATE_FOLDER_CHECK:
                    if (self::FOLDER != $this->_result) {
                        $msg = 'Couldn\'t change folder to ' . self::FOLDER;
                        throw new Exception($msg);
                    }
                    
                    $telnet->sendString('cat ip_conntrack_max');
                    $this->_state = self::STATE_CHECK_VALUE;
                    break;
                    
                case self::STATE_CHECK_VALUE:
                    if ($this->_result >= self::IP_CONNTRACK_MAX) {
                        $msg = 'The value bigest enough is set already: ' .
                               $this->_result;
                        throw new Exception($msg);
                    }
                    
                    $cmd = 'echo ' . self::IP_CONNTRACK_MAX .
                           ' > ip_conntrack_max';
                           
                    $telnet->sendString($cmd);
                    $this->_state = self::STATE_VALUE_SET;
                    break;
                    
                case self::STATE_VALUE_SET:
                    $telnet->sendString('cat ip_conntrack_max');
                    $this->_state = self::STATE_VALUE_IS_SET;
                    break;
                    
                case self::STATE_VALUE_IS_SET:
                    if ($this->_result != self::IP_CONNTRACK_MAX) {
                        $msg = 'Couldn\'t change value ' . $this->_result .
                               ' to ' . self::IP_CONNTRACK_MAX;
                        throw new Exception($msg);
                    }
                    
                    $telnet->sendString('exit');
                    break;
            }
        }
        
        protected function _stringEnds($needle, $haystack) {
            $len = -strlen($needle);
            $test = substr($haystack, $len);
            
            return ($needle === $test);
        }
    }

?>