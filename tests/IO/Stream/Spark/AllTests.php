<?php
    
    require_once dirname(__FILE__) . '/../../../init.php';
    
    class IO_Stream_Spark_AllTests {
        public static function suite() {
            $suite = new PHPUnit_Framework_TestSuite('IO_Stream_Spark');
            
            $suite->addTestSuite('IO_Stream_Spark_SocketTest');
            
            return $suite;
        }
    }

?>