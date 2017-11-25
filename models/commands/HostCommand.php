<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\models\commands;

/**
 * Description of HostCommand
 *
 * @author dv4mp1r3
 */
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
                
                $message = [
                    'taskId' => $this->taskId,
                    'host' => $ip,
                    'command' => NmapCommand::getCommandName(),
                    'extra' => null,
                ];
                
                $this->dropMessageTo($message, AbstractCommand::RABBIT_EXCHANGE_DEFAULT);
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
