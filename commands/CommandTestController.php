<?php

namespace app\commands;

use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\console\Controller;
use app\models\payloads\TaskProcessor;


class CommandTestController extends Controller
{
    /**
     * @param string|null $messageBodyFile имя json файла в каталоге tests/data/
     * @throws \Exception
     */
    public function actionRun($messageBodyFile = null)
    {
        try {
            if ($messageBodyFile === null) {
                $messageBodyFile = Yii::getAlias('@app') . '/tests/data/testMessageBody.json';
            } else {
                $messageBodyFile = Yii::getAlias('@app') . "/tests/data/$messageBodyFile.json";
            }
            $object = new AMQPMessage(file_get_contents($messageBodyFile));
            $taskProcessor = new TaskProcessor(null);
            $taskProcessor->execute($object);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}