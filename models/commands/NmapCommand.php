<?php

namespace app\models\commands;

class NmapCommand extends AbstractCommand
{
    /**
     *
     * @var string 
     */
    public $host;
    
    public $domain;
    
    public function postExecute()
    {
        $lines = explode("\n", $this->output);
        foreach ($lines as &$line) {
            if (strpos($line, '80/tcp') !== false)
            {
                if (strpos($line, 'open') && strpos($line, 'http'))
                {
                    $this->debugPrint("on $this->host found new HTTP server");
                    $this->pushPhpmyadminChecker(false, $this->domain);
                }   
            }            
            else if (strpos($line, '443/tcp') !== false)
            {
                if (strpos($line, 'open') && strpos($line, 'https'))
                {
                    $this->debugPrint("on $this->host found new HTTPS server");
                    $this->pushPhpmyadminChecker(true, $this->domain);
                }   
            }
        }
    }
    
    protected function pushPhpmyadminChecker($isHttps, $domain)
    {
        $extra = ['isHttps' => $isHttps];
        $message = $this->publisher->buildMessage(
                $this->taskId, 
                $domain, 
                PhpmyadminCommand::getCommandName(), 
                $extra
            );
        $this->publisher->publishMessage($message, self::RABBIT_EXCHANGE_DEFAULT);
    }

    public function preExecute()
    {
        $this->setCommand("nmap $this->host");
    }

    public static function getCommandName()
    {
        return 'nmap';
    }
}
