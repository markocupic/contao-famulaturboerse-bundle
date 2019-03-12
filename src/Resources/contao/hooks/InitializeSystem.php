<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Hooks;

use Contao\Database;
use NotificationCenter\Model\Notification;

/**
 * Class InitializeSystem
 * @package Markocupic\Famulatur\Hooks
 */
class InitializeSystem
{

    /**
     * Migrate data from efg tables tl_formdate and tl_formdata_details
     */
    public function migrateFromEfg()
    {
        // !!!!!!!First manually import tl_formdata and tl_formdata_details with phpmyadmin
        if (TL_MODE === 'BE' && Database::getInstance()->tableExists('tl_formdata') && Database::getInstance()->tableExists('tl_formdata_details'))
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
}

class_alias(InitializeSystem::class, 'InitializeSystem');
