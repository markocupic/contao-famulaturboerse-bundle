<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Classes;

use Contao\Database;
use Contao\Config;
use Contao\FamulaturAngebotModel;
use Contao\System;
use Contao\Input;

/**
 * Class FamulaturHelper
 * @package Markocupic
 */
class FamulaturHelper
{

    public static function generateAlias($varValue, $id)
    {
        $objFamulaturAngebot = FamulaturAngebotModel::findByPk($id);
        if ($objFamulaturAngebot === null)
        {
            throw new \Exception(sprintf('Famulatur Angebot datarecord with ID: %s does not exist.', $id));
        }

        // Closure
        $aliasExists = function (string $alias) use ($objFamulaturAngebot) {
            $objAliasIds = Database::getInstance()->prepare("SELECT id FROM tl_famulatur_angebot WHERE alias=? AND id!=?")
                ->execute($alias, $objFamulaturAngebot->id);

            if (!$objAliasIds->numRows)
            {
                return false;
            }

            return true;
        };

        // Sanitize
        $web = $objFamulaturAngebot->anform_web;
        $web = str_ireplace('http://', $web, '');
        $web = str_ireplace('https://', $web, '');
        $web = str_ireplace('www.', $web, '');
        $web = preg_replace('/\W/', '-', $web);

        $email = $objFamulaturAngebot->anform_email;
        $email = preg_replace('/\W/', '-', $email);

        $varValue = str_ireplace('angebot-', '', $varValue);
        $varValue = str_ireplace('angebot_', '', $varValue);

        // Generate an alias if there is none
        if ($varValue == '')
        {
            if ($web !== '' && !$aliasExists($web))
            {
                $varValue = $web;
            }
            elseif ($email !== '' && !$aliasExists($email))
            {
                $varValue = $email;
            }
            elseif ($web !== '' && !$aliasExists($web . '-' . $objFamulaturAngebot->id))
            {
                $varValue = $web . '-' . $objFamulaturAngebot->id;
            }
            elseif ($email !== '' && !$aliasExists($email . '-' . $objFamulaturAngebot->id))
            {
                $varValue = $email . '-' . $objFamulaturAngebot->id;
            }
            elseif (!$aliasExists('angebot-' . $objFamulaturAngebot->id))
            {
                $varValue = 'angebot-' . $objFamulaturAngebot->id;
            }
            elseif (!$aliasExists('angebot_' . $objFamulaturAngebot->id))
            {
                $varValue = 'angebot_' . $objFamulaturAngebot->id;
            }
            else
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }
        }
        else
        {
            if ($aliasExists($varValue))
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }
        }

        return $varValue;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getValidZipcodesFromOpenGeo()
    {
        $arrConfig = array(
            'dbHost'     => Config::get('openGeoDbHost'),
            'dbPort'     => Config::get('openGeoDbPort'),
            'dbUser'     => Config::get('openGeoDbUser'),
            'dbPass'     => Config::get('openGeoDbPassword'),
            'dbDatabase' => Config::get('openGeoDbDatabase')
        );

        $arrZip = array();

        if (Database::getInstance($arrConfig)->tableExists('zipcode'))
        {
            $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zipcode')->execute();
            while ($objDb->next())
            {
                $arrZip[] = $objDb->zipcode;
            }

            return $arrZip;
        }
        else
        {
            throw new \Exception(sprintf('Could not find database table %s.zipcode. Please check if the database and the table exists.', Config::get('openGeoDbDatabase')));
        }
    }

    /**
     * @param $varValue
     * @return bool
     */
    public static function isValidGermanPostalCode($varValue)
    {
        $arrValidZipCodes = static::getValidZipcodesFromOpenGeo();
        if (!preg_match('/^[0-9]{5}$/', $varValue))
        {
            return false;
        }
        elseif (!in_array($varValue, $arrValidZipCodes))
        {
            return false;
        }
        return true;
    }

    /**
     * @param $intId
     */
    public static function updateLatAndLng($intId)
    {
        $objFamulaturAngebot = Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE id=?')->limit(1)->execute($intId);

        if ($objFamulaturAngebot->numRows)
        {
            if ($objFamulaturAngebot->anform_strasse !== '' && $objFamulaturAngebot->anform_plz !== '' && $objFamulaturAngebot->anform_stadt !== '')
            {
                $strAddress = sprintf('%s,%s %s, Deutschland', $objFamulaturAngebot->anform_strasse, $objFamulaturAngebot->anform_plz, $objFamulaturAngebot->anform_stadt);
                $objOpenCageGeo = new OpenStreetMapGeoCode();
                if ($objOpenCageGeo !== null)
                {
                    $arrCoord = $objOpenCageGeo->getCoordsFromAddress($strAddress, 'de');

                    if ($arrCoord !== null)
                    {
                        $set = [
                            'anform_lat' => $arrCoord['lat'],
                            'anform_lng' => $arrCoord['lng'],
                        ];
                        Input::setPost('anform_lat', $arrCoord['lat']);
                        Input::setPost('anform_lng', $arrCoord['lng']);

                        Database::getInstance()->prepare('UPDATE tl_famulatur_angebot %s WHERE id=?')->set($set)->execute($objFamulaturAngebot->id);
                        System::log(sprintf('tl_famulatur_angebot LAT & LNG with ID: %s has been updated.', $objFamulaturAngebot->id), __METHOD__, TL_GENERAL);
                        return;
                    }
                }
            }

            $set = [
                'anform_lat' => '',
                'anform_lng' => '',
            ];
            Input::setPost('anform_lat', '');
            Input::setPost('anform_lng', '');
            Database::getInstance()->prepare('UPDATE tl_famulatur_angebot %s WHERE id=?')->set($set)->execute($objFamulaturAngebot->id);
            System::log(sprintf('tl_famulatur_angebot LAT & LNG with ID: %s has been updated.', $objFamulaturAngebot->id), __METHOD__, TL_GENERAL);
        }
    }

}

class_alias(FamulaturHelper::class, 'FamulaturHelper');
