<?php

class Server {
    private $server;
    private $clients = [];

    public function __construct($server_ip, $server_port){
        $this->server = socket_create(AF_INET,SOCK_STREAM,SOL_TCP) or die("Server open failed {socket_strerror(socket_last_error())}");
        
        socket_bind($this->server, $server_ip, $server_port);
        socket_listen($this->server, SOMAXCONN);
        socket_set_nonblock($this->server);

        set_error_handler(function($errno, $errstr, $errfile, $errline){
            if(preg_match("/unable to read from socket/", $errstr)){
                throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
            }
        }, E_WARNING);
    }

    private function loop($waitTime){
        $oldPos = "{\"x\":-1, \"y\":-1}\n";


        while(true){
            $client = socket_accept($this->server);
            if($client){
                socket_set_nonblock($client);
                $this->clients[] = $client;
            }   

            if(count($this->clients)){
                $mouseClient = [];

                for($i = 0; $i<count($this->clients); $i++){
                    if($this->clients[$i] === null){
                        continue;
                    }
                    $buff = null;
                    try{
                        $buff = socket_read($this->clients[$i], 1024);
                    }
                    catch(ErrorException $e){
                        $this->clients[$i] = null;
                    }
                    if(strlen($buff) && count($mouseClient) == 0){
                        $buffSplit = explode("\n", $buff);
                        $json_str = $oldPos;
                        for($j=count($buffSplit) - 1; $j>=0; $j--){
                            $testObj = @json_decode($buffSplit[$j], true);
                            if($testObj){
                                $json_str = $buffSplit[$j]."\n";
                                break;
                            }
                        }
                        $oldPos = $json_str;
                    }
                }

                for($i = 0; $i<count($this->clients); $i++){
                    if($this->clients[$i] === null){
                        continue;
                    }
                    @socket_write($this->clients[$i], $oldPos, strlen($oldPos));
                }
            }

            sleep($waitTime);
        }

        socket_close($this->server);
    }

    public function run($waitTime, $callback=null){
        if($callback !== null && is_callable($callback)){
            $callback();
        }

        $this->loop($waitTime);
    }
}