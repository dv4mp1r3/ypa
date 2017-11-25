<?php

namespace app\models\commands;

class PhpmyadminCommand extends AbstractCommand
{
    const SCRIPT_NAME = 'pycheckmyadmin';
    
    public $domain;
    public $isHttps;
    
    public function postExecute()
    {
        
    }

    public function preExecute()
    {
        $domain = $this->domain;
        if ($this->isHttps)
        {
            $this->setCommand(self::SCRIPT_NAME." https://{$domain}");
        }
        else
        {
            $this->setCommand(self::SCRIPT_NAME." http://{$domain}");
        }       
    }

    public static function getCommandName()
    {
        return 'phpmyadmin';
    }
}
