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
    
    /**
     * Выполение задачи из очереди
     * @param string $queue
     */
    public function actionTask($queue = null)
    {
        $name = $this->getQueueName(__FUNCTION__);
        $this->loop($name, $queue);
    }
    
    /**
     * Получение имени очереди для выполняемой задачи
     * @param string $functionName
     * @return string
     */
    protected function getQueueName($functionName)
    {
        return str_replace('action', '', $functionName);        
    }
    
    /**
     * Цикл обработки сообщений воркером
     * @param string $payloadName Название класса из неймспейса app\models\payloads
     * инстанс класса AbstractPayload
     * @see app\models\payloads\AbstractPayload
     * @param string $queue название очереди (если не используется, то берется из конфига)
     */
    protected function loop($payloadName, $queue = null)
    {
        $classname = "app\\models\\payloads\\{$payloadName}Processor";
        $payload = new $classname(lcfirst($payloadName));
        $this->w = new AMQPWorker($payload);
        
        $this->w->connect();
        if (empty($queue))
        {
            $queue = \Yii::$app->params['rabbit']['queue'];
        }
    
        $this->w->listen($queue);
    }
    
    public function __destruct()
    {
        if ($this->w instanceof AMQPWorker)
        {
            $this->w->shutdown();
        }        
    }
}
