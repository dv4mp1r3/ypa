<?php

namespace app\models\payloads;


use app\models\commands;

class TaskProcessor extends AbstractPayload
{
    
    public function afterExecute($data)
    {

    }
    
    /**
     * 
     * @param PhpAmqpLib\Message\AMQPMessage $message
     */
    public function execute($message)
    {
        try
        {
            $msgBody = json_decode($message->body);
            if (!property_exists($msgBody, 'command'))
            {
                throw new \Exception('property command does not exists in message');
            }
            
            if (!property_exists($msgBody, 'taskId'))
            {
                 throw new \Exception('property taskId does not exists in message');
            }

            if (!class_exists($msgBody->command))
            {
                throw new \Exception('Unknown command '.$msgBody->command);
            }
            $commandClassname = $msgBody->command;//"app\\models\\commands\\{$cmd}Command";

            /**
             * @var commands\AbstractCommand cmd
             */
            $cmd = new $commandClassname($this->connection);
            $cmd->initParameters($msgBody);
            $cmd->run();                      
        } 
        catch (\Exception $ex) 
        {
            $this->printException($ex);           
        }
        
        $this->sendSuccess($message);     
    }
}
