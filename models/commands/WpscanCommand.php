<?php

namespace app\models\commands;

class WpscanCommand extends AbstractCommand
{
    const CODE_VULNERABILITY = '^[[31m[!]^[[0m';
    const CODE_WARNING = '^[[33m[!]^[[0m';
    const CODE_NOTICE = '^[[31m[!]^[[0m';
    
    /**
     *
     * @var string 
     */
    public $domain;
    
    public function postExecute()
    {
        $lines = explode("\n", $this->output);
        $noticeData = [];
        foreach ($lines as $lineNumber => &$line) {
            if ($this->lineBeginsAt($line, self::CODE_VULNERABILITY))
            {
                $noticeStr = str_replace(self::CODE_VULNERABILITY, '', $line); 
                $this->pushNotification(0, 0, $noticeStr);
            }
            else if ($this->lineBeginsAt($line, self::CODE_WARNING))
            {
                $noticeData[] = str_replace(self::CODE_WARNING, '', $line);
            }            
        }        
        if (count($noticeData) > 0)
        {
            $this->pushNotification(0, 0, ['data' => $noticeData]);
        }
    }

    public function preExecute()
    {
        $domain = $this->domain;
        $this->setCommand("wpscan --url $domain --follow-redirection");
    }

    public static function getCommandName()
    {
        return 'wpscan';
    }
}
