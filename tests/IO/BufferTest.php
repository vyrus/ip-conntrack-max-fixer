<?php
    
    require_once dirname(__FILE__) . '/../../init.php';
    
    class IO_BufferTest extends PHPUnit_Framework_TestCase {
        protected $_data = '1234567';
        
        /**
        * Тест чтения-записи.
        */
        public function testReadWrite() {
            $buf = IO_Buffer::create();
            $data_len = strlen($this->_data);
            
            /* Записываем данные */
            $bytes_written = $buf->write($this->_data);
            $this->assertEquals($data_len, $bytes_written);
            
            /* Сбрасываем позицию указателя на начало */
            $buf->rewind();
            
            /* Читаем данные большим куском */
            $read_data = $buf->read();
            $this->assertEquals($this->_data, $read_data);
            
            $buf->rewind();
            
            /* Читаем данные отдельными порциями */
            $bytes = floor($data_len / 2);
            
            /* Раз */
            $test_data = substr($this->_data, 0, $bytes);
            $read_data = $buf->read($bytes);
            $this->assertEquals($test_data, $read_data);
            
            /* Два */
            $test_data = substr($this->_data, $bytes);
            $read_data = $buf->read();
            $this->assertEquals($test_data, $read_data);
            
            $buf->rewind();
            
            /* Читаем и удаляем прочитанный блок из буфера */
            $read_data = $buf->read($bytes);
            $pos = $buf->release($bytes);
            
            /* Сверяем позиции */
            $this->assertEquals($bytes, $pos);
            $this->assertEquals(0, $buf->getOffset());
            
            /* Проверяем, сколько данных осталось в буфере */
            $left_data = substr($this->_data, $bytes);
            $left_len = strlen($left_data);
            $this->assertEquals($left_len, $buf->getLength());
            
            /* Забираем оставшиеся данные */
            $read_data = $buf->read();
            $pos = $buf->release();
            
            $this->assertEquals($left_len, $pos);
            $this->assertEquals(0, $buf->getLength());
        }
        
        /**
        * Тест работы указателя в буфере.
        */
        public function testOffset() {
            $buf = IO_Buffer::create();
            $data_len = strlen($this->_data);
            
            /* Ожидаем нулевую позицию указателя */
            $this->assertEquals(0, $buf->getOffset());
            
            /* Записываем данные */
            $bytes_written = $buf->write($this->_data);
            $this->assertEquals($data_len, $bytes_written);
            
            /* Теперь указатель должен быть на конце буфера */
            $this->assertEquals($data_len, $buf->getOffset());
            
            /* И длина буфера должна соответсвовать длине записанного блока */
            $this->assertEquals($data_len, $buf->getLength());
            
            /* Сбрасываем позицию указателя на начало */
            $buf->rewind();
            $this->assertEquals(0, $buf->getOffset());
            
            /* Переводим указатель на заданную позицию */
            $offset = floor($data_len / 2);
            $buf->seek($offset);
            $this->assertEquals($offset, $buf->getOffset());
        }
        
        /**
        * Тест некорректной работы с указателем.
        */
        public function testOffsetFail_1() {
            $buf = IO_Buffer::create();
            $data_len = strlen($this->_data);
            
            $bytes_written = $buf->write($this->_data);
            
            /* Пытаемся перевести указатель на некорректную позицию */
            $this->setExpectedException('IO_Buffer_Exception');
            $buf->seek(-1);
        }
        
        /**
        * Тест некорректной работы с указателем.
        */
        public function testOffsetFail_2() {
            $buf = IO_Buffer::create();
            $data_len = strlen($this->_data);
            
            $bytes_written = $buf->write($this->_data);
            
            /* Пытаемся перевести указатель на некорректную позицию */
            $this->setExpectedException('IO_Buffer_Exception');
            $buf->seek($data_len + 1);
        }
        
        /**
        * Тест для проверки функции копирования-при-записи.
        */
        public function testCopyOnWrite() {
            /* Создаём заглушку для буфера копирования */
            $copy_buf = $this->getMock('IO_Buffer_Interface');
            
            $callback = array($this, 'writeCallback');
            
            $copy_buf->expects($this->once())
                     ->method('write')
                     ->with($this->equalTo($this->_data))
                     ->will($this->returnCallback($callback));
            
            $opts = array('copy_on_write' => $copy_buf);
            
            /* Создаём новый объект буфера */
            $buf = IO_Buffer::create($opts);
            $this->assertType('IO_Buffer', $buf);
            
            /* Записываем в него данные */
            $bytes_written = $buf->write($this->_data);
        }
        
        public function writeCallback($data) {
            return strlen($data);
        }
        
        /**
        * Тест для проверки неправильной работы копирования-при-записи.
        */
        public function testCopyOnWriteFail() {
            /* Создаём заглушку для буфера копирования */
            $copy_buf = $this->getMock('IO_Buffer_Interface');
            
            $callback = array($this, 'writeCallbackFail');
            
            /* Задаём метод, возвращающий неправильное значение */
            $copy_buf->expects($this->once())
                     ->method('write')
                     ->will($this->returnCallback($callback));
            
            $opts = array('copy_on_write' => $copy_buf);
            
            /* Создаём новый объект буфера */
            $buf = IO_Buffer::create($opts);
            $this->assertType('IO_Buffer', $buf);
            
            $this->setExpectedException('IO_Buffer_Exception');
            
            /* Записываем в него данные */
            $bytes_written = $buf->write($this->_data);
        }
        
        public function writeCallbackFail($data) {
            return strlen($data) - 1;
        }
    }
    
?>