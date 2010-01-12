<?php

    /**
    * Буфер ввода-вывода для временного хранения данных, которые уже были
    * считаны из потока, но ещё не были обработаны.
    */
    class IO_Buffer implements IO_Buffer_Interface {
        /**
        * Содержимое буфера.
        * 
        * @var string
        */
        protected $_buffer;
        
        /**
        * Текущая позиция указателя в буфере.
        * 
        * @var int
        */
        private $_offset = 0;
        
        /**
        * Опции буфера.
        * 
        * @var Options
        */
        protected $_opts;
        
        /**
        * Значения опций потока по умолчанию.
        * 
        * @var array
        */
        protected $_default_options = array(
            'enable_profiler' => false,
            'copy_on_write' => null
        );
        
        /**
        * Создание нового объекта буфера.
        * 
        * @param  IO_Buffer_Context_Interface Контекст буфера.
        * @return IO_Buffer
        */
        public function __construct(IO_Buffer_Context_Interface $context) {
            $this->_opts = $context->createOptions();
            $this->setOptions($this->_default_options);
        }
        
        /**
        * Создание нового объекта буфера.
        * 
        * @param  IO_Buffer_Context_Interface Контекст буфера.
        * @return IO_Buffer
        */
        public static function create(IO_Buffer_Context_Interface $context) {
            return new self($context);
        }
        
        /**
        * Установка опций буфера.
        * 
        * @param  array|Options $options
        * @return void
        */
        public function setOptions($options = array()) {
            $this->_opts->apply($options);
        }
        
        /**
        * Возвращает текущую длину буфера.
        * 
        * @return int
        */
        public function getLength() {
            return strlen($this->_buffer);
        }
        
        /**
        * Возвращает текущую позицию указателя.
        * 
        * @return int
        */
        public function getOffset() {
            return $this->_offset;
        }
        
        /**
        * Сброс позиции указателя на начало буфера.
        * 
        * @return void
        */
        public function rewind() {
            $this->_offset = 0;
        }
        
        /**
        * Перемещение указателя на заданную позицию.
        * 
        * @param int $offset Новая позиция указателя.
        * @return void
        * @throws IO_Buffer_Exception Если позиция выходит за границу буфера.
        */
        public function seek($offset) {
            /* Проверяем корректность новой позиции */
            if ($offset < 0 || $offset > $this->getLength())
            {
                $e = 'Позиция указателя выходит за текущие границы буфера';
                throw new IO_Buffer_Exception($e);
            }
            
            $this->_offset = $offset;
        }
        
        /**
        * Считывание блока данных из буфера. Если не указывать количество байт 
        * для считывания, то будет считан блок с текущей позиции указателя и до 
        * конца буфера. Иначе - блок, начинающийся с позиции указателя и с 
        * заданной длинной.
        * 
        * @param  int $bytes Количество байт для считывания.
        * @return string
        */
        public function read($bytes = null) {
            /* Сколько считывать байт: сколько есть всего или сколько задано */
            $bytes = (null === $bytes ? strlen($this->_buffer) : $bytes);
            
            /* Вырезаем блок данных нужной длины */
            $block = substr($this->_buffer, $this->_offset, $bytes);
            
            /* Передвигаем указатель на считанное количество байт вперёд */
            $this->_offset += $bytes;
            
            return $block;
        }
        
        /**
        * Запись данных в буфер.
        * 
        * @param  string $data Блок данных
        * @return int Количество записанных в буфер байт данных.
        * @throws IO_Buffer_Exception Если не удалось осуществить copy-on-write.
        */
        public function write($data) {
            $copy_buf = $this->_opts->get('copy_on_write');
            
            /* Если в опциях установлен буфер для копирования данных, */
            if ($copy_buf instanceof IO_Buffer_Interface) {
                /* то сразу и записываем туда переданный блок */
                $bytes_written = $copy_buf->write($data);
                
                if ($bytes_written !== strlen($data)) {
                    $e = 'Не удалось скопировать блок данных в буфер-копию';
                    throw new IO_Buffer_Exception($e);
                }
            }
            
            /* Находим длину блока */
            $data_len = strlen($data);
            
            /* Дописываем блок в содержимое буфера */
            $this->_buffer .= $data;
            /* И передвигаем указатель на конец буфера */
            $this->_offset += $data_len;
            
            return $data_len;
        }
        
        /**
        * Удаляет из буфера блок данных от начала и до текущей позиции указателя
        * (или до заданной длины блока, если он указан).
        * 
        * @param  int $bytes Длины блока для удаления.
        * @return int Позиция оставшегося блока в границах буфера до удаления.
        */
        public function release($bytes = null) {
            /**
            * Блок какого размера удалять: с начала буфера и до текущей позиции
            * указателя или до заданной длины.
            */
            $start = (null === $bytes ? $this->_offset : $bytes);
            
            /* Отрезаем кусок от начала буфера */
            $this->_buffer = substr($this->_buffer, $start);
            
            /* И сбрасываем позицию указателя в начало буфера */
            $this->rewind();
            
            return $start;
        }
        
    }

?>