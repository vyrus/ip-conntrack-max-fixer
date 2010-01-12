<?php
    
    require_once dirname(__FILE__) . '/../init.php';
    
    class IO_StreamTest extends PHPUnit_Framework_TestCase {
        /**
        * Создание объекта.
        */
        public function testCreate() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            $stream = IO_Stream::create($context);
            $this->assertType('IO_Stream', $stream);
        }
        
        /**
        * Установка/получение искры, получение потока.
        */
        public function testSetGetSpark() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $spark    = $this->getMock('IO_Stream_Spark_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            /* Один раз у искры будет запрошен сырой поток */
            $spark->expects($this->once())
                  ->method('getStream')
                  ->will($this->returnValue(fopen('php://stdin', 'r')));
                 
            $stream = IO_Stream::create($context);
                  
            /* Fluent interface */
            $this->assertEquals($stream, $stream->setSpark($spark));
            $this->assertEquals($spark, $stream->getSpark());
            
            $this->assertType('resource', $stream->getRawStream());
            $this->assertType('int', $stream->getId());
        }
        
        /**
        * Установка/получение слушателя.
        */
        public function testSetGetListener() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $listener = $this->getMock('IO_Stream_Listener_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            $stream = IO_Stream::create($context);
            
            /* Fluent interface */
            $this->assertEquals($stream, $stream->setListener($listener));
            $this->assertEquals($listener, $stream->getListener());
        }
        
        /**
        * Работа с флагами операций потока.
        */
        public function testOpFlags() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            $stream = IO_Stream::create($context);
            
            $read   = IO_Stream::OPERATION_READ;
            $write  = IO_Stream::OPERATION_WRITE;
            $accept = IO_Stream::OPERATION_ACCEPT;
            
            /* Ни один флаг интереса не установлен */
            $this->assertFalse($stream->getInterest($read));
            $this->assertFalse($stream->getInterest($write));
            $this->assertFalse($stream->getInterest($accept));
            
            /* Также ни один флаг готовности не установлен */
            $this->assertFalse($stream->getReady($read));
            $this->assertFalse($stream->getReady($write));
            $this->assertFalse($stream->getReady($accept));
            
            /* Устанавливаем все флаги интереса и проверяем fluent interface */
            $this->assertEquals($stream, $stream->setInterest($read));
            $this->assertEquals($stream, $stream->setInterest($write));
            $this->assertEquals($stream, $stream->setInterest($accept));
            
            /* Все флаги интереса должны быть установлены */
            $this->assertTrue($stream->getInterest($read));
            $this->assertTrue($stream->getInterest($write));
            $this->assertTrue($stream->getInterest($accept));
            
            /* Устанавливаем все флаги готовности + fluent interface */
            $this->assertEquals($stream, $stream->setReady($read));
            $this->assertEquals($stream, $stream->setReady($write));
            $this->assertEquals($stream, $stream->setReady($accept));
            
            /* Все флаги готовности должны быть установлены */
            $this->assertTrue($stream->getReady($read));
            $this->assertTrue($stream->getReady($write));
            $this->assertTrue($stream->getReady($accept));
            
            /* Проверяем сброс отдельного интереса + fluent interface */
            $this->assertEquals($stream, $stream->resetInterest($read));
            $this->assertFalse($stream->getInterest($read));
            
            /* Сбрасываем все флаги интереса и проверяем fluent interface */
            $this->assertEquals($stream, $stream->resetAllInterest());
            
            /* Все флаги интереса должны быть сброшены */
            $this->assertFalse($stream->getInterest($read));
            $this->assertFalse($stream->getInterest($write));
            $this->assertFalse($stream->getInterest($accept));
            
            /* Сбрасываем все флаг готовности и проверяем fluent interface */
            $this->assertEquals($stream, $stream->resetAllReady());
            
            /* Все флаги готовности должны быть сброшены */
            $this->assertFalse($stream->getReady($read));
            $this->assertFalse($stream->getReady($write));
            $this->assertFalse($stream->getReady($accept));
        }
    }
    
?>