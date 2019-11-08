<?php

/**
 * 服务管理
 */

namespace dux;

use dux\swoole\EventRegister;
use dux\swoole\ServerEnum;

class ServerManager {

    private $eventRegister;

    private $server;

    private $isStart = false;


    public function __construct(){
        $this->eventRegister = new EventRegister();
    }

    public function init($config){

        $config = array_merge([
            'setting'   => [],
            'args'      => []
        ],$config);

        $this->swooleServer($config['type'],$config['host'],$config['port'],$config['setting'],...$config['args']);
    }

    public function getSwooleServer(){
        return $this->server;
    }

    public function swooleServer($type,$address = '0.0.0.0',$port,array $setting = [],...$args) : void {

        switch ($type){
            case ServerEnum::SERVER:{
                $this->server = new \swoole_server($address,$port,...$args);
                break;
            }
            case ServerEnum::WEB_SERVER:{
                $this->server = new \swoole_http_server($address,$port,...$args);
                break;
            }
            case ServerEnum::WEB_SOCKET_SERVER:{
                $this->server = new \swoole_websocket_server($address,$port,...$args);
                break;
            }
            case ServerEnum::REDIS_SERVER:{
                $this->server = new \swoole_redis_server($address,$port,...$args);
                break;
            }
            default:{
                throw new \Exception("unknown server type :{$type}", 500);
                break;
            }
        }

        if($this->server){
            $this->server->set($setting);
        }
    }

    public function eventRegister() : EventRegister {
        return $this->eventRegister;
    }

    /**
     * 开始服务
     */
    public function start() : void {
        $events = $this->eventRegister()->all();
        foreach ($events as $event => $callback){
            $this->getSwooleServer()->on($event, function (...$args) use ($callback) {
                foreach ($callback as $item) {
                    call_user_func($item,...$args);
                }
            });
        }

        $this->isStart = true;
        $this->getSwooleServer()->start();
    }

    /**
     * 状态
     * @return bool
     */
    public function isStart() : bool {
        return $this->isStart;
    }


}