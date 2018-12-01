<?php

namespace app\models;

use yii\base\Model;
use Yii;
use app\models\payloads\AbstractPayload;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPWorker extends Model
{
    const CONFIG_INDEX_HOST = 'vhost',
        CONFIG_INDEX_QUEUE = 'queue';
        
    protected $vHost = null;
    protected $queue = null;
    protected $tag;
    
    protected $payload;
    
    protected $channel;
    protected $connection;
    
    public function __construct($payload)
    {
        if (!($payload instanceof AbstractPayload))
        {
            throw new \Exception('Wrong payload parent class');
        }
                
        $this->payload = $payload; 
        
        $this->tag = rand(0, 100);   
        
    }
    
    public function connect()
    {
        $rabbitConnectionData = Yii::$app->params['rabbit'];
        
        $this->connection = new AMQPStreamConnection(
            $rabbitConnectionData['host'], 
            $rabbitConnectionData['port'], 
            $rabbitConnectionData['user'], 
            $rabbitConnectionData['password'], 
            $rabbitConnectionData[self::CONFIG_INDEX_HOST], 
            $insist = false,
            $login_method = 'AMQPLAIN',
            $login_response = null,
            $locale = 'en_US',
            $connection_timeout = 60*50,
            $read_write_timeout = 60*50,
            $context = null,
            $keepalive = false,
            $heartbeat = 30*50);
        //подключение к очереди
        $this->connection->set_close_on_destruct(false);
        $this->channel = $this->connection->channel();
        $this->channel->basic_qos(0, 1, false);        
        $this->payload->setConnection($this->connection);
    }
    
    /**
     * 
     * @param string $queue
     */
    public function listen($queue = null)
    {
        $this->queue = $queue;
        $this->channel->basic_consume($queue, $this->getTag(), 
                false, 
                false, 
                false, 
                false, 
                [$this->payload, commands\AbstractCommand::RABBIT_QUEUE_DEFAULT]);
        while (count($this->channel->callbacks)) 
        {
            $this->channel->wait();
        }
    }
    
    public function getTag()
    {
        return $this->tag;
    }
    
    ///**
    // * @param \PhpAmqpLib\Channel\AMQPChannel $channel
    // * @param \PhpAmqpLib\Connection\AbstractConnection $connection
    // */
    public function shutdown() 
    {
        if ($this->channel !== null) {
            $this->channel->close();
        }
        if ($this->connection !== null) {
            $this->connection->close();
        }
    }
}
