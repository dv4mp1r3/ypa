<?php

namespace app\models\commands;

class HostCommand extends AbstractCommand
{
    /**
     *
     * @var string 
     */
    public $domain;
    
    public function postExecute()
    {
        $searchString = "$this->domain has address ";
        $lines = explode("\n", $this->output);
        foreach ($lines as &$line) {
            if (strpos($line, $searchString) !== false)
            {
                $ip = trim(str_replace($searchString, '', $line));
                $this->debugPrint("IP FOUND: $ip");   
                $message = $this->publisher->buildMessage(
                        $this->taskId, 
                        $this->domain, 
                        NmapCommand::getCommandName(), 
                        ['host' => $ip]
                    );                
                $this->publisher->publishMessage($message, 
                    AbstractCommand::RABBIT_EXCHANGE_DEFAULT, 
                    \app\models\AMQPPublisher::ROUTING_KEY_DISCOVER_TOOL);
            }
        }
    }

    public function preExecute()
    {
        if (property_exists($this, 'domain'))
        {
            $this->setCommand("host {$this->domain}");
        }
    }

    public static function getCommandName()
    {
        return 'host';
    }
}
