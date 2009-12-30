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
        * Установка/получение искры, получение потока.
        */
        public function testSetGetSpark() {
            $stream = IO_Stream::create();
            
            $spark = $this->getMock('IO_Stream_Spark_Interface');
            
            $spark->expects($this->once())
                  ->method('getStream')
                  ->will($this->returnValue(fopen('php://stdin', 'r')));
                  
            $stream->setSpark($spark);
            $this->assertEquals($spark, $stream->getSpark());
            
            $this->assertType('resource', $stream->getStream());
            $this->assertType('int', $stream->getStreamId());
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
        
        /**
        * Работа с флагами операций потока.
        */
        public function testOpFlags() {
            $stream = IO_Stream::create();
            
            $read   = IO_Stream::OPERATION_READ;
            $write  = IO_Stream::OPERATION_WRITE;
            $accept = IO_Stream::OPERATION_ACCEPT;
            
            $this->assertFalse($stream->getInterest($read));
            $this->assertFalse($stream->getInterest($write));
            $this->assertFalse($stream->getInterest($accept));
            
            $this->assertFalse($stream->getReady($read));
            $this->assertFalse($stream->getReady($write));
            $this->assertFalse($stream->getReady($accept));
            
            $stream->setInterest($read);
            $stream->setInterest($write);
            $stream->setInterest($accept);
            
            $this->assertTrue($stream->getInterest($read));
            $this->assertTrue($stream->getInterest($write));
            $this->assertTrue($stream->getInterest($accept));
            
            $stream->setReady($read);
            $stream->setReady($write);
            $stream->setReady($accept);
            
            $this->assertTrue($stream->getReady($read));
            $this->assertTrue($stream->getReady($write));
            $this->assertTrue($stream->getReady($accept));
            
            $stream->resetAllInterest();
            
            $this->assertFalse($stream->getInterest($read));
            $this->assertFalse($stream->getInterest($write));
            $this->assertFalse($stream->getInterest($accept));
            
            $stream->resetAllReady();
            
            $this->assertFalse($stream->getReady($read));
            $this->assertFalse($stream->getReady($write));
            $this->assertFalse($stream->getReady($accept));
        }
    }
    
?>