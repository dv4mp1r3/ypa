<?php

namespace app\models\payloads;


class NotificationProcessor extends AbstractPayload
{
    /**
     * 
     * @inheritdoc
     */
    public function afterExecute($data)
    {
       
    }

    public function execute($message)
    {
        $this->afterExecute(null);
    }
}
