<?php

namespace app\models\commands;

use Yii;

/**
 * Запуск nmap со скриптом vulners
 * Class VulnersCommand
 * @package app\models\commands
 * @see https://github.com/vulnersCom/nmap-vulners
 */
class VulnersCommand extends AbstractCommand
{
    public $domain;

    protected $foundedVulns = [];

    public function preExecute()
    {
        if (defined('YII_DEBUG') && YII_DEBUG)
        {
            $dir = Yii::getAlias('@app');
            $file = "$dir/tests/data/vulnersCommand.log";
            $this->setCommand("cat $file");
            return;
        }
        $this->setCommand("nmap -sV --script vulners $this->domain");
    }

    public function postExecute()
    {
        if (empty($this->output))
        {
            throw new \LogicException("Empty output");
        }
        $lines = explode("\n", $this->output);
        $vulnersSwitch = false;
        $productName = '';
        $lastPort = 0;
        foreach ($lines as &$line) {
            $port = intval($line);
            if ($port) {
                $lastPort = $port;
                $vulnersSwitch = false;
                $productName = '';
            }
            else if ($this->lineBeginsAt($line, '| vulners:'))
            {
                $vulnersSwitch = true;
                continue;
            }
            else if ($vulnersSwitch)
            {
                if ($this->lineContains($line, ['CVE-']))
                {
                    $line = str_replace('|       ', '', $line);
                    $dataArray = explode(' ', $line);
                    $dataArray = array_values(array_filter($dataArray));
                    $dataArray[] = "port $lastPort";
                    array_push($this->foundedVulns[$productName], $dataArray);
                }
                else
                {
                    $productName = trim($line);
                    $this->foundedVulns[$productName] = [];
                }
            }
        }

        if (!empty($this->foundedVulns))
        {
            $this->sendReport();
        }
    }

    protected function sendReport()
    {
        $report = $this->makeReport();
    }

    protected function makeReport()
    {
        //todo: implement
    }

    public static function getCommandName()
    {
        return 'nmap';
    }
}