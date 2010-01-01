<?php
    
    require_once dirname(__FILE__) . '/../../init.php';
    
    class IO_Stream_AllTests {
        public static function suite() {
            $suite = new PHPUnit_Framework_TestSuite('IO_Stream');
            
            $suite->addTest(IO_Stream_Spark_AllTests::suite());
            $suite->addTestSuite('IO_Stream_BufferedTest');
            $suite->addTestSuite('IO_Stream_SelectorTest');
            
            return $suite;
        }
    }

?>