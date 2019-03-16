<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 04.03.2019
 * Time: 23:20
 */

namespace Markocupic\Famulatur\Classes;

/**
 * Class OpenStreetMapGeoCode
 * @package Markocupic
 */
class OpenStreetMapGeoCode
{

    /**
     * @var string
     */
    protected $requestUrl = 'https://nominatim.openstreetmap.org';

    /**
     * OpenStreetMapGeoCode constructor.
     * @param $apiKey
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * @param $strAddress f.ex: Mühlestrasse 2, 6209 Sursee, Switzerland
     * @param string $strLang f.ex: de
     * @return mixed
     */
    public function getCoordsFromAddress($strAddress)
    {
        $strAddress = urlencode($strAddress);
        $url = sprintf('%s/search?format=json&q=%s', $this->requestUrl, $strAddress);

        $json = $this->execRequest($url);

        if (strlen($json))
        {
            $arrJSON = \GuzzleHttp\json_decode($json);
            if (!empty($arrJSON[0]))
            {
                $objGeo = $arrJSON[0];
                if (is_object($objGeo))
                {
                    if (isset($objGeo->lat) && isset($objGeo->lon))
                    {
                        if (strlen($objGeo->lat) && strlen($objGeo->lon))
                        {
                            return array(
                                'lat' => $objGeo->lat,
                                'lng' => $objGeo->lon,
                            );
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $url
     * @return string
     */
    private function execRequest($url)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $opts = array('http' => array('header' => "User-Agent: $userAgent\r\n"));
        $context = stream_context_create($opts);
        $json = file_get_contents($url, false, $context);
        if ($json !== false)
        {
            return $json;
        }

        return '';
    }

}