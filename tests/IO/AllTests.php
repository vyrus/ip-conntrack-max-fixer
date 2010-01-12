<?php
    
    require_once dirname(__FILE__) . '/../init.php';
    
    class IO_AllTests {
        public static function suite() {
            $suite = new PHPUnit_Framework_TestSuite('IO');
            
            $suite->addTest(IO_Stream_AllTests::suite());
            $suite->addTestSuite('IO_BufferTest');
            $suite->addTestSuite('IO_StreamTest');
            
            return $suite;
        }
    }

?>