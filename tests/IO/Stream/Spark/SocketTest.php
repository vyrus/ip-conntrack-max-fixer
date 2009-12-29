<?php
    
    require_once dirname(__FILE__) . '/../../../init.php';
    
    class IO_Stream_Spark_SocketTest extends PHPUnit_Framework_TestCase {
        protected $_opts = array('host' => 'yandex.ru',
                                 'port' => 80);
                                 
        public function testCreate() {
            $spark = IO_Stream_Spark_Socket::create($this->_opts);
            $this->assertType('IO_Stream_Spark_Socket', $spark);
        }
        
        public function testStream() {
            $spark = IO_Stream_Spark_Socket::create($this->_opts);
            
            $this->assertTrue($spark->ignite());
            $this->assertType('resource', $spark->getStream());
        }
        
        public function testInfo() {
            $spark = IO_Stream_Spark_Socket::create($this->_opts);
            $spark->ignite();
            
            $name = $spark->getName();
            $this->assertType('string', $name);
            
            $name = $spark->getName(true);
            $this->assertType('string', $name);
            $this->assertStringEndsWith(':' . $this->_opts['port'], $name);
            
            $info = $spark->getInfo();
            $this->assertType('array', $info);
        }
    }
    
?>