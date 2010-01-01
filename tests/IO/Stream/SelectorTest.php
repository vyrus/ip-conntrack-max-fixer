<?php
    
    require_once dirname(__FILE__) . '/../../init.php';
    
    class IO_Stream_SelectorTest extends PHPUnit_Framework_TestCase {
        /**
        * Тест создания экземпляра класса.
        */
        public function testCreate() {
            $selector = IO_Stream_Selector::create();
            
            $this->assertType('IO_Stream_Selector', $selector);
        }
        
        /**
        * Тест учёта потоков.
        */
        public function testRegUnreg() {
            $stream = $this->getMock('IO_Stream_Interface');
            
            $stream->expects($this->exactly(2))
                   ->method('getId')
                   ->will($this->returnValue(1)); 
            
            $selector = IO_Stream_Selector::create();
            $selector->register($stream);
            $selector->unregister($stream);
        }
        
        /**
        * Тест повторной регистрации потока.
        */
        public function testRegFail() {
            $stream = $this->getMock('IO_Stream_Interface');
            
            $stream->expects($this->exactly(2))
                   ->method('getId')
                   ->will($this->returnValue(1)); 
            
            $selector = IO_Stream_Selector::create();
            
            $this->setExpectedException('IO_Stream_Selector_Exception');
            
            $selector->register($stream);
            $selector->register($stream);
        }
        
        /**
        * Тест отмены регистрации незарегистрированного потока.
        */
        public function testUnregFail() {
            $stream = $this->getMock('IO_Stream_Interface');
            
            $stream->expects($this->exactly(1))
                   ->method('getId')
                   ->will($this->returnValue(1)); 
            
            $selector = IO_Stream_Selector::create();
            
            $this->setExpectedException('IO_Stream_Selector_Exception');
            
            $selector->unregister($stream);
        }
        
        /**
        * Тест выборки потоков.
        * @todo Need some refactoring to split stream_select() call and 
        * some general selecting logic from IO_Stream_Select.
        */
        public function _testSelect() {
            $selector = IO_Stream_Selector::create();
            
            /* Создаём парочку потоков-заглушек: для чтения и для записи */
            $in_stream = $this->getMock('IO_Stream_Interface');
            $out_stream = $this->getMock('IO_Stream_Interface');
            
            /* Открываем реальные потоки, иначе селектор не сработает */
            /**
            * @todo IO_Stream_Selector_Select, IO_Stream_Selector_Epoll, ...
            */
            $in_stream_raw  = fopen('php://stdin', 'r');
            $out_stream_raw = fopen('php://stdout', 'w');
            
            /* И узнаём идентификаторы этих потоков */
            $in_stream_id  = (int) $in_stream_raw;
            $out_stream_id = (int) $out_stream_raw;
            
            /* Идентификаторы потоков */
            $in_stream->expects($this->once())
                   ->method('getId')
                   ->will($this->returnValue($in_stream_id));
                   
            $out_stream->expects($this->once())
                   ->method('getId')
                   ->will($this->returnValue($out_stream_id));
                   
            /* Сброс всех флагов готовности */       
            $in_stream->expects($this->once())
                   ->method('resetAllReady');
                   
            $out_stream->expects($this->once())
                   ->method('resetAllReady');
            
            /* Флаги заинтересованности в операциях */
            $callback = array($this, 'getInterestCallback_In');
                   
            $in_stream->expects($this->exactly(2))
                   ->method('getInterest')
                   ->will($this->returnCallback($callback));
                   
            $callback = array($this, 'getInterestCallback_Out');
                   
            $out_stream->expects($this->exactly(3))
                   ->method('getInterest')
                   ->will($this->returnCallback($callback));
            
            /* Сырые потоки */       
            $in_stream->expects($this->once())
                   ->method('getRawStream')
                   ->will($this->returnValue($in_stream_raw));
                   
            $out_stream->expects($this->once())
                   ->method('getRawStream')
                   ->will($this->returnValue($out_stream_raw));
            
            /* Установка флагов готовности */       
            $in_stream->expects($this->exactly(2))
                   ->method('setReady');
                   
            $out_stream->expects($this->exactly(2))
                   ->method('setReady');
                   
            /* Регистрируем потоки */
            $selector->register($in_stream);
            $selector->register($out_stream);
            /* И делаем выборку */
            $result = $selector->select($streams, 200000);
            
            $this->assertEquals(2, $result);
            $this->assertTrue(in_array($in_stream, $streams));
            $this->assertTrue(in_array($out_stream, $streams));
        }
        
        public function getInterestCallback_In($op) {
            switch ($op)
            {
                case IO_Stream_Interface::OPERATION_READ:
                    $result = true;
                    break;
                    
                case IO_Stream_Interface::OPERATION_WRITE:
                    $result = false;
                    break;
            }
            
            return $result;
        }
        
        public function getInterestCallback_Out($op) {
            switch ($op)
            {
                case IO_Stream_Interface::OPERATION_READ:
                    $result = false;
                    break;
                    
                case IO_Stream_Interface::OPERATION_ACCEPT:
                    $result = false;
                    break;
                    
                case IO_Stream_Interface::OPERATION_WRITE:
                    $result = true;
                    break;
            }
            
            return $result;
        }
        
        /**
        * Тест выборки из одного ни в чём не заинтересованного потока.
        */
        public function testSelectNoIinterest() {
            $selector = IO_Stream_Selector::create();
            
            /* Настраиваем поток-заглушку */
            $stream = $this->getMock('IO_Stream_Interface');
            
            /* Идентификатор потока */
            $stream->expects($this->once())
                   ->method('getId')
                   ->will($this->returnValue(1));
            
            /* Сброс всех флагов готовности */
            $stream->expects($this->once())
                   ->method('resetAllReady');
            
            /* Никакого интереса к операциям */
            $stream->expects($this->exactly(3))
                   ->method('getInterest')
                   ->will($this->returnValue(false));
                   
            $selector->register($stream);
            
            /* Ни одного потока не должно быть выбрано */
            $this->assertEquals(0, $selector->select($streams));
        }
        
        /**
        * Тест выборки из пустого списка потоков.
        */
        public function testSelectOnNoStreams() {
            $selector = IO_Stream_Selector::create();
            
            $this->setExpectedException('IO_Stream_Selector_Exception');
            
            $selector->select($streams);
        }
    }
    
?>