<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Hooks;

use Contao\Config;
use Contao\Database;
use Contao\System;
use Markocupic\Famulatur\Classes\OpenStreetMapGeoCode;
use NotificationCenter\Model\Notification;

/**
 * Class InitializeSystem
 * @package Markocupic\Famulatur\Hooks
 */
class InitializeSystem
{
    /**
     * @see https://github.com/ratopi/opengeodb/wiki/OpenGeoDB_-_Umkreissuche
     * @see http://opengeodb.org/wiki/OpenGeoDB_-_Umkreissuche
     * @see Download http://www.fa-technik.adfc.de/code/opengeodb/
     * @see http://www.lichtblau-it.de/downloads
     * @throws \Exception
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
        if (Database::getInstance($arrConfig)->tableExists('zip_coordinates'))
        {
            return;
        }

        // Check if database exists
        foreach (['zipcode', 'city'] as $strTable)
        {
            if (!Database::getInstance($arrConfig)->tableExists($strTable))
            {
                throw new \Exception(sprintf('Could not find database table %s.%s. Please check if the database and the table exists.', $strTable, Config::get('openGeoDbDatabase')));
            }
        }
        try
        {
            // Begin transaction
            Database::getInstance()->beginTransaction();

            // Create table zip_coordinates
            $createTableSQL = 'CREATE TABLE zip_coordinates (
            zc_id INT NOT NULL auto_increment PRIMARY KEY,
            zc_loc_id INT NOT NULL ,
            zc_zip VARCHAR( 10 ) NOT NULL ,
            zc_location_name VARCHAR( 255 ) NOT NULL ,
            zc_lat DOUBLE NOT NULL ,
            zc_lon DOUBLE NOT NULL)';
            Database::getInstance($arrConfig)->prepare($createTableSQL)->execute();

            // Insert zipcode.city_id into zip_coordinates.zc_loc_id
            // Insert zipcode.zipcode into zip_coordinates.zc_zip
            $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM zipcode')->execute();
            while ($objDb->next())
            {
                $set = array(
                    'zc_loc_id' => $objDb->city_id,
                    'zc_zip'    => $objDb->zipcode,
                );
                Database::getInstance($arrConfig)->prepare('INSERT INTO zip_coordinates %s')->set($set)->execute();
            }

            // Insert city.name into zip_coordinates.zc_location_name
            // Insert city.lat into zip_coordinates.zc_lat
            // Insert city.lng into zip_coordinates.zc_lng
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

            // Commit transaction
            Database::getInstance()->commitTransaction();
        } catch (Exception $e)
        {
            echo 'An exception has been thrown. We must rollback the transaction.';
            Database::getInstance()->rollbackTransaction();
        }
        return;
    }

    /**
     * Migrate data from efg tables tl_formdate and tl_formdata_details
     */
    public function migrateFromEfg()
    {
        // !!!!!!!First manually import tl_formdata and tl_formdata_details with phpmyadmin
        if (TL_MODE === 'BE' && Database::getInstance()->tableExists('tl_famulatur_angebot') && Database::getInstance()->tableExists('tl_formdata') && Database::getInstance()->tableExists('tl_formdata_details'))
        {
            $arrFields = array();
            $arrFieldList = Database::getInstance()->listFields('tl_famulatur_angebot');
            foreach ($arrFieldList as $field)
            {
                $arrFields[] = $field['name'];
            }

            // Do only migrate if tl_famulatur_angebot is empty
            $objFamulatur = Database::getInstance()->execute('SELECT * FROM tl_famulatur_angebot');
            if (!$objFamulatur->numRows)
            {
                try
                {
                    echo('Migrated data from efg tables (tl_formdata & tl_formdata_details) into tl_famulatur_angebot: ');

                    // Begin transaction
                    Database::getInstance()->beginTransaction();

                    $i = 0;
                    // Get data from tl_formdata
                    $objDb = Database::getInstance()->prepare('SELECT * FROM tl_formdata WHERE form=?')->execute('Angebot');
                    while ($objDb->next())
                    {
                        $i++;
                        $set = $objDb->row();
                        $set['be_notes'] = '';
                        foreach ($set as $k => $v)
                        {
                            if (!in_array($k, $arrFields))
                            {
                                unset($set[$k]);
                            }
                        }
                        Database::getInstance()->prepare('INSERT INTO tl_famulatur_angebot %s')->set($set)->execute();
                        echo '.';
                    }

                    // Get data from tl_formdata_details
                    $objDb = Database::getInstance()->prepare('SELECT * FROM tl_formdata_details ORDER BY pid')->execute();
                    while ($objDb->next())
                    {
                        $objDb2 = Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE id=?')->execute($objDb->pid);
                        if ($objDb2->numRows)
                        {
                            if (in_array($objDb->ff_name, $arrFields))
                            {
                                Database::getInstance()->prepare('UPDATE tl_famulatur_angebot SET ' . $objDb->ff_name . '=? WHERE id=?')->execute($objDb->value, $objDb->pid);
                            }
                        }
                    }
                    // Commit transaction
                    Database::getInstance()->commitTransaction();

                    echo '<br>Finished migration process. Inserted ' . $i . ' records into tl_famulatur_angebot.';
                } catch (Exception $e)
                {
                    echo 'An exception has been thrown. We must rollback the transaction.';
                    Database::getInstance()->rollbackTransaction();
                }
                exit;
            }
        }
    }

