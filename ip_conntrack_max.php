<?php

    require_once 'init.php';

    $net = Network::create();
    $telnet = Telnet::create();
    
    $fixer = IpConntrackMaxFixer::create();
    $fixer->setNetwork($net)
          ->setTelnet($telnet)
          ->setHost('192.168.1.1')
          ->setLogin('admin')
          ->setPasswd('admin')
          ->setConntrackMax(4096);
    
    $fixer->fixIt();
    
    exit();

?>