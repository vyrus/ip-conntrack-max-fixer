<?php

    $cur_dir = dirname(__FILE__);
    require_once $cur_dir . '/lib/Init.php';
    require_once $cur_dir . '/lib/Init/Exception.php';
        
    Init::define('DS',           DIRECTORY_SEPARATOR);
    Init::define('ROOT',         dirname(realpath(__FILE__)));
    Init::define('APP',          ROOT . DS . 'app');
    Init::define('LIB',          ROOT . DS . 'lib');
    Init::define('THIRD_PARTY',  ROOT . DS . 'third_party');
        
    Init::setIncludePath( array(APP, LIB, THIRD_PARTY) );
    Init::setErrorReporting(E_ALL);
    //Init::setupErrorHandler();

    Init::define('CR',   "\r");
    Init::define('LF',   "\n");
    Init::define('CRLF', CR . LF);
    
    Init::setLocale('ru_RU.UTF8');
    Init::setTimezone('Europe/Moscow');
    
    require_once 'Zend/Loader.php';
    Zend_Loader::registerAutoload();
    
?>