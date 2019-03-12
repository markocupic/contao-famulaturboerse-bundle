<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['famulatur'] = array(
    'famulatur_angebot' => array
    (
        'tables' => array('tl_famulatur_angebot'),
    )
);

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 3, array
(
    'famulatur' => array
    (
        'angebotForm'      => 'Markocupic\Famulatur\Modules\ModuleAngebotForm',
        'angebotDashboard' => 'Markocupic\Famulatur\Modules\ModuleAngebotDashboard',
        'angebotList'      => 'Markocupic\Famulatur\Modules\ModuleAngebotList',
        'angebotReader'    => 'Markocupic\Famulatur\Modules\ModuleAngebotReader',
    )
));

/**
 * Register HOOKS
 */
$GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'] = array();
$GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'] = array();

$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Markocupic\Famulatur\Hooks\InitializeSystem', 'migrateFromEfg');
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Markocupic\Famulatur\Hooks\InitializeSystem', 'insertDefaultNotification');
$GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'][] = array('Markocupic\Famulatur\Hooks\InsertFamulaturAngebot', 'insertFamulaturAngebot');
$GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'][] = array('Markocupic\Famulatur\Hooks\UpdateFamulaturAngebot', 'updateFamulaturAngebot');

/**
 * Notification_center
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['famulatur'] = array
(
    // Type
    'notification_on_famulatur_insert' => array
    (
        // Field in tl_nc_language
        'email_sender_name'    => array(),
        'email_sender_address' => array(),
        'recipients'           => array('anform_*'),
        'email_replyTo'        => array(),
        'email_recipient_cc'   => array('anform_*'),
        'email_recipient_bcc'  => array('anform_*'),
        'email_subject'        => array('anform_*'),
        'email_text'           => array('email_text', 'anform_*', 'link_backend'),
        'email_html'           => array('email_html', 'anform_*', 'link_backend'),
        'attachment_tokens'    => array()
    )
);
