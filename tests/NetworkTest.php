<?php
    
    require_once dirname(__FILE__) . '/init.php';
    
    class NetworkTest extends PHPUnit_Framework_TestCase {
        /**
        * @var int
        */
        protected $_read_count = 0;
        
        /**
        * @var int
        */
        protected $_write_count = 0;
        
        /**
        * Тест создания службы сети и регистрации и отмены регистрации потоков.
        */
        public function testCreateRegUnregStream() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('Network_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $selector = $this->getMock('IO_Stream_Selector_Interface');
            $stream   = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            /* Один раз будет создан селектор потоков */
            $context->expects($this->once())
                    ->method('createStreamSelector')
                    ->will($this->returnValue($selector));            
            
            /* Один раз будет вызван метод регистрации потока */
            $selector->expects($this->once())
                     ->method('register')
                     ->with($this->equalTo($stream));
                     
            /* И один раз метод дерегистрации потока */
            $selector->expects($this->once())
                     ->method('unregister')
                     ->with($this->equalTo($stream));
            
            $net = Network::create($context);
            
            $net->registerStream($stream);
            $net->unregisterStream($stream);
        }
        
        /**
        * Тест неудачной обработки потоков - селектор не вернёт ни одного 
        * потока.
        */
        public function testDispatchFailWithSelect() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('Network_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $selector = $this->getMock('IO_Stream_Selector_Interface');
            $stream   = $this->getMock('IO_Stream_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                    
            /* И один раз будет создан селектор потоков */
            $context->expects($this->once())
                    ->method('createStreamSelector')
                    ->will($this->returnValue($selector));
            
            /* Один раз будет вызван метод регистрации потока */
            $selector->expects($this->once())
                     ->method('register')
                     ->with($this->equalTo($stream));
            
            /* Селектор не вернёт ни одного потока, готового к обработке */
            $selector->expects($this->once())
                     ->method('select')
                     ->will($this->returnValue(array()));
            
            $net = Network::create($context);
            $net->registerStream($stream);
            
            $net->dispatchStreams();
        }
        
        /**
        * Тест неудачной обработки потоков - поток закрылся.
        */
        public function testDispatchFailWithOpen() {
            /* Настройка заглушек объектов */
            $context  = $this->getMock('Network_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $selector = $this->getMock('IO_Stream_Selector_Interface');
            $stream   = $this->getMock('IO_Stream_Buffered_Interface');
            $listener = $this->getMock('IO_Stream_Listener_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            /* Один раз будет создан селектор потоков */
            $context->expects($this->once())
                    ->method('createStreamSelector')
                    ->will($this->returnValue($selector));
            
            /* Один раз будет вызван метод регистрации потока */
            $selector->expects($this->once())
                     ->method('register')
                     ->with($this->equalTo($stream));
            
            /* Селектор вернёт 1 поток, готовый к обработке */
            $selector->expects($this->once())
                     ->method('select')
                     ->will($this->returnValue(array($stream)));
                     
            /* Один раз у потока будет запрошен обработчик событий */
            $stream->expects($this->once())
                   ->method('getListener')
                   ->will($this->returnValue($listener));
            
            /* Проверка, закрылся ли поток или нет, вернёт false */
            $stream->expects($this->once())
                   ->method('isOpen')
                   ->will($this->returnValue(false));
            
            /* Будет вызван обработчик события закрытия потока */
            $listener->expects($this->once())
                     ->method('onStreamClose')
                     ->with($this->equalTo($stream));
            
            $net = Network::create($context);
            $net->registerStream($stream);
            
            $net->dispatchStreams();
        }
        
        /**
        * Тест неудачной обработки потоков - поток сгенерировал исключение.
        */
        public function testDispatchFailWithStreamException() {
            /* Настройка заглушек объектов */
            $context  = $this->getMock('Network_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $selector = $this->getMock('IO_Stream_Selector_Interface');
            $stream   = $this->getMock('IO_Stream_Buffered_Interface');
            $listener = $this->getMock('IO_Stream_Listener_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            /* Один раз будет создан селектор потоков */
            $context->expects($this->once())
                    ->method('createStreamSelector')
                    ->will($this->returnValue($selector));
            
            /* Один раз будет вызван метод регистрации потока */
            $selector->expects($this->once())
                     ->method('register')
                     ->with($this->equalTo($stream));
            
            /* Селектор вернёт 1 поток, готовый к обработке */
            $selector->expects($this->once())
                     ->method('select')
                     ->will($this->returnValue(array($stream)));
                     
            /* Один раз у потока будет запрошен обработчик событий */
            $stream->expects($this->once())
                   ->method('getListener')
                   ->will($this->returnValue($listener));
            
            /* Проверка, закрылся ли поток или нет, вернёт true */
            $stream->expects($this->once())
                   ->method('isOpen')
                   ->will($this->returnValue(true));
                     
            /* Поток скажет, что готов к операции */
            $stream->expects($this->once())
                   ->method('getReady')
                   ->will($this->returnValue(true));
                   
            /* При попытке чтения поток сгенерирует исключение */
            $stream->expects($this->once())
                   ->method('read')
                   ->will($this->throwException(new IO_Stream_Exception('e')));
            
            /* И будет вызван обработчик события ошибки потока */
            $listener->expects($this->once())
                     ->method('onStreamError')
                     ->with($this->equalTo($stream), $this->equalTo('e'));
            
            $net = Network::create($context);
            $net->registerStream($stream);
            
            $net->dispatchStreams();
        }
        
        /**
        * Тест удачной обработки потоков.
        */
        public function testDispatchSuccess() {
            /* Настройка заглушек объектов */
            $context  = $this->getMock('Network_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $selector = $this->getMock('IO_Stream_Selector_Interface');
            $stream   = $this->getMock('IO_Stream_Buffered_Interface');
            $listener = $this->getMock('IO_Stream_Listener_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            /* Один раз будет создан селектор потоков */
            $context->expects($this->once())
                    ->method('createStreamSelector')
                    ->will($this->returnValue($selector));
            
            /* Один раз будет вызван метод регистрации потока */
            $selector->expects($this->once())
                     ->method('register')
                     ->with($this->equalTo($stream));
            
            /* Селектор вернёт 1 поток, готовый к обработке */
            $selector->expects($this->once())
                     ->method('select')
                     ->will($this->returnValue(array($stream)));
                     
            /* Один раз у потока будет запрошен обработчик событий */
            $stream->expects($this->once())
                   ->method('getListener')
                   ->will($this->returnValue($listener));
            
            /* Проверка, закрылся ли поток или нет, вернёт true */
            $stream->expects($this->once())
                   ->method('isOpen')
                   ->will($this->returnValue(true));
                     
            /* Поток дважды скажет, что готов к операции */
            $stream->expects($this->exactly(2))
                   ->method('getReady')
                   ->will($this->returnValue(true));
                   
            $callback = array($this, 'readCallback');
                   
            /* Будет два цикла чтения из потока */
            $stream->expects($this->exactly(2))
                   ->method('read')
                   ->will($this->returnCallback($callback));
                   
            $callback = array($this, 'writeCallback');
                   
            /* И два цикла записи в поток */
            $stream->expects($this->exactly(2))
                   ->method('write')
                   ->will($this->returnCallback($callback));
            
            /* Будет вызван обработчик события чтения из потока */
            $listener->expects($this->once())
                     ->method('onStreamRead')
                     ->with($this->equalTo($stream), $this->equalTo(4096));
                     
            /* И обработчик события записи в поток */
            $listener->expects($this->once())
                     ->method('onStreamWrite')
                     ->with($this->equalTo($stream), $this->equalTo(4096));
            
            $net = Network::create($context);
            $net->registerStream($stream);
            
            $net->dispatchStreams();
        }
        
        /**
        * Callback метода чтения из потока. При первом чтении возвращает 4096, 
        * а при втором false.
        */
        public function readCallback() {
            $this->_read_count++;
            
            if (1 === $this->_read_count) {
                return 4096;
            } else {
                return false;
            }
        }
        
        /**
        * Callback метода записи в поток. При первой записи возвращает 4096, а 
        * при второй false.
        */
        public function writeCallback() {
            $this->_write_count++;
            
            if (1 === $this->_write_count) {
                return 4096;
            } else {
                return false;
            }
        }
    }
    
?>