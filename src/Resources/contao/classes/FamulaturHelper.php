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
     * @see http://opengeodb.org/wiki/OpenGeoDB_-_Umkreissuche
     * @see Download http://www.fa-technik.adfc.de/code/opengeodb/
     * @see http://www.lichtblau-it.de/downloads
     */
    public static function generateZipcodeTable()
    {
        $arrConfig = array(
            'dbHost'     => Config::get('openGeoDbHost'),
            'dbPort'     => Config::get('openGeoDbPort'),
            'dbUser'     => Config::get('openGeoDbUser'),
            'dbPass'     => Config::get('openGeoDbPassword'),
            'dbDatabase' => Config::get('openGeoDbDatabase')
        );

        $createTableSQL = 'CREATE TABLE zip_coordinates (
            zc_id INT NOT NULL auto_increment PRIMARY KEY,
            zc_loc_id INT NOT NULL ,
            zc_zip VARCHAR( 10 ) NOT NULL ,
            zc_location_name VARCHAR( 255 ) NOT NULL ,
            zc_lat DOUBLE NOT NULL ,
            zc_lon DOUBLE NOT NULL)';
        Database::getInstance($arrConfig)->prepare($createTableSQL)->execute();

        $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zipcode')->execute();
        while ($objDb->next())
        {
            $set = array(
                'zc_loc_id' => $objDb->city_id,
                'zc_zip'    => $objDb->zipcode,
            );
            Database::getInstance($arrConfig)->prepare('INSERT INTO zip_coordinates %s')->set($set)->execute();
        }

        $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zip_coordinates')->execute();
        while ($objDb->next())
        {
            $objDb2 = Database::getInstance($arrConfig)->prepare('SELECT * FROM city WHERE id=?')->execute($objDb->zc_loc_id);
            $set = array(
                'zc_location_name' => $objDb2->name,
                'zc_lat'           => $objDb2->lat,
                'zc_lon'           => $objDb2->lng
            );
            Database::getInstance($arrConfig)->prepare('UPDATE zip_coordinates %s WHERE zc_id=?')->set($set)->execute($objDb->zc_id);
        }
        return;
    }

    /**
     * @return array
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

        $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zipcode')->execute();
        while ($objDb->next())
        {
            $arrZip[] = $objDb->zipcode;
        }

        return $arrZip;
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

}

class_alias(FamulaturHelper::class, 'FamulaturHelper');
