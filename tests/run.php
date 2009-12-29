<?php
    
    require_once 'init.php';

    header('Content-Type: text/html; charset=utf-8');
        
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTestSuite('IO_StreamTest');
        
    echo '<pre>';
        PHPUnit_TextUI_TestRunner::run($suite);
    echo '</pre>';
                        
?>