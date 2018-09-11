<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use DetectCMS\DetectCMS;


class InternalCommandsController extends Controller
{
    /**
     * Поиск CMS для домена
     * @param string $domain
     * @param int $port
     *
     * Название CMS в случае если найдена, пустой вывод в противном случае
     */
    public function actionCmsdetect($url)
    {
        $cms = new DetectCMS($url);
        if ($cms->check())
        {
            $result = $cms->getResult();
            echo $result.PHP_EOL;
        }
    }
}