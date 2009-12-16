<?php

    /**
    * @todo Не работает:
    * $flags = BitFlags::create();
    * $flags->set(pow(2, 31));
    * echo $flags;
    */
    class BitFlags {
        /**
        * @var int
        */
        protected $bitfield = 0;
        
        public function __construct($bitfield = 0) {
            $this->bitfield = $bitfield;
        }
        
        public static function create($bitfield = 0) {
            return new self($bitfield);
        }
        
        public function set($flag) {
            $this->bitfield |= $flag;
            
            return $this;
        }
        
        public function reset($flag = null) {
            if (null === $flag) {
                $this->bitfield = 0;
            } else {
                $this->bitfield ^= $flag;
            }
            
            return $this;
        }
        
        public function is_set($flag) {
            if ($this->bitfield & $flag) {
                return true;
            }
            
            return false;
        }
        
        public function __toString() {
            $exp = 8;
            while ($this->bitfield > ($border = pow(2, $exp) - 1)) {
                $exp += 8;
            }
            
            $bytes_num = $exp / 8;
            $bytes = array();
            
            for ($byte = 0; $byte < $bytes_num; $byte++) {
                $bits = array();
                
                $start = $byte * 8;
                $stop  = ($byte + 1) * 8;
                
                for ($i = $start; $i < $stop; $i++) {
                    $compare_bit = pow(2, $i);
                    $bits[] = ($this->bitfield & $compare_bit ? 1 : 0);
                }
                
                $bytes[] = implode('', $bits);
            }
                
            return strrev(implode(' ', $bytes));
        }
    }      
                       
?>