    /**
     * Insert default notification
     */
    public function insertDefaultNotification()
    {
        if (TL_MODE === 'BE' && Database::getInstance()->tableExists('tl_nc_notification'))
        {
            // Create default notification
            $objNotification = Notification::findOneByType('notification_on_famulatur_insert');
            if ($objNotification === null)
            {
                $set = array(
                    'type'   => 'notification_on_famulatur_insert',
                    'title'  => 'Benachrichtigung bei INSERTS in der Famulaturboerse',
                    'tstamp' => time()
                );
                $oInsertStmt = Database::getInstance()->prepare('INSERT into tl_nc_notification %s')->set($set)->execute();

                $set = array(
                    'pid'            => $oInsertStmt->insertId,
                    'tstamp'         => time(),
                    'title'          => 'Benachrichtigung bei INSERTS in die Famulaturboerse (vorkonfiguriert)',
                    'gateway'        => 1,
                    'gateway_type'   => 'email',
                    'email_priority' => 3,
                    'email_template' => 'mail_default',
                    'published'      => 1
                );
                $oInsertStmt2 = Database::getInstance()->prepare('INSERT into tl_nc_message %s')->set($set)->execute();

                $set = array(
                    'pid'          => $oInsertStmt2->insertId,
                    'tstamp'       => time(),
                    'gateway_type' => 'email',
                    'language'     => 'de',
                    'fallback'     => '1',
                    'email_mode'   => 'textOnly',
                    'email_text'   => '##email_text##'
                );
                Database::getInstance()->prepare('INSERT into tl_nc_language %s')->set($set)->execute();
            }
        }
    }

    /**
     *
     */
    public function updateLatAndLng()
    {
        if (TL_MODE == 'BE' && Database::getInstance()->tableExists('tl_famulatur_angebot'))
        {
            // Remove lat & lng
            //Database::getInstance()->prepare('UPDATE tl_famulatur_angebot %s')->limit(200)->set(['anform_lat'=>'', 'anform_lng' => ''])->execute();

            $objDatabase = Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE anform_lat=? AND anform_lng=?')->limit(200)->execute('', '');
            while ($objDatabase->next())
            {
                if ($objDatabase->anform_strasse !== '' && $objDatabase->anform_plz !== '' && $objDatabase->anform_stadt !== '')
                {
                    $strAddress = sprintf('%s,%s %s, Deutschland', $objDatabase->anform_strasse, $objDatabase->anform_plz, $objDatabase->anform_stadt);
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
                            Database::getInstance()->prepare('UPDATE tl_famulatur_angebot %s WHERE id=?')->set($set)->execute($objDatabase->id);
                            System::log(sprintf('tl_famulatur_angebot LAT & LNG with ID: %s has been updated.', $objDatabase->id), __METHOD__, TL_GENERAL);
                        }
                    }
                }
            }
        }
    }
}

class_alias(InitializeSystem::class, 'InitializeSystem');
