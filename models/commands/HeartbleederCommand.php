<?php

namespace app\models\commands;


class HeartbleederCommand extends AbstractCommand
{
    /**
     * @var string
     */
    public $domain;

    /**
     * @var integer
     */
    public $port;

    public function preExecute()
    {
        $cmd = $this->getCommand().' '.$this->domain;
        if (!empty($this->port))
        {
            $cmd .= ":$this->port";
        }
        $this->setCommand($cmd);
    }

    public function postExecute()
    {
        if ($this->outputContains("is vulnerable") &&
        $this->outputContains("INSECURE"))
        {
            //TODO: шлем уведомление
        }
    }

    public static function getCommandName()
    {
        return 'heartbleeder';
    }

    public function initParameters($msgBody)
    {
        parent::initParameters($msgBody);
        if (property_exists($msgBody, 'extra') &&
        property_exists($msgBody->extra, 'port'))
        {
            $this->port = $msgBody->extra->port;
        }
    }
}