<?php

namespace app\models\commands;

class NmapCommand extends AbstractCommand
{
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';

    /**
     *
     * @var string 
     */
    public $host;

    /**
     * @var string
     */
    public $domain;
    
    public function postExecute()
    {
        $lines = explode("\n", $this->output);
        foreach ($lines as &$line) {
            $port = intval($line);
            if (!$port)
            {
                continue;
            }

            if (strpos($line, '80/tcp') !== false || strpos($line, '443/tcp') !== false)
            {
                if (strpos($line, self::PROTOCOL_HTTPS))
                {
                    $this->debugPrint("on $this->host found new HTTP server");
                    $this->pushCms(self::PROTOCOL_HTTPS, $this->domain, $port);
                }
                else if (strpos($line, self::PROTOCOL_HTTP))
                {
                    $this->debugPrint("on $this->host found new HTTPS server");
                    $this->pushCms(self::PROTOCOL_HTTP, $this->domain, $port);
                }
            }
        }
    }

    /**
     * @param string $protocol
     * @param string $domain
     * @param string $port
     */
    protected function pushCms($protocol, $domain, $port)
    {
        $extra = [
            'protocol' => $protocol,
            'domain' => $domain,
            'path' => '',
            'port' => $port
        ];
        $message = $this->publisher->buildMessage(
            $this->taskId,
            $domain,
            PhpmyadminCommand::class,
            $extra
        );
        $this->publisher->publishMessage($message, self::RABBIT_EXCHANGE_DEFAULT);
    }

    /**
     * @param stdClass $msgBody
     * @inheritdoc
     */
    public function initParameters($msgBody)
    {
        parent::initParameters($msgBody);
        $this->host = $msgBody->host;
    }

    /**
     * @param boolean $isHttps
     * @param string $domain
     */
    protected function pushPhpmyadminChecker($isHttps, $domain)
    {
        $extra = ['isHttps' => $isHttps];
        $message = $this->publisher->buildMessage(
                $this->taskId, 
                $domain, 
                PhpmyadminCommand::class,
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
