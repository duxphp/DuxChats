<?php

/**
 * 聊天
 */

namespace dux;

use dux\swoole\EventHelper;

class Chats {


    protected $config = [
        'host' => '',
        'port' => 9052
    ];

    public $server;

    protected $manger;

    public $events = [];

    public function __construct(array $config = []) {

        if(empty($config))
            $config = \dux\Config::get('dux.use_socket_data');

        $this->config = array_merge($this->config, $config);
        $this->init();
    }

    protected function init(){

        $this->manger = new ServerManager();
        $this->manger->init($this->config);
        $this->server = $this->manger->getSwooleServer();
        $this->registerDefaultCallBack();
    }

    public function manger() : ?ServerManager {
        return $this->manger;
    }

    public function start(){
        $this->manger()->start();
    }

    public function onOpen(\swoole_websocket_server $server,\swoole_http_request $request){

    }

    public function onMessage(\swoole_websocket_server $server,\swoole_websocket_frame $frame){

    }

    public function onClose(\swoole_websocket_server $server, $fd){

    }

    /**
     * 添加事件
     * @param array $events
     */
    public function addEvents(array $events = null) : void {

        if(is_null($events))
            $events = $this->events;

        $register = $this->manger()->eventRegister();

        if(!$register)
            return;

        foreach ($events as $event => $callback){

            if(is_array($callback) && !is_object($callback[0])){
                $this->addEvents($callback);
                continue;
            }

            $eventStr = constant('\dux\swoole\EventRegister::'. $event);
            if(empty($eventStr)){
                continue;
            }

            EventHelper::registerWithAdd($register,$eventStr,$callback);
        }
    }


    /**
     * 注册默认事件
     *
     */
    private function registerDefaultCallBack() : void {

        isset($this->events['onOpen']) || $this->events['onOpen'] = [$this,'onOpen'];
        isset($this->events['onMessage']) || $this->events['onMessage'] = [$this,'onMessage'];
        isset($this->events['onClose']) || $this->events['onClose'] = [$this,'onClose'];
    }

}