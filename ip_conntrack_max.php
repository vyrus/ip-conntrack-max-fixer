<?php

    require_once 'init.php';

    header('Content-Type: text/plain');
    
    $net = Network::create();
    
    $fixer = IpConntrackMaxFixer::create();
    $fixer->setNetwork($net);
    
    $telnet = Telnet::create();
    $telnet->setListener($fixer);
    
    $telnet->connect('192.168.1.1');
    
    while(true) {
        $net->dispatchStreams();
    }

?>