<?php
namespace app\models; 

use \app\models\helpers\HttpResource;

class PageSource
{
    protected $sourceUrl;
    
    protected $sourceContent;
    
    /**
     * 
     * @param string $url
     */
    public function __construct($url)
    {
        $this->sourceUrl = $url;
       
    }
    
    /**
     * загрузка ресурса по урлу через curl 
     * @param array $curlOptions ассоциативный массив для курла
     * @see HttpResource::get
     */
    public function load($curlOptions = null)
    {
        $this->sourceContent = HttpResource::get($this->sourceUrl, $curlOptions);
    }
    
    /**
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->sourceContent;
    }
    
    /**
     * проверка на то что подстрока есть в загруженной странице
     * @param string $substr
     * @return boolean
     */
    public function sourceContains($substr)
    {
        return strpos($this->sourceContent, $substr) !== false;
    }
}
