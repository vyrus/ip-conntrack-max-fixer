<?php
    
    require_once dirname(__FILE__) . '/../../init.php';

    class IO_Stream_BufferedTest extends PHPUnit_Framework_TestCase {
        /**
        * Что можем, то и тестируем... =)
        */
        public function testGeneric() {
            /* Создание заглушек объектов */
            $context  = $this->getMock('IO_Stream_Buffered_Context_Interface');
            $opts     = $this->getMock('Options_Interface');
            $buffer   = $this->getMock('IO_Buffer_Interface');
            
            /* Один раз будет создан новый объект настроек */
            $context->expects($this->once())
                    ->method('createOptions')
                    ->will($this->returnValue($opts));
            
            /* Один раз будут установлены опции */
            $opts->expects($this->once())
                 ->method('apply');
                 
            /* И два раза будет создан новый буфер */
            $context->expects($this->exactly(2))
                    ->method('createBuffer')
                    ->will($this->returnValue($buffer));
                 
            $stream = IO_Stream_Buffered::create($context);
            
            $this->assertType('IO_Stream_Buffered', $stream);
            
            $this->assertEquals($buffer, $stream->getReadBuffer());
            $this->assertEquals($buffer, $stream->getWriteBuffer());
        }
    }

?>