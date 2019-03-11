<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 04.03.2019
 * Time: 21:46
 */

$GLOBALS['TL_DCA']['tl_famulatur_angebot'] = array(
    // Config
    'config'   => array
    (
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'notCopyable'       => true,
        'onsubmit_callback' => array
        (//
        ),
        'onload_callback'   => array
        (
            //array('tl_famulatur_angebot', 'setPalettes'),
            //array('tl_famulatur_angebot', 'deleteUnfinishedAndOldEntries'),
        ),
        'ondelete_callback' => array
        (//
        ),
        'sql'               => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'alias' => 'index',
            ),
        ),
    ),

    // List
    'list'     => array
    (
        'sorting'           => array
        (
            'mode'        => 2,
            'fields'      => array('date DESC'),
            'flag'        => 5,
            'panelLayout' => 'filter;sort,search,limit',
        ),
        'label'             => array
        (
            'fields'      => array('date', 'alias', 'anform_email'),
            'showColumns' => true,
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ),
        ),
        'operations'        => array
        (
            'edit' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ),

            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),

            'toggle' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['toggle'],
                'icon'            => 'visible.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_famulatur_angebot', 'toggleIcon'),
            ),

            'show'    => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ),
            'feature' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['feature'],
                'icon'            => 'featured.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFeatured(this,%s)"',
                'button_callback' => array('tl_famulatur_angebot', 'iconFeatured')
            ),
        ),
    ),

    // Palettes
    'palettes' => array(
        //'__selector__' => array('confirmationSent'),
        'default' => 'date,ip,be_notes,fd_member,fd_member_group,alias,anform_kurzbeschreibung,anform_zusatztext,anform_richtung,anform_schwerpunkte,anform_angebot,anform_erwartung,anform_anforderungen,anform_kvbezirk,anform_hausbesuch,anform_fahrt,anform_nacht,anform_verkehr,anform_praxis,anform_ansprechpartner,anform_strasse,anform_plz,anform_stadt,anform_bundesland,anform_telefon,anform_fax,anform_email,anform_web',
    ),

    // Subpalettes
    //'subpalettes' => array('confirmationSent' => 'confirmationDate'),

    // Fields
    'fields'   => array(

        'id'                      => array('sql' => "int(10) unsigned NOT NULL auto_increment"),
        'tstamp'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['tstamp'],
            'inputType' => 'text',
            'flag'      => 5,
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('rgxp' => 'datim', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ),
        'featured'                => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['featured'],
            'inputType' => 'checkbox',
            'filter'    => true,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('isBoolean' => true, 'submitOnChange' => false, 'tl_class' => 'clr long'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'date'                    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['date'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => true,
            'search'    => false,
            'flag'      => 5,
            'eval'      => array('rgxp' => 'datim', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "int(10) unsigned NULL"
        ),
        'ip'                      => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['ip'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('doNotCopy' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'sorting'                 => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['sorting'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('doNotCopy' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'published'               => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['published'],
            'inputType' => 'checkbox',
            'filter'    => true,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('isBoolean' => true, 'submitOnChange' => true, 'tl_class' => 'clr long'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'confirmationSent'        => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['confirmationSent'],
            'inputType' => 'checkbox',
            'filter'    => true,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('isBoolean' => true, 'submitOnChange' => true, 'tl_class' => 'clr long'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'confirmationDate'        => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['confirmationDate'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => true,
            'sorting'   => false,
            'search'    => false,
            'flag'      => 5,
            'eval'      => array('rgxp' => 'date', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "int(10) unsigned NULL"
        ),
        'be_notes'                => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['be_notes'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => false, 'rows' => 6, 'cols' => 40, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'extnd', 'tl_class' => 'clr long'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'fd_member'               => array(
            'label'      => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['fd_member'],
            'exclude'    => true,
            'inputType'  => 'select',
            'filter'     => true,
            'sorting'    => true,
            'search'     => true,
            'foreignKey' => 'tl_member.username',
            'eval'       => array('multiple' => false, 'includeBlankOption' => true, 'tl_class' => 'w50'),
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => array('type' => 'belongsToOne', 'load' => 'lazy')
        ),
        'fd_member_group'         => array(
            'label'      => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['fd_member_group'],
            'exclude'    => true,
            'inputType'  => 'checkboxWizard',
            'filter'     => true,
            'sorting'    => false,
            'search'     => false,
            'foreignKey' => 'tl_member_group.name',
            'eval'       => array('multiple' => true, 'tl_class' => 'clr'),
            'sql'        => "blob NULL",
            'relation'   => array('type' => 'belongsToMany', 'load' => 'lazy')
        ),
        'alias'                   => array(
            'label'         => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['alias'],
            'exclude'       => true,
            'inputType'     => 'text',
            'filter'        => false,
            'sorting'       => true,
            'search'        => true,
            'eval'          => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'save_callback' => array
            (
                array('tl_famulatur_angebot', 'generateAlias')
            ),
            'sql'           => "varchar(128) BINARY NOT NULL default ''"
        ),
        'anform_kurzbeschreibung' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_kurzbeschreibung'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_zusatztext'       => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_zusatztext'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'extnd', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_richtung'         => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_richtung'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('Allgemeinmediziner/Hausarzt', 'Hausärztlich tätiger Internist', 'Kinder- und Jugendarzt'),
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_schwerpunkte'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_schwerpunkte'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'extnd', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_angebot'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_angebot'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'rows' => 6, 'cols' => 40, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'extnd', 'tl_class' => 'clr long'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_erwartung'        => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_erwartung'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => false, 'rows' => 6, 'cols' => 40, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'extnd', 'tl_class' => 'clr long'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_anforderungen'    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_anforderungen'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_kvbezirk'         => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_kvbezirk'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('Baden-Wuerttemberg', 'Bayern', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hessen', 'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein', 'Westfalen', 'Rheinland-Pfalz', 'Saarland', 'Sachsen', 'Sachsen-Anhalt', 'Schleswig-Holstein', 'Thueringen'),
            'filter'    => true,
            'sorting'   => true,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_hausbesuch'       => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_hausbesuch'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('nein', 'ja'),
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_fahrt'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_fahrt'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('nein', 'ja'),
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'eval'      => array('mandatory' => false, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_nacht'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_nacht'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('nein', 'ja'),
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'eval'      => array('mandatory' => false, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_verkehr'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_verkehr'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('mandatory' => false, 'rows' => 6, 'cols' => 40, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'extnd', 'tl_class' => 'clr long'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_praxis'           => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_praxis'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_ansprechpartner'  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_ansprechpartner'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_strasse'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_strasse'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_plz'              => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_plz'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => true,
            'sorting'   => true,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_stadt'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_stadt'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => true,
            'sorting'   => true,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_bundesland'       => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_bundesland'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('Baden-Wuerttemberg', 'Bayern', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hessen', 'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein-Westfalen', 'Rheinland-Pfalz', 'Saarland', 'Sachsen', 'Sachsen-Anhalt', 'Schleswig-Holstein', 'Thueringen'),
            'filter'    => true,
            'sorting'   => true,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_telefon'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_telefon'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_fax'              => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_fax'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => false,
            'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_email'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_email'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => true,
            'sorting'   => true,
            'search'    => true,
            'eval'      => array('mandatory' => true, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'email', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'anform_web'              => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_web'],
            'exclude'   => true,
            'inputType' => 'text',
            'filter'    => false,
            'sorting'   => false,
            'search'    => true,
            'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'url', 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        /**
         * 'anform_dauer'             => array(
         * 'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_dauer'],
         * 'exclude'   => true,
         * 'inputType' => 'text',
         * 'filter'    => true,
         * 'search'    => true,
         * 'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
         * 'sql'       => "varchar(255) NOT NULL default ''",
         * ),
         * 'anform_zeitraum'          => array(
         * 'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_zeitraum'],
         * 'exclude'   => true,
         * 'inputType' => 'text',
         * 'filter'    => true,
         * 'search'    => true,
         * 'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'date', 'tl_class' => 'w50'),
         * 'sql'       => "varchar(12) NOT NULL default ''",
         * ),
         * 'anform_art'               => array(
         * 'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_art'],
         * 'exclude'   => true,
         * 'inputType' => 'text',
         * 'filter'    => true,
         * 'search'    => true,
         * 'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
         * 'sql'       => "varchar(255) NOT NULL default ''",
         * ),
         *   'anform_bildung'           => array(
         * 'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_bildung'],
         * 'exclude'   => true,
         * 'inputType' => 'text',
         * 'filter'    => true,
         * 'search'    => true,
         * 'eval'      => array('mandatory' => false, 'allowHtml' => false, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
         * 'sql'       => "varchar(255) NOT NULL default ''",
         * ),
         *  'anform_zusatzbezeichnung' => array(
         * 'label'     => &$GLOBALS['TL_LANG']['tl_famulatur_angebot']['anform_zusatzbezeichnung'],
         * 'exclude'   => true,
         * 'inputType' => 'select',
         * 'options'   => array('keine', 'Akupunktur', 'Chirotherapie', 'Palliativmedizin', 'Psychotherapie', 'Sportmedizin', 'andere'),
         * 'filter'    => true,
         * 'search'    => true,
         * 'eval'      => array('tl_class' => 'w50'),
         * 'sql'       => "varchar(255) NOT NULL default ''",
         * ),
         * **/
    )
);

