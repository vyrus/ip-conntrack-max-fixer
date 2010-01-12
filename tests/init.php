<?php
    
    $cur_dir = dirname(__FILE__);
    require_once $cur_dir . '/../lib/Init.php';
    require_once $cur_dir . '/../lib/Init/Exception.php';
    
    Init::define('DS',          DIRECTORY_SEPARATOR);
    Init::define('ROOT',        realpath($cur_dir . '/../'));
    Init::define('LIB',         ROOT . DS . 'lib');
    Init::define('THIRD_PARTY', ROOT . DS . 'third_party');
    Init::define('TESTS',       ROOT . DS . 'tests');
          
    Init::setIncludePath( array(LIB, THIRD_PARTY, TESTS) );
    
    require_once 'Zend/Loader.php';
    Zend_Loader::registerAutoload();
    
    require_once 'PHPUnit/Framework.php';
    
?>