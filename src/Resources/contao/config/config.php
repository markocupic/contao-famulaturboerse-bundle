<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
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


// Front end modules
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

// Register HOOKS
$GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'] = array();
$GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'] = array();


/**
 * Add HOOK
 */
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('Markocupic\Famulatur\Classes\FamulaturHelper', 'migrate');
$GLOBALS['TL_HOOKS']['onInsertFamulaturAngebot'][] = array('Markocupic\Famulatur\Hooks\InsertFamulaturAngebot', 'insertFamulaturAngebot');
$GLOBALS['TL_HOOKS']['onUpdateFamulaturAngebot'][] = array('Markocupic\Famulatur\Hooks\UpdateFamulaturAngebot', 'updateFamulaturAngebot');


// Notification_center
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['recipients'][] = 'anform_*';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_recipient_cc'][] = 'anform_*';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_recipient_bcc'][] = 'anform_*';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_subject'][] = 'anform_*';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_text'][] = 'anform_*';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_html'][] = 'anform_*';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_text'][] = 'link_backend';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['defaultemail']['default_email']['email_html'][] = 'link_backend';


