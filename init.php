<?php

    /* $Id: init.php 7 2009-02-01 01:33:43Z Vyrus $ */
    
    $cur_dir = dirname(__FILE__);
    require_once $cur_dir . '/lib/Init.php';
    require_once $cur_dir . '/lib/Init/Exception.php';
        
    Init::setIncludePath( array(LIB, THIRD_PARTY) );
    Init::setErrorReporting(E_ALL/* | E_STRICT */);
    //Init::setupErrorHandler();

    Init::define('CR',   "\r");
    Init::define('LF',   "\n");
    Init::define('CRLF', CR . LF);
    
    Init::setLocale('ru_RU.UTF8');
    Init::setTimezone('Europe/Moscow');
    
    require_once 'Zend/Loader.php';
    Zend_Loader::registerAutoload();
    
?>