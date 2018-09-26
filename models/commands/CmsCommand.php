<?php

namespace app\models\commands;


class CmsCommand extends AbstractCommand
{
    const PORT_DEFAULT_HTTPS = 443;
    const PORT_DEFAULT_HTTP = 80;

    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $port;

    /**
     * @var string
     */
    public $protocol;

    public function preExecute()
    {
        $dir = Yii::getAlias('@app');
        $url = $this->buildUrl();
        if (empty($dir) || empty($url))
        {
            throw new \Exception('Empty dir/url variable. Command was interrupted');
        }
        $this->setCommand("php $dir/yii internal-commands/cmsdetect $url");
    }

    public function initParameters($msgBody)
    {
        parent::initParameters($msgBody);
        $this->port = $msgBody->extra->port;
        $this->protocol = $msgBody->extra->protocol;
        $this->path =$msgBody->extra->path;
    }

    /**
     * Сборка урла для поиска cms
     * @return string
     * @throws \Exception
     */
    protected function buildUrl()
    {
        $url = 'http';
        if ($this->protocol === self::PROTOCOL_HTTPS)
        {
            $url .= 's';
        }
        elseif ($this->protocol !== self::PROTOCOL_HTTP)
        {
            throw new \Exception('Unsupported protocol type: '.$this->protocol);
        }

        $url .= "://{$this->domain}:{$this->port}";

        if (!empty($this->path))
        {
            $url .= "/$this->path";
        }

        return $url;
    }

    public function postExecute()
    {
        if (!empty($this->output))
        {
            // TODO: что-то нашли, бросаем уведомление, генерим задание
        }
    }

    public static function getCommandName()
    {
        return 'cms';
    }
}