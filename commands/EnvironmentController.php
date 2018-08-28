<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

/**
 * Проверка окружения на готовность к работе
 * (установлены все компоненты, софт и прочее)
 */
class EnvironmentController extends Controller
{
    
    const COMPONENT_TYPE_RABBIT = 1;
    const COMPONENT_TYPE_MYSQL = 2;
    
    /**
     * Конфиг для сервера очередей
     * @var array 
     */
    protected $rabbitConfig = [
        'host' => 'localhost',
        'port' => 5672,
        'vhost' => '/vhost',
        'user' => '',
        'password' => '',
        'queue' => 'task',
    ];
    
    /**
     * Конфиг для СУБД
     * @var array 
     */
    protected $mysqlConfig = [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=ypa',
        'username' => '',
        'password' => '',
        'charset' => 'utf8',
    ];
    
    /**
     * Список используемого софта в очередях
     * @var array 
     */
    protected $thirdPartyApps = [
        'nmap',
        'wpscan',
        'hydra',
        'photoshop'
    ];
    
    /**
     * Список компонентов, без которых проект не заработает
     * @var array 
     */
    protected $components = [
        'rabbit',
        'mysql'
    ];
    
    /**
     * Проверка наличия всех установленных компонентов в системе:
     * сервер очередей и субд
     * данные для проверки берутся из конфига
     */
    public function actionCheckComponents()
    {
        echo "MySQL status: [";
        if ($this->isServiceExists(self::COMPONENT_TYPE_MYSQL))
        {
            $this->printAsPositive("+");
        }
        else
        {
            $this->printAsNegative("-");
        }
        echo "]".PHP_EOL;
        echo "Rabbit-mq status: [";
        if ($this->isServiceExists(self::COMPONENT_TYPE_RABBIT))
        {
             $this->printAsPositive("+");
        }
        else 
        {
            $this->printAsNegative("-");
        }
        echo "]".PHP_EOL;
    }
    
    /**
     * 
     * @param integer $componentType
     * @return boolean 
     */
    protected function isServiceExists($componentType)
    {
        switch ($componentType)
        {
            case self::COMPONENT_TYPE_MYSQL:
                break;
            case self::COMPONENT_TYPE_RABBIT:
                break;
        }
        
        if ($this->isLocalService($componentType))
        {
            echo "local/";
        }
        else
        {
            echo "remote/";
        }
        return true;
    }
    
    /**
     * 
     * @param integer $componentType
     * @return boolean 
     */
    protected function isLocalService($componentType)
    {
        $localhosts = [
            'localhost',
            '127.0.0.1',
            '::1',
        ];
        switch ($componentType)
        {
            case self::COMPONENT_TYPE_MYSQL:
                $dsn = Yii::$app->components['db']['dsn'];
                foreach ($localhosts as $v) {
                    if (strpos($dsn, "host=$v;") !== false)
                    {
                        return true;
                    }
                }
                return false;
            case self::COMPONENT_TYPE_RABBIT:
                $host = Yii::$app->params['rabbit']['host'];
                return in_array($host, $localhosts);
        }
        throw new \Exception("Unknown component type $componentType");
    }
    
    /**
     * Проверка установленного софта (models/commands)
     */
    public function actionCheckApps()
    {
        foreach ($this->thirdPartyApps as $alias) {
            echo "$alias status: [";
            if ($this->isProgramExist($alias))
            {
                $this->printAsPositive("+");
            }
            else
            {
                $this->printAsNegative("-");
            }
            echo "]".PHP_EOL;
        }
    }
    
    /**
     * 
     * @param string $alias
     * @return boolean
     */
    protected function isProgramExist($alias)
    {
        $result = shell_exec("which $alias");
        return strpos($result, $alias) !== false;
    }
    
    public function actionSetup()
    {

        $this->genFileFromConfig(__DIR__.'/../config/db.php', $this->mysqlConfig);
        $this->genFileFromConfig(__DIR__.'/../config/rabbit.php', $this->rabbitConfig);
        
        $secret = bin2hex(random_bytes(10));
        file_put_contents(__DIR__.'/../config/cookieValidationKey.php', sha1($secret));

    }
    
    /**
     * 
     * @param string $filePath
     * @param array $array
     * @return boolean
     */
    protected function genFileFromConfig($filePath, $array)
    {
        $exportedArray = var_export($array, true);
        if (file_exists($filePath))
        {
            unlink($filePath);
        }
        return file_put_contents($filePath, "<?php return $exportedArray;") > 0;
    }
    
    protected function printAsPositive($string)
    {
        echo "\033[01;32m$string\033[0m";
    }
    
    protected function printAsNegative($string)
    {
        echo "\033[01;31m$string\033[0m";
    }
    
    /**
     * 
     * @return boolean
     */
    protected function isRabbitAvailable()
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
        
        return true;
    }
    
    protected function isMysqlAvailable()
    {
        
    }
   
}
