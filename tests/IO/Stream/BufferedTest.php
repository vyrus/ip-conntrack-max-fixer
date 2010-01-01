<?php
    
    require_once dirname(__FILE__) . '/../../init.php';

    class IO_Stream_BufferedTest extends PHPUnit_Framework_TestCase {
        public function testGeneric() {
            $stream = IO_Stream_Buffered::create();
            
            $this->assertType('IO_Stream_Buffered', $stream);
            $this->assertType('IO_Buffer_Interface', $stream->getReadBuffer());
            $this->assertType('IO_Buffer_Interface', $stream->getWriteBuffer());
        }
    }

?>