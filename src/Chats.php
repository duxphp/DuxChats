<?php

/**
 * 聊天
 */

namespace dux;

use dux\swoole\EventHelper;

class Chats {


    protected $config = [
        'host' => '0.0.0.0',
        'port' => 9052
    ];

    public $server;

    protected $manger;

    public $events = [];

    public function __construct(array $config = []) {

        if(empty($config))
            $config = \dux\Config::get('dux.socket_data');

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
     * @param array|null $events
     * @param null|string $eventName
     */
    public function addEvents(array $events = null,?string $eventName = null) : void {

        if(is_null($events))
            $events = $this->events;

        $register = $this->manger()->eventRegister();

        if(!$register)
            return;

        foreach ($events as $event => $callback){

            $eventKey = is_null($eventName) ? $event : $eventName;

            //拦截 [$this,'事件名'] 等数组
            if(is_array($callback) && !is_string($callback[1])){
                $this->addEvents($callback,$eventKey);
                continue;
            }

            $eventStr = constant('\dux\swoole\EventRegister::'. $eventKey);
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