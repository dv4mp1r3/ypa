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
                    $this->addHeartbleederCommand($this->domain, $port);
                    $this->addCmsCommand(self::PROTOCOL_HTTPS, $this->domain, $port);
                    $this->addDirParserCommand(self::PROTOCOL_HTTPS, $this->domain);
                }
                else if (strpos($line, self::PROTOCOL_HTTP))
                {
                    $this->debugPrint("on $this->host found new HTTPS server");
                    $this->addCmsCommand(self::PROTOCOL_HTTP, $this->domain, $port);
                    $this->addDirParserCommand(self::PROTOCOL_HTTP, $this->domain);
                }
            }
            else if ((strpos($line, 'open') !== false || strpos($line, 'filtered') !== false)
                && strpos($line, 'ssh') !== false)
            {
                $this->addHydraCommand('ssh', $this->domain, $port);
            }

        }
    }

    /**
     * Отправка в очередь задания для брута сервиса через thc hydra
     * @param string $protocol
     * @param string $address
     * @param integer $port
     * @param bool $useProxy использовать proxychains при работе
     * @param null|string $userList полный путь к листу юзеров
     * @param null|string $passList полный путь к листу с паролями
     * @see https://github.com/vanhauser-thc/thc-hydra
     */
    protected function addHydraCommand($protocol, $address, $port, $useProxy = false, $userList = null, $passList = null)
    {
        $url = "$protocol://$address:$port";
        $extra = [
            'url' => $url,
            'useProxy' => $useProxy,
        ];
        if (!empty($userList))
        {
            $extra['userList'] = $userList;
        }
        if (!empty($passList))
        {
            $extra['passList'] = $passList;
        }

        $message = $this->publisher->buildMessage(
            $this->taskId,
            $this->domain,
            HydraCommand::class,
            $extra
        );
        $this->publisher->publishMessage($message, self::RABBIT_EXCHANGE_DEFAULT);

    }

    /**
     * @param string $domain
     * @param integer|null $port
     */
    protected function addHeartbleederCommand($domain, $port = null)
    {
        $message = $this->publisher->buildMessage(
            $this->taskId,
            $domain,
            HeartbleederCommand::class,
            $port !== null ? ['port' => $port] : null
            );
        $this->publisher->publishMessage($message, self::RABBIT_EXCHANGE_DEFAULT);
    }

    /**
     * @param string $protocol
     * @param string $domain
     * @param string $port
     */
    protected function addCmsCommand($protocol, $domain, $port)
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
    protected function addDirParserCommand($protocol, $domain)
    {

        $extra = [
            'url' => "$protocol://$domain",
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
