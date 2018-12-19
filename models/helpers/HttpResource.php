<?php
namespace app\models\helpers;

class HttpResource
{

    /**
     *
     * @var array 
     */
    protected static $curlOptions = [
        CURLOPT_CUSTOMREQUEST => "GET", //set request type post or get
        CURLOPT_POST => false, //set to GET
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0', //set user agent
        //CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
        //CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ];

    /**
     *
     * @var array 
     */
    protected static $lastError = [
        'code' => null,
        'message' => null,
    ];

    /**
     * 
     * @param string $url
     * @param array $curlOptions
     * @return string
     */
    public static function get($url, $curlOptions = null)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, self::initOptions($curlOptions));
        $content = curl_exec($ch);
        self::$lastError['code'] = curl_errno($ch);
        self::$lastError['message'] = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        return $content;
    }

    /**
     * 
     * @param array $curlOptions
     * @return array
     */
    protected static function initOptions($curlOptions)
    {
        $a = self::$curlOptions;
        if (is_array($curlOptions) && !empty($curlOptions)) {
            $a = array_merge(self::$curlOptions, $curlOptions);
        }
        return $a;
    }

    /**
     * 
     * @return array
     */
    public static function getLastError()
    {
        return self::$lastError;
    }
}
