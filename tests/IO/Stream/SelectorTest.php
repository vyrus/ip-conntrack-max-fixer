<?php
    
    require_once dirname(__FILE__) . '/../../init.php';
    
    class IO_Stream_SelectorTest extends PHPUnit_Framework_TestCase {
        /**
        * Тест создания экземпляра класса.
        */
        public function testCreate() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            $selector = IO_Stream_Selector::create($context);
            
            $this->assertType('IO_Stream_Selector', $selector);
        }
        
        /**
        * Тест учёта потоков.
        */
        public function testRegUnreg() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $stream   = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            /* И дважды будет вызван метод получение идентификатора потока */
            $stream->expects($this->exactly(2))
                   ->method('getId')
                   ->will($this->returnValue(1)); 
            
            $selector = IO_Stream_Selector::create($context);
            
            $selector->register($stream);
            $selector->unregister($stream);
        }
        
        /**
        * Тест повторной регистрации потока.
        */
        public function testRegFail() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $stream   = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            /* И дважды будет вызван метод получение идентификатора потока */
            $stream->expects($this->exactly(2))
                   ->method('getId')
                   ->will($this->returnValue(1)); 
            
            $selector = IO_Stream_Selector::create($context);
            
            $this->setExpectedException('IO_Stream_Selector_Exception');
            
            $selector->register($stream);
            $selector->register($stream);
        }
        
        /**
        * Тест отмены регистрации незарегистрированного потока.
        */
        public function testUnregFail() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $stream   = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            /* И будет вызван метод получение идентификатора потока */
            $stream->expects($this->once())
                   ->method('getId')
                   ->will($this->returnValue(1)); 
            
            $selector = IO_Stream_Selector::create($context);
            
            $this->setExpectedException('IO_Stream_Selector_Exception');
            
            $selector->unregister($stream);
        }
        
        /**
        * Тест выборки потоков.
        * 
        * @todo Нестабильный тест.
        * 
        * @todo Need some refactoring to split stream_select() call and 
        * some general selecting logic from IO_Stream_Select.
        */
        public function _testSelect() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            
            /* Создаём парочку потоков-заглушек: для чтения и для записи */
            $in_stream  = $this->getMock('IO_Stream_Interface');
            $out_stream = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
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
            
            /*       
            $out_stream->expects($this->exactly(2))
                   ->method('setReady');
            */       
            
            $selector = IO_Stream_Selector::create($context);
            
            /* Регистрируем потоки */
            $selector->register($in_stream);
            $selector->register($out_stream);
            /* И делаем выборку */
            $streams = $selector->select();
            
            /**
            * @todo Непонятно, почему 1 поток только выберется...
            */
            $this->assertType('array', $streams);
            $this->assertTrue(in_array($in_stream, $streams));
            //$this->assertTrue(in_array($out_stream, $streams));
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
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $stream   = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            /* И будет вызван метод получение идентификатора потока */
            $stream->expects($this->once())
                   ->method('getId')
                   ->will($this->returnValue(1));
                   
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
            
            $selector = IO_Stream_Selector::create($context);
            $selector->register($stream);
            
            /* Ни одного потока не должно быть выбрано */
            $this->assertEquals(array(), $selector->select());
        }
        
        /**
        * Тест выборки из пустого списка потоков.
        */
        public function testSelectOnNoStreams() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Selector_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            $selector = IO_Stream_Selector::create($context);
            
            $this->setExpectedException('IO_Stream_Selector_Exception');
            
            $selector->select();
        }
    }
    
?>