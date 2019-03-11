<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */


/**
 * Class tl_module_famulatur
 */
class tl_module_famulatur
{
    public static function listFields()
    {
        // Load language file
        \Contao\Controller::loadLanguageFile('tl_famulatur_angebot');

        $arrFields = \Contao\Database::getInstance()->listFields('tl_famulatur_angebot');
        $arrReturn = [];
        foreach ($arrFields as $k => $v)
        {
            if (strpos($v['name'], 'anform_') === 0)
            {
                $arrReturn[$v['name']] = $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$v['name']][0] !== '' ? $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$v['name']][0] : $v['name'];
            }
        }
        return $arrReturn;
    }
}