<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$connection = socket_connect($socket,'127.0.0.1', 8886);

while(true){
    $buff = socket_read($socket,1024);

    $buffSplit = explode("\n", $buff);

    for($i = count($buffSplit)-1; $i>=0; $i--){
        $jsonObj = @json_decode($buffSplit[$i], true);

        if($jsonObj){
            echo "X position is {$jsonObj['x']}, Y position is {$jsonObj['y']}\n";
            break;
        }
    }
}