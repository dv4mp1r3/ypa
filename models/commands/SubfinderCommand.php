<?php

namespace app\models\commands;

/**
 *
 * враппер для утилиты subfinder
 * @see https://github.com/subfinder/subfinder
 * @package app\models\commands
 */
class SubfinderCommand extends AbstractCommand
{

    /**
     * @var array
     */
    protected $domains = [];

    /**
     * @var string
     */
    public $domain;

    public function preExecute()
    {
        $this->setCommand("subfinder -d $this->domain");
    }

    public function postExecute()
    {
        $lines = explode("\n", $this->output);
        $rDomain = ".$this->domain";
        $lineIsDomain = false;
        foreach ($lines as &$line) {

            if ($lineIsDomain && !empty($line))
            {
                if ($line == $rDomain)
                {
                    array_push($this->domains, $this->domain);
                }
                else
                {
                    array_push($this->domains, trim($line));
                }

                continue;
            }

            if (strpos($line, "Unique subdomains found for {$this->domain}"))
            {
                $lineIsDomain = true;
                continue;
            }
        }

        if (count($this->domains))
        {
            $this->pushDomainsToPing();
        }
    }

    protected function pushDomainsToPing()
    {
        foreach ($this->domains as $domain)
        {
            $message = $this->publisher->buildMessage(
                $this->taskId,
                $domain,
                PingCommand::class,
                ['previousCommand' => self::class]
            );
            $this->publisher->publishMessage($message,
                AbstractCommand::RABBIT_EXCHANGE_DEFAULT,
                \app\models\AMQPPublisher::ROUTING_KEY_DISCOVER_TOOL);
        }
    }



    public static function getCommandName()
    {
        return 'subfinder';
    }
}