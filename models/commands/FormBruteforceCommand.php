<?php

namespace app\models\commands;

/**
 * Брутфорс http через форму или гет-запросами
 * todo: тесты
 * @package app\models\commands
 */
class FormBruteforceCommand extends HydraCommand
{

    const FORM_ID = 'form_id';
    const PARAM_NAME = 'name';
    const PARAM_ID = 'id';
    const PARAM_METHOD = 'method';
    const PARAM_ACTION = 'actions';

    const HTTP_METHOD_POST = 'post';
    const HTTP_METHOD_GET = 'get';

    /**
     * @var string
     */
    protected $serializedFormData;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var string
     */
    protected $action;

    public function preExecute()
    {
        $cmd = $this->getCommand()." -L {$this->userList} -P $this->passList -t 4";
        if ($this->useProxy)
        {
            $cmd = "proxychains $cmd";
        }

        $cmd .= " $this->domain";

        if ($this->httpMethod == self::HTTP_METHOD_POST)
        {
            $cmd .= " http-post-form ";
        }
        else if ($this->httpMethod == self::HTTP_METHOD_GET)
        {
            $cmd .= " http-get-form ";
        }

        $cmd .= "$this->action:$this->serializedFormData";

        $this->setCommand($cmd);
    }

    /**
     * @param array $formData
     * @return string
     */
    protected function buildFormData($formData)
    {
        $result = http_build_query($formData);
        return $result;
    }

    public function initParameters($msgBody)
    {
        parent::initParameters($msgBody);
        $this->serializedFormData = $this->buildFormData($msgBody->extra->formData);
        $this->action = $msgBody->extra->action;
        $this->httpMethod = $msgBody->extra->httpMethod;
    }

    public function postExecute()
    {
        // TODO: Implement postExecute() method.
    }

}