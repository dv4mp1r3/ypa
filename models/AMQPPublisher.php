<?php
namespace app\models;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPPublisher
{
    /**
     *
     * @var AMQPStreamConnection 
     */
    protected $connection; 
    
    protected $channel;
    
    /**
     * Название очередей в rabbit mq
     */
    
    /**
     * 
     */
    const ROUTING_KEY_HOST_ADDED       = 'add_host';
    
    /**
     * 
     */
    const ROUTING_KEY_PENTESTING_TOOL  = 'pentest';
    
    /**
     * 
     */
    const ROUTING_KEY_POTENTIALLY_SLOW = 'potentially_slow';
    
    /**
     * 
     */
    const ROUTING_KEY_DISCOVER_TOOL    = 'discover';
    
    /**
     * 
     * @param AMQPStreamConnection $connection
     */
    public function __construct($connection) {
        $this->connection = $connection;
        $this->channel = $connection->channel();
    }
         
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * 
     * @param string $domain
     * @param string $commandName
     * @param array $extra
     * @return AMQPMessage
     */
    public function buildMessage($taskId, $domain, $commandName, $extra = null)
    {
        $msg = [
            'taskId' => $taskId,
            'domain' => $domain,
            'command' => $commandName === null ? 'host' : $commandName,
            'extra' => $extra,
        ];
        
        $message = new AMQPMessage(json_encode($msg), 
            [
                'content_type' => 'text/plain', 
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);
        return $message;
    }
    
    public function publishMessage($message, $exchange, $routingKey = '')
    {
        return $this->channel->basic_publish($message, $exchange, $routingKey);
    }
}
