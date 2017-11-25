<?php

namespace app\commands;
use yii\console\Controller;
use app\models\AMQPWorker;
use app\models\payloads\TaskProcessor;
use app\models\payloads\NotificationProcessor;

class RabbitController extends Controller
{
    protected $w;
    
    public function __construct($id, $module, $config = array())
    {
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 1);
        ini_set("memory_limit", "64M");
        $this->w = null;
        parent::__construct($id, $module, $config);
    }
    
    public function actionNotification()
    {
        $name = $this->getQueueName(__FUNCTION__);
        $this->loop($name);
    }
    
    public function actionTask()
    {
        $name = $this->getQueueName(__FUNCTION__);
        $this->loop($name);
    }
    
    protected function getQueueName($functionName)
    {
        return str_replace('action', '', $functionName);        
    }
    
    protected function loop($payloadName)
    {
        $classname = "app\\models\\payloads\\{$payloadName}Processor";
        $payload = new $classname(lcfirst($payloadName));
        $this->w = new AMQPWorker($payload);
        
        $this->w->connect();
        $this->w->listen('execute');
    }
    
    public function __destruct()
    {
        if ($this->w instanceof AMQPWorker)
        {
            $this->w->shutdown();
        }        
    }
}
