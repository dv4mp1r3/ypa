<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\models\payloads;

use app\models\commands\HostCommand;
use app\models\commands\NmapCommand;
use app\models\commands\WpscanCommand;
/**
 * Description of Task
 *
 * @author dv4mp1r3
 */
class TaskProcessor extends AbstractPayload
{
    
    public function afterExecute($data)
    {
//        /echo __METHOD__." called \n";
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
                throw new Exception('property command does not exists in message');
            }
            
            if (!property_exists($msgBody, 'taskId'))
            {
                throw new Exception('property taskId does not exists in message');
            }
            
            $cmd = ucfirst($msgBody->command);
            $commandClassname = "app\\models\\commands\\{$cmd}Command";
            $cmd = new $commandClassname($msgBody->taskId);
            
            switch($msgBody->command)
            {
                case HostCommand::getCommandName():
                    $cmd->domain = $msgBody->domain;
                    break;
                case NmapCommand::getCommandName():
                    $cmd->host = $msgBody->host;
                    break;
                case 'whois':
                    break;
                case WpscanCommand::getCommandName():
                    $cmd->domain = $msgBody->domain;
                    break;
                default: 
                    throw new Exception('Unknown command '.$msgBody->command);
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
