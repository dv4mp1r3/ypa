<?php

namespace app\models\commands;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use app\models\Notification;
use Yii;
use app\models\Task;
use app\models\AMQPPublisher;

abstract class AbstractCommand
{
    const RABBIT_QUEUE_DEFAULT = 'execute';
    const RABBIT_EXCHANGE_DEFAULT = 'task';
    
    protected static $RABBIT_POSSIBLE_VHOSTS = [
        '/ypa',
        '/ypa-high',
        '/ypa-medium',
        '/ypa-low',
    ];
    
    protected $output;
    private $originalCommand;
    
    protected $executionResult;
    
    protected $taskId;
    
    /**
     *
     * @var \app\models\AMQPPublisher
     */
    protected $publisher;

    /**
     * 
     * @param integer $taskId
     * @param AMQPStreamConnection $connection
     */
    public function __construct($connection = null, $queue = null)
    {
        if ($connection instanceof AMQPStreamConnection)
        {
            $this->publisher = new \app\models\AMQPPublisher($connection);
        }      
    }

    /**
     * инициализация свойств из данных в сообщении из очереди
     * @param stdClass $msgBody
     */
    public function initParameters($msgBody)
    {
        // опциональный параметр, которого может не быть в команде
        if (property_exists($msgBody, 'domain'))
        {
            $this->domain = $msgBody->domain;
        }

        // обязательный параметр, любая команда
        // должна быть привязана к задаче
        $this->taskId = $msgBody->taskId;
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
     * @param string $line
     * @param string $substr
     * @return bool
     */
    protected function lineBeginsAt($line, $substr)
    {
        return strpos($line, $substr) === 0;
    }

    /**
     * @param string $substr
     * @return bool
     */
    protected function outputContains($substr)
    {
        return strpos($this->output, $substr) !== false;
    }
    
    /**
     * 
     * @param string $line
     * @param array $dataArray
     * @return boolean
     */
    protected function lineContains($line, $dataArray)
    {
        foreach ($dataArray as $key => &$value) {
            if (strpos($line, $value) !== false)
            {
                return true;
            }
        }
        
        return false; 
    }
}
