<?php


require_once "lib/Server.php";

set_time_limit(0);

$s = new Server("0.0.0.0", 8886);
$s->run(0.8, function(){
    echo "server is running on port 8886";
});