<?php

namespace app\models\commands;

class PhpmyadminCommand extends AbstractCommand
{
    const SCRIPT_NAME = 'websploit';
    
    public $domain;
    public $isHttps;
    
    protected $interestingSubstr = [
        '[301 Moved Permanently]'
    ];

    public function postExecute()
    {
        $lines = explode("\n", $this->output);
        foreach ($lines as &$line) {
            if (empty($line))
            {
                continue;
            }
            $pos =  strpos($line, "/]");            
            if ($pos > 0 && strpos($line, "[93m[/") === 0 && strpos($line, 'Not Found') == false)
            {
                $folder = substr($line, 7, $pos -7);
                $this->debugPrint("Phpmyadmin found something on $this->domain/$folder");
            }                
        }
    }

    public function preExecute()
    {
        $domain = $this->domain;
        if ($this->isHttps)
        {
            $this->setCommand(self::SCRIPT_NAME." web/pma https://{$domain}");
        }
        else
        {
            $this->setCommand(self::SCRIPT_NAME." web/pma http://{$domain}");
        }       
    }

    public static function getCommandName()
    {
        return 'phpmyadmin';
    }
}
