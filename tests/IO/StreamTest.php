<?php
    
    require_once dirname(__FILE__) . '/../../init.php';
    
    class IO_StreamTest extends PHPUnit_Framework_TestCase {
        /**
        * Создание объекта.
        */
        public function testCreate() {
            $stream = IO_Stream::create();
            $this->assertType('IO_Stream', $stream);
        }
        
        /**
        * Установка/получение искры.
        */
        public function testSetGetSpark() {
            $stream = IO_Stream::create();
            
            $spark = $this->getMock('IO_Stream_Spark_Interface');
            
            $spark->expects($this->once())
                  ->method('getStream')
                  ->will($this->returnValue(fopen('php://stdin', 'r')));
                  
            $stream->setSpark($spark);
            $this->assertEquals($spark, $stream->getSpark());
        }
        
        /**
        * Установка/получение слушателя.
        */
        public function testSetGetListener() {
            $stream = IO_Stream::create();
            
            $listener = $this->getMock('IO_Stream_Listener_Interface');
            
            $stream->setListener($listener);
            $this->assertEquals($listener, $stream->getListener());
        }
    }
    
?>