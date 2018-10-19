<?php

namespace app\models\commands;


class HydraCommand extends AbstractCommand
{
    const DEFAULT_USERS_LIST = '/opt/hydra/user.txt';
    const DEFAULT_PASS_LIST = '/opt/hydra/pass.txt';

    protected $useProxy = false;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $userList;

    /**
     * @var string
     */
    protected $passList;

    /**
     * @var integer
     */
    protected $threadCount;

    public function initParameters($msgBody)
    {
        parent::initParameters($msgBody);
        $this->userList = $msgBody->extra->userList;
        $this->passList = $msgBody->extra->passList;
        if (property_exists($msgBody->extra, 'useProxy'))
        {
            $this->useProxy = true;
        }

    }

    public function preExecute()
    {
        $cmd = $this->getCommand()." -L {$this->userList} -P $this->passList -t 4";
        if ($this->useProxy)
        {
            $cmd = "proxychains $cmd";
        }
        $this->setCommand($cmd);

    }

    public function postExecute()
    {
        if ($this->outputContains("[ERROR] could not connect "))
        {
            //TODO: возможно следует посканить с сокс листом
        }
    }

    public static function getCommandName()
    {
        return 'hydra';
    }
}