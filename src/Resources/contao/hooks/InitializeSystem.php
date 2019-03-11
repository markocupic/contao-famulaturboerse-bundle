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
        if (Database::getInstance()->tableExists('tl_formdata') && Database::getInstance()->tableExists('tl_formdata_details'))
        {
            // Do only migrate if tl_famulatur_angebot is empty
            $objFamulatur = Database::getInstance()->execute('SELECT * FROM tl_famulatur_angebot');
            if (!$objFamulatur->numRows)
            {
                // First truncate tl_famulatur_angebot
                Database::getInstance()->execute('TRUNCATE TABLE tl_famulatur_angebot');

                // Get data from tl_formdata
                $objDb = Database::getInstance()->prepare('SELECT * FROM tl_formdata WHERE form=?')->execute('Angebot');
                while ($objDb->next())
                {
                    $set = $objDb->row();
                    $set['be_notes'] = '';
                    Database::getInstance()->prepare('INSERT INTO tl_famulatur_angebot %s')->set($set)->execute();
                }

                // Get data from tl_formdata_details
                $objDb = Database::getInstance()->prepare('SELECT * FROM tl_formdata_details ORDER BY pid')->execute();
                while ($objDb->next())
                {
                    $objDb2 = Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE id=?')->execute($objDb->pid);
                    if ($objDb2->numRows)
                    {
                        Database::getInstance()->prepare('UPDATE tl_famulatur_angebot SET ' . $objDb->ff_name . '=? WHERE id=?')->execute($objDb->value, $objDb->pid);
                    }
                }
            }
        }
    }

    /**
     * Insert default notification
     */
    public function insertDefaultNotification()
    {
        if (Database::getInstance()->tableExists('tl_nc_notification'))
        {
            // Create default notification
            $objNotification = Notification::findOneByType('default_email');
            if ($objNotification === null)
            {
                $set = array(
                    'type'   => 'default_email',
                    'title'  => 'Standard E-Mail (nur mit Platzhaltern)',
                    'tstamp' => time()
                );
                $oInsertStmt = Database::getInstance()->prepare('INSERT into tl_nc_notification %s')->set($set)->execute();

                $set = array(
                    'pid'            => $oInsertStmt->insertId,
                    'tstamp'         => time(),
                    'title'          => 'Standard Nachricht',
                    'gateway'        => 1,
                    'gateway_type'   => 'email',
                    'email_priority' => 3,
                    'email_template' => 'mail_default',
                    'published'      => 1
                );
                $oInsertStmt2 = Database::getInstance()->prepare('INSERT into tl_nc_message %s')->set($set)->execute();

                $set = array(
                    'pid'                  => $oInsertStmt2->insertId,
                    'tstamp'               => time(),
                    'gateway_type'         => 'email',
                    'language'             => 'de',
                    'fallback'             => '1',
                    'recipients'           => '##send_to##',
                    'attachment_tokens'    => '#attachment_tokens##',
                    'email_sender_name'    => '##email_sender_name##',
                    'email_sender_address' => '##email_sender_email##',
                    'email_recipient_cc'   => '##recipient_cc##',
                    'email_recipient_bcc'  => '##recipient_bcc##',
                    'email_replyTo'        => '##reply_to##',
                    'email_subject'        => '##email_subject##',
                    'email_mode'           => 'extOnly',
                    'email_text'           => '##email_text##'
                );
                Database::getInstance()->prepare('INSERT into tl_nc_language %s')->set($set)->execute();
            }
        }
    }
}