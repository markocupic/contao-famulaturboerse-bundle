<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 04.03.2019
 * Time: 23:20
 */

namespace Markocupic\Famulatur\Classes;

/**
 * Class OpenCageGeoCode
 * @package Markocupic
 */
class OpenCageGeoCode
{
    /**
     * @var
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $requestUrl = 'https://api.opencagedata.com/geocode/v1';

    /**
     * OpenCageGeoCode constructor.
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        if (strlen($apiKey))
        {
            $this->apiKey = $apiKey;
            return $this;
        }
        return null;
    }

    /**
     * @param $strAddress f.ex: Mühlestrasse 2, 6209 Sursee, Switzerland
     * @param string $strLang f.ex: de
     * @return mixed
     */
    public function getCoordsFromAddress($strAddress, $strLang = 'en')
    {
        $strAddress = urlencode($strAddress);
        $apiKey = $this->apiKey;
        $url = sprintf('%s/json?q=%s&key=%s&language=%s&pretty=1', $this->requestUrl, $strAddress, $apiKey, $strLang);

        $buffer = $this->execCurl($url);

        if (!empty($buffer))
        {
            $arrJSON = json_decode($buffer);
            if (is_array($arrJSON->results) && !empty($arrJSON->results))
            {
                $arrResults = $arrJSON->results;
                $objGeo = $arrResults[0];
                $lat = $objGeo->bounds->northeast->lat;
                $lng = $objGeo->bounds->northeast->lng;
                return array('lat' => $lat, 'lng' => $lng);
            }
        }

        return null;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function execCurl($url)
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        return $buffer;
    }

}