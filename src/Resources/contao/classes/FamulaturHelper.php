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
use Contao\Controller;
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



}