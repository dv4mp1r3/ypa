<?php

namespace app\models\commands;


class DirParserCommand extends AbstractCommand
{
    public $url;

    public function preExecute()
    {
        if (property_exists($this, 'domain'))
        {
            $this->setCommand("dirb {$this->url} /home/test/dirb222/wordlists/indexes.txt");
        }
    }

    /**
     *
     */
    public function postExecute()
    {
        $result_array = [];
        $i=0;
        $searchString = "==> DIRECTORY: $this->url";
        $linestoexplode = str_replace("\r", "\n", $this->output);
        $lines = explode ("\n", $linestoexplode );
        foreach ($lines as $line){
            $occurances_string = substr_count($line, $searchString);
            if ($occurances_string>0){
                $fine_line = str_replace("==> DIRECTORY: ", "", $line);
                $result_array[$i]=$fine_line;
                $i=$i+1;
            }
        }
        $this->addAdminsCommand($result_array);
    }
    protected function addAdminsCommand($directory_list)
    {
        $extra = [];
        if (!empty($directory_list))
        {
            $extra['directory_list'] = $directory_list;
        }

        $message = $this->publisher->buildMessage(
            $this->taskId,
            $this->domain,
            Null,   //ToDo
            $extra
        );
        $this->publisher->publishMessage($message, self::RABBIT_EXCHANGE_DEFAULT);
    }
    protected function addServiceDirCommand()
    {
        $extra = [];
        if (!empty($directory_list))
        {
            $extra['directory_list'] = $directory_list;
        }

        $message = $this->publisher->buildMessage(
            $this->taskId,
            $this->domain,
            Null,   //ToDo
            $extra
        );
        $this->publisher->publishMessage($message, self::RABBIT_EXCHANGE_DEFAULT);
    }

    public static function getCommandName()
    {
        return 'directory buster';
        // TODO: Implement getCommandName() method.
    }
}