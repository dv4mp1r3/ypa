<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use DetectCMS\DetectCMS;


class InternalCommandsController extends Controller
{
    const PORT_DEFAULT_HTTPS = 443;
    const PORT_DEFAULT_HTTP = 80;

    /**
     * Поиск CMS для домена
     * TODO: глянуть сорсы, возможен ли поиск по нестандартному пути
     * TODO: поиск на нестандартном порту
     * @param string $domain
     * @param int $port
     */
    public function actionCmsdetect($domain, $port = self::PORT_DEFAULT_HTTPS)
    {
        $cms = new DetectCMS($domain);
        if ($cms->check())
        {
            $result = $cms->getResult();
            echo $result.PHP_EOL;
        }
    }
}