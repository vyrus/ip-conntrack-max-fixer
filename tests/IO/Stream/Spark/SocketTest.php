<?php
    
    require_once dirname(__FILE__) . '/../../../init.php';
    
    class IO_Stream_Spark_SocketTest extends PHPUnit_Framework_TestCase {
        protected $_opts = array('transport'       => 'tcp',
                                 'host'            => 'yandex.ru',
                                 'port'            => 80,
                                 'connect_timeout' => 5);
                                 
        /**
        * Тест общего цикла работы с искрой.
        */
        public function testCreateAndOptions() {
            /* Создаём заглушки объектов */
            $context = $this->getMock('IO_Stream_Spark_Context_Interface');
            $opts    = $this->getMock('Options_Interface');
            
            /* Один раз будет вызвано создание объекта опций */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
                    
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
            
            $callback = array($this, 'getCallback');
            
            /* Четыре раза искра будет обращаться за параметрами */
            $opts->expects($this->exactly(4))
                 ->method('get')
                 ->will($this->returnCallback($callback));
            
            /* Создаём искры */
            $spark = IO_Stream_Spark_Socket::create($context);
            $this->assertType('IO_Stream_Spark_Socket', $spark);
            
            /* Зажигаем! :) */
            $this->assertTrue($spark->ignite());
            $this->assertType('resource', $spark->getStream());
            
            /* Проверяем получение информации о соединении */
            $name = $spark->getName();
            $this->assertType('string', $name);
            
            $name = $spark->getName(true);
            $this->assertType('string', $name);
            $this->assertStringEndsWith(':' . $this->_opts['port'], $name);
            
            $info = $spark->getInfo();
            $this->assertType('array', $info);
        }
        
        public function getCallback($key) {
            return $this->_opts[$key];
        }
    }
    
?>