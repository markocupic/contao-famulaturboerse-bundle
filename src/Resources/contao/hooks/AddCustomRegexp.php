<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Markocupic\Famulatur\Hooks;

use Markocupic\Famulatur\Classes\FamulaturHelper;
use Contao\Widget;

/**
 * Class AddCustomRegexp
 * @package Markocupic\Famulatur\Hooks
 */
class AddCustomRegexp
{

    /**
     * Check for a valid german postal code
     * @param $strRegexp
     * @param $varValue
     * @param Widget $objWidget
     * @return bool
     */
    public function isValidGermanPostalCode($strRegexp, $varValue, Widget $objWidget)
    {
        if ($strRegexp === 'germanpostal')
        {
            if (!FamulaturHelper::isValidGermanPostalCode($varValue))
            {
                $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidPostalCode'], $objWidget->label));
                return true;
            }
        }
        return false;
    }
}

class_alias(AddCustomRegexp::class, 'AddCustomRegexp');
