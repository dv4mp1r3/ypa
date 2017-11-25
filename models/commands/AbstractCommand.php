<?php

namespace app\models\commands;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use app\models\Notification;
use Yii;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractCommand
 *
 * @author dv4mp1r3
 */
abstract class AbstractCommand
{
    const RABBIT_QUEUE_DEFAULT = 'execute';
    const RABBIT_EXCHANGE_DEFAULT = 'task';
    
    protected $output;
    private $originalCommand;
    
    protected $executionResult;
    
    protected $taskId;

    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }
    
    public function run()
    {
        $this->preExecute();        
        $this->output = shell_exec($this->originalCommand);        
        $this->postExecute();
        
        $this->executionResult = $this->output !== null;
        
        return $this->output;
        
    }
    
    public function isSuccess()
    {
        return $this->executionResult;
    }
    
    abstract public function preExecute();
    
    abstract public function postExecute();
    
    abstract public static function getCommandName();
    
    protected function setCommand($command)
    {
        $this->originalCommand = $command;
    }
    
    protected function getCommand()
    {
        return $this->originalCommand;
    }
    
    protected function debugPrint($string)
    {
        if (defined('YII_DEBUG') && YII_DEBUG == true)
        {
            echo $string;
            echo "\n";
        }
    }
    
    /**
     * 
     * @param integer $type
     * @param integer $level
     * @param array $extra
     * @return int
     * @throws \Exception
     */
    protected function pushNotification($type, $level, $extra = null)
    {
        $n = new Notification();
        $n->task_id = $this->taskId;
        $n->type = $type;
        $n->command = $this->getCommand();
        $n->level = $level;
        $n->extra = $extra !== null ? implode("\n", $extra) : '';
        $n->creation_date = date('Y-m-d H:i:s');
        
        if ($n->validate() && $n->save())
        {
            return $n->id;
        }
        
        throw new \Exception($n->getErrorsAsString());

    }
    
    /**
     * Перенос сообщения в другую очередь
     * @param array $message декодированный из json текст сообщения (то что было передано в execute)
     * @param string $exchange имя очереди для вхоста RABBIT_VHOST
     * @param \Exception $ex исключение, которое было выброшено при обработке сообщения
     * текст исключения и номер строки, где оно было выброшено будет добавлено к сообщению
     */
    protected function dropMessageTo($message, $exchange, $ex = null) {         
        $message = json_encode($message);
        
        $rabbitConnectionData = Yii::$app->params['rabbit'];

        $connection = new AMQPStreamConnection(
            $rabbitConnectionData['host'], 
            $rabbitConnectionData['port'], 
            $rabbitConnectionData['user'], 
            $rabbitConnectionData['password'],
            $rabbitConnectionData['vhost']);
        $channel = $connection->channel();
        
        $amqpMessage = new AMQPMessage($message, [
            'content_type' => 'text/plain', 
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);
        $channel->basic_publish($amqpMessage, $exchange);

        $channel->close();
        $connection->close();
    }
    
    protected function lineBeginsAt($line, $substr)
    {
        return strpos($line, $substr) == 0;
    }
    
}
