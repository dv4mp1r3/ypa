<?php

namespace app\models\payloads;


use app\models\Notification;
use PhpAmqpLib\Message\AMQPMessage;

class NotificationProcessor extends AbstractPayload
{
    const AFTER_SAVE_NOTIFY_TO_TELEGRAM = 'telegram';

    /**
     * @param  $data
     */
    public function afterExecute($data)
    {
        if (!property_exists($data->body->extra, 'afterSave'))
        {
            return;
        }

        if ($data->body->extra->afterSave === self::AFTER_SAVE_NOTIFY_TO_TELEGRAM)
        {
            //todo: отправить уведомление в телеграм
        }
    }

    public function execute($message)
    {
        $notification = new Notification();

        $msgBody = json_decode($message->body);

        if (!property_exists($msgBody, 'taskId'))
        {
            throw new \Exception('property taskId does not exists in message');
        }

        $this->mapMessageToNotification($notification, $message);
        if (!$notification->validate())
        {
            throw new \Exception("Can't validate notification. Errors: ".$notification->getErrorsAsString());
        }
        $notification->save();
        $this->afterExecute($message);
    }

    /**
     * Мапинг сообщения из очереди в уведомление по списку атрибутов внутри метода
     * @param Notification $notification
     * @param AMQPMessage $message
     */
    protected function mapMessageToNotification($notification, $message)
    {
        $attrs = [
            'extra',
            'command',
            'type',
            'level',
            'text'
        ];
        if (!property_exists($message->body, 'extra'))
        {
            throw new \InvalidArgumentException('message does not have extra property');
        }

        foreach ($attrs as $attr)
        {
            if (property_exists($message->body->extra, $attr))
            {
                $notification->setAttribute($attr, $message->body->extra->$attr);
            }
        }
    }
}
