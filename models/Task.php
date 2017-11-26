<?php
namespace app\models;

use Yii;
use yii\web\UploadedFile;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property string $name
 * @property integer $id_parent_task
 * @property string $creation_date
 * @property string $filename
 */
class Task extends \yii\db\ActiveRecord
{

    const UPLOAD_FOLDER = 'uploads';

    public $domains;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'filename'], 'required'],
            [['id_parent_task'], 'integer'],
            [['creation_date'], 'safe'],
            [['name', 'filename'], 'string', 'max' => 255],
            [['filename'], 'unique'],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            //'id_parent_task' => 'Id Parent Task',
            //'creation_date' => 'Creation Date',
            //'filename' => 'Filename',
        ];
    }

    /**
     * @param yii\web\UploadedFile $uf 
     * @return string
     */
    protected function uploadFile($uf)
    {
        if (!($uf instanceof UploadedFile)) {
            throw new \Exception('$uf is not instance of UploadedFile');
        }

        $basename = stripslashes($uf->getBaseName()) . '-' . md5(time());
        $ext = 'txt';
        $filePath = Yii::getAlias('@webroot') . "/../" . Task::UPLOAD_FOLDER;
        $filePath = str_replace('web/../', '', $filePath);
        if (!file_exists($filePath)) {
            mkdir($filePath, 0755);
        }
        $filePath .= "/$basename.$ext";

        $error = $uf->saveAs($filePath);

        return $filePath;
    }

    /**
     * 
     * @return array
     */
    public function readDomains()
    {
        $content = file_get_contents($this->filename);
        $lines = explode("\n", str_replace("\r", '', $content));
        array_unique($lines);
        foreach ($lines as $key => &$line) {
            if (empty($line)) {
                unset($lines[$key]);
            }
        }
        return $lines;
    }

    /**
     * @param yii\web\UploadedFile $uploadedFile
     * @param boolean $runValidation
     * @param array $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->creation_date = date('Y-m-d H:i:s');
        $tmpDomainsFile = UploadedFile::getInstance($this, 'domains');
        $filPath = $this->uploadFile($tmpDomainsFile);

        $this->filename = $filPath;
        $saveResult = parent::save();
        if (!$saveResult) {
            throw new \Exception($this->getErrorsAsString());
        }

        return $saveResult;
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

    public function afterSave($insert, $changedAttributes)
    {
        $this->pushToQueue('task');        
        parent::afterSave($insert, $changedAttributes);
    }
    
    /**
     * 
     * @param string $queue
     * @return integer 
     */
    public function pushToQueue($queue)
    {
        $rabbitConnectionData = Yii::$app->params['rabbit'];
  
        $exchange = commands\AbstractCommand::RABBIT_EXCHANGE_DEFAULT;
        $connection = new AMQPStreamConnection(
            $rabbitConnectionData['host'], 
            $rabbitConnectionData['port'], 
            $rabbitConnectionData['user'], 
            $rabbitConnectionData['password'],
            $rabbitConnectionData['vhost']);
        $channel = $connection->channel();
        //$channel->queue_declare($queue, false, true, false, false);
        $publisher = new AMQPPublisher($connection);
        $domains = $this->readDomains();
        $domainsCount = 0;
        foreach ($domains as $key => $domain) 
        { 
            $message = $publisher->buildMessage($this->id, $domain, commands\HostCommand::getCommandName());
            $publisher->publishMessage($message, $exchange);
            $domainsCount++;
        }
        
        $channel->close();
        $connection->close();
        
        return $domainsCount;
    }
    
    /**
     * 
     * @param string $domain
     * @param string $commandName
     * @param array $extra
     * @return AMQPMessage
     */
    protected function buildRabbitMessage($domain, $commandName, $extra = null)
    {
        $msg = [
            'taskId' => $this->id,
            'domain' => $domain,
            'command' => $commandName === null ? 'host' : $commandName,
            'extra' => $extra,
        ];
        
        $message = new AMQPMessage(json_encode($msg), 
                [
                    'content_type' => 'text/plain', 
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);
        return $message;
    }
}
