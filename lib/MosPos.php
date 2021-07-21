<?php

class MosPos {
    private $server;
    public function __construct($address, $port){

        $this->server = socket_create(AF_INET,SOCK_STREAM,SOL_TCP) or die("Server open failed {socket_strerror(socket_last_error())}");
        $connection = socket_connect($this->server, $address, $port);

        if($connection === false){
            die("Server is not available");
        }

        if(!file_exists(__dir__."/mospos.ps1")){
            $script_str = "
                Add-Type -AssemblyName System.Windows.Forms
    
                \$X = [System.Windows.Forms.Cursor]::Position.X
                \$Y = [System.Windows.Forms.Cursor]::Position.Y
                
                Write-Output \"{`\"x`\": \$X , `\"y`\": \$Y}\"
            ";

            $fp = fopen(__dir__."/mospos.ps1", "w");

            fwrite($fp, $script_str);
            fclose($fp);
        }

        socket_set_nonblock($this->server);
    }


    private function getMosPos(){
        $output= shell_exec('powershell -File "'.realpath(__DIR__."/mospos.ps1").'"');
        $mospos = json_decode($output, true);
        return $mospos;
    }

    private function loop(){
        while(true){
            $newPos = $this->getMosPos();
            $json_str = json_encode($newPos)."\n";

            $rst = @socket_write($this->server, $json_str, strlen($json_str));

            if($rst === false){
                break;
            }
        }
    }

    public function run($callback=null){
        if($callback !== null && is_callable($callback)){
            $callback();
        }

        $this->loop();
    }
}