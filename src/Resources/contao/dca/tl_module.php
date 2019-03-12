<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

// Selectors
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'notifyOnFamulaturAngebotInserts';

// Palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['angebotForm'] = '{title_legend},name,headline,type;{form_legend},autoPublishOnInsert,formFields;{redirect_legend},jumpTo;{notification_legend},notifyOnFamulaturAngebotInserts;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['angebotDashboard'] = '{title_legend},name,headline,type;{redirect_legend},formAngebotJumpTo,readerJumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['angebotList'] = '{title_legend},name,headline,type;{redirect_legend},readerJumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['angebotReader'] = '{title_legend},name,headline,type;{field_legend},formFields;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Subpalettes
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['notifyOnFamulaturAngebotInserts'] = 'insertFamulaturAngebotNotification';

// Fieldss
$GLOBALS['TL_DCA']['tl_module']['fields']['formFields'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['formFields'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => tl_module_famulatur::listFields(),
    'eval'      => array('multiple' => true, 'tl_class' => 'w50'),
    'sql'       => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['autoPublishOnInsert'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['autoPublishOnInsert'],
    'inputType' => 'checkbox',
    'filter'    => true,
    'sorting'   => false,
    'search'    => false,
    'eval'      => array('isBoolean' => true, 'submitOnChange' => false, 'tl_class' => 'clr long'),
    'sql'       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['formAngebotJumpTo'] = array(
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['formAngebotJumpTo'],
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => array('fieldType' => 'radio'),
    'sql'        => "int(10) unsigned NOT NULL default '0'",
    'relation'   => array('type' => 'hasOne', 'load' => 'lazy')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['readerJumpTo'] = array(
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['readerJumpTo'],
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => array('fieldType' => 'radio'),
    'sql'        => "int(10) unsigned NOT NULL default '0'",
    'relation'   => array('type' => 'hasOne', 'load' => 'lazy')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['notifyOnFamulaturAngebotInserts'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['notifyOnFamulaturAngebotInserts'],
    'inputType' => 'checkbox',
    'filter'    => true,
    'sorting'   => false,
    'search'    => false,
    'eval'      => array('isBoolean' => true, 'submitOnChange' => true, 'tl_class' => 'clr long'),
    'sql'       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['insertFamulaturAngebotNotification'] = array(
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['insertFamulaturAngebotNotification'],
    'exclude'    => true,
    'search'     => true,
    'inputType'  => 'select',
    'foreignKey' => 'tl_nc_notification.title',
    'eval'       => array('mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr'),
    'sql'        => "int(10) unsigned NOT NULL default '0'",
    'relation'   => array('type' => 'hasOne', 'load' => 'lazy'),
);
