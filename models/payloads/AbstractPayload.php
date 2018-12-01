<?php
namespace app\models\payloads;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use app\models\Notification;

abstract class AbstractPayload
{   
    
    protected $queue;
    
    /**
     *
     * @var AMQPStreamConnection;
     */
    protected $connection;
    
    /**
     * 
     * @param AMQPStreamConnection; $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * 
     * @param string $queue
     */
    public function __construct($queue)
    {
        $this->queue = $queue;
    }

    /**
     * 
     * @param AMQPMessage $message
     */
    abstract public function execute($message);

    /**
     * 
     * @param array|string $data
     */
    abstract public function afterExecute($data);
    
    /**
     * 
     * @param AMQPMessage $message
     * @param mixed $data
     */
    protected function sendSuccess($message, $data = null)
    {
        $this->afterExecute($data);
        if (!empty($message->delivery_info) && array_key_exists('channel', $message->delivery_info))
        {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    }
    
    /**
     * 
     * @param \Exception $ex
     */
    protected function printException($ex)
    {
        echo $ex->getMessage();
        echo "\n";
        echo $ex->getTraceAsString();
        echo "\n";
    }
}
