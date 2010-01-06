<?php

    require_once 'init.php';
    
    $context = new Fixer_Context();
    
    $fixer = Fixer::create($context);
    $fixer->setHost('192.168.1.1')
          ->setLogin('admin')
          ->setPasswd('admin')
          ->setConntrackMax(4096);
    
    $fixer->fixIt();
    
    exit();

?>