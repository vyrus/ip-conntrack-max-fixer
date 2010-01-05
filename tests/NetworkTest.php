<?php
    
    require_once dirname(__FILE__) . '/init.php';
    
    class NetworkTest extends PHPUnit_Framework_TestCase {
        /**
        * Тест создания экземпляра службы сети.
        */
        public function testCreate() {
            /* Настройка заглушки объекта настроек */
            $opts = $this->getMock('Options_Interface');
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            /* Настройка заглушки объекта-контекста */
            $context = $this->getMock('Network_Context_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
                    
            /* И один раз будет создан селектор потоков */
            $context->expects($this->once())
                    ->method('createStreamSelector');
            
            $net = Network::create($context);
            
            $this->assertType('Network', $net);
        }
    }
    
?>