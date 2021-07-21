<?php

require_once "lib/MosPos.php";

$m = new MosPos('127.0.0.1', 8886);
$m->run(function(){
    echo "Client is running...\n";
});