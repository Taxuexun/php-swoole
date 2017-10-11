<?php 

class Server
{
    public static $server;
    public static $message = [
        'hello1',
        'hello everyone',
        'welcome to swoole test',
        'i am a test',
        'it is a message',
    ];


    public function __construct()
    {
        self::$server = new swoole_websocket_server("192.168.33.10", 1992);
        $this->ServerOn();
        self::$server->start();
    }

    public function ServerOn()
    {
        self::$server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";//$request->fd 是客户端id
        });

        self::$server->on('message', function (swoole_websocket_server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            //$frame->fd 是客户端id，$frame->data是客户端发送的数据
            //服务端向客户端发送数据是用 $server->push( '客户端id' ,  '内容')
            $data = $frame->data;

           foreach($server->connections as $fd){
                $server->push($fd , $data);//循环广播
           }
        });

        self::$server->on('close', function ($server, $fd) {
            echo "client {$fd} closed\n";
            $this->CrontabPush();
        });
    }

    public function CrontabPush()
    {
        //setinterval
       /* swoole_timer_tick(2000, function ($timer_id)
        {
            foreach(self::$server->connections as $fd){
                $rand_num = array_rand(self::$message);
                self::$server->push($fd ,self::$message[$rand_num] .'leave');//循环广播
            }
        });*/
       //settimeout
       self::$server->after( 2000,  function ()
       {
           foreach(self::$server->connections as $fd){
               self::$server->push($fd ,mt_rand(10,99) .'-leave');//循环广播
           }
       });

    }
}
$server = new Server();

