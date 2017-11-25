<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\models\commands;

/**
 * Description of NmapCommand
 *
 * @author dv4mp1r3
 */
class NmapCommand extends AbstractCommand
{
    /**
     *
     * @var string 
     */
    public $host;
    
    public function postExecute()
    {
        $lines = explode("\n", $this->output);
        foreach ($lines as &$line) {
            if (strpos($line, '80/tcp') !== false)
            {
                if (strpos($line, 'open') && strpos($line, 'http'))
                {
                    $this->debugPrint("on $this->host found new HTTP server");
                }   
            }            
            else if (strpos($line, '443/tcp') !== false)
            {
                if (strpos($line, 'open') && strpos($line, 'https'))
                {
                    $this->debugPrint("on $this->host found new HTTPS server");
                }   
            }
        }
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
