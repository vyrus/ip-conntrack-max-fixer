<?php
    
    require_once dirname(__FILE__) . '/init.php';
    
    class NetworkTest extends PHPUnit_Framework_TestCase {
        /**
        * Тест создания экземпляра.
        */
        public function testCreate() {
            $net = Network::create();
            
            $this->assertType('Network', $net);
        }
    }
    
?>