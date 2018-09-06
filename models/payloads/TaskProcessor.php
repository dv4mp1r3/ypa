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
            
            $cmd = ucfirst($msgBody->command);
            $commandClassname = "app\\models\\commands\\{$cmd}Command";
            /**
             * @var commands\AbstractCommand cmd
             */
            $cmd = new $commandClassname($msgBody->taskId, $this->connection);
            
            switch($msgBody->command)
            {
                case commands\HostCommand::getCommandName():
                    $cmd->domain = $msgBody->domain;
                    break;
                case commands\NmapCommand::getCommandName():
                    $cmd->host = $msgBody->extra->host;
                    $cmd->domain = $msgBody->domain;
                    break;
                case commands\WhoisCommand::getCommandName();
                    break;
                case commands\WpscanCommand::getCommandName():
                    $cmd->domain = $msgBody->domain;
                    break;
                case commands\PhpmyadminCommand::getCommandName():
                    $cmd->isHttps = $msgBody->extra->isHttps;
                    $cmd->domain = $msgBody->domain;
                    break;
                case commands\PingCommand::getCommandName():
                    $cmd->previousCommand = $msgBody->extra->previousCommand;
                    $cmd->domain = $msgBody->domain;
                    break;
                case commands\SubfinderCommand::getCommandName():
                    $cmd->domain = $msgBody->domain;
                    break;
                default: 
                    throw new \Exception('Unknown command '.$msgBody->command);
            }

            $cmd->run();                      
        } 
        catch (\Exception $ex) 
        {
            $this->printException($ex);           
        }
        
        $this->sendSuccess($message);     
    }
}
