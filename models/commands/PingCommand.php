<?php

namespace app\models\commands;

/**
 * Обработка результатов от SubfinderCommand
 * массив доменов проверяется на то что они активны
 * все активные домены отправляются в очередь на сканирование nmap
 * @see SubfinderCommand
 * @see NmapCommand
 * @package app\models\commands
 */
class PingCommand extends AbstractCommand
{
    public $domain;

    public $previousCommand;

    public function preExecute()
    {
        if (property_exists($this, 'domain'))
        {
            $this->setCommand($this->getCommandName()." {$this->domain} -c 1");
        }
    }

    public function postExecute()
    {
        if ($this->outputContains('1 packets transmitted, 1 received'))
        {
            $this->pushDomainToScan($this->domain);
        }
    }

    /**
     * Добавление домена в очередь на сканирование nmap
     * @see NmapCommand
     * @param string $domain
     */
    protected function pushDomainToScan($domain)
    {
        /**
         * Если домен найден ранее через subfinder то отправляем в nmap
         */
        $commandName = empty($this->previousCommand)
            ? SubfinderCommand::getCommandName()
            : NmapCommand::getCommandName();
        $message = $this->publisher->buildMessage(
            $this->taskId,
            $this->domain,
            $commandName,
            ['host' => $domain]
        );
        $this->publisher->publishMessage($message,
            AbstractCommand::RABBIT_EXCHANGE_DEFAULT,
            \app\models\AMQPPublisher::ROUTING_KEY_DISCOVER_TOOL);
    }

    public static function getCommandName()
    {
        return 'ping';
    }
}
