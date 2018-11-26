<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property integer $id
 * @property string $text
 * @property string $creation_date
 * @property string $extra
 * @property integer $task_id
 * @property string $command
 * @property integer $type
 * @property integer $level
 *
 * @property Task $task
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creation_date'], 'safe'],
            [['extra'], 'string'],
            [['task_id'], 'required'],
            [['task_id', 'type', 'level'], 'integer'],
            [['text'], 'string', 'max' => 255],
            [['command'], 'string', 'max' => 512],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::className(), 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'текст уведомления',
            'creation_date' => 'Creation Date',
            'extra' => 'дополнительное поле для помещения кастомных данных, которые возможно понадобятся позже',
            'task_id' => 'связь с задачей',
            'command' => 'Оригинальная команда, которая привела к выбросу уведомления',
            'type' => 'тип уведомления (бага, RCE и прочее)',
            'level' => 'уровень уведомления (некритичный, критический и прочее)',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }
    
    public function getErrorsAsString()
    {
        $string = '';
        $errors = $this->getErrors();
        foreach ($errors as $attributeName => &$attributeErrors) {
            $string .= "\nAttribute - '$attributeName':\n" . implode("\n", $attributeErrors);
        }
        return $string;
    }
}
