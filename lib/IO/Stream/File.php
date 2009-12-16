<?php

    class IO_Stream_File extends IO_Stream_Abstract {
        /**
        * @var array
        */
        protected $default_options = array(
            'enable_profiler' => false,
            'file_path' => null,
            'mode' => null
        );
        
        public function open() {
            $file_path = $this->options->file_path;
            $mode = $this->options->mode;
                           
            if (false === ($this->stream = fopen($file_path, $mode))) {
                throw new IO_Stream_File_Exception('Error opening ' . $file_path . ' with ' . $mode . ' mode');
            }
            
            return true;
        }
    }

?>