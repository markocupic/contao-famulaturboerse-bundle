<?php
/**
 * Created by PhpStorm.
 * User: Marko
 * Date: 04.03.2019
 * Time: 23:20
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


    public function migrate()
    {
        return;
        /**
         * $arrConfig = array(
         * 'dbHost'     => Config::get('dbHost'),
         * 'dbPort'     => Config::get('dbPort'),
         * 'dbUser'     => 'aeracing_opengeo',
         * 'dbPass'     => 'WUa?7Zy;2Jy:6V',
         * 'dbDatabase' => 'aeracing_opengeo'
         * );
         *
         * $objDb = Database::getInstance($arrConfig)->prepare('SELECT * FROM city ORDER BY name')->execute();
         * while($objDb->next())
         * {
         * echo $objDb->name . '<br>';
         * }*/

        Controller::loadDataContainer('tl_famulatur_angebot');
        foreach ($GLOBALS['TL_DCA']['tl_famulatur_angebot']['fields'] as $k => $v)
        {
            //echo sprintf('$GLOBALS[\'TL_LANG\'][\'tl_famulatur_angebot\'][\'%s\'] = array(\'%s\', \'\');', $k, $k);
            //echo '<br>';
        }


        // !!!!!!!First import tl_formdate and tl_formdata_details with phpmyadmin if the tables do not exist


        // First truncate tl_famulatur_angebot
        Database::getInstance()->prepare('TRUNCATE TABLE tl_famulatur_angebot')->execute();

        $objDb = Database::getInstance()->prepare('SELECT * FROM tl_formdata WHERE form=?')->execute('Angebot');
        while ($objDb->next())
        {
            $set = $objDb->row();
            //echo print_r($set,true) . '<br>';
            $set['be_notes'] = '';
            Database::getInstance()->prepare('INSERT INTO tl_famulatur_angebot %s')->set($set)->execute();

        }

        $objDb = Database::getInstance()->prepare('SELECT * FROM tl_formdata_details ORDER BY pid')->execute();
        while ($objDb->next())
        {
            $objDb2 = Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE id=?')->execute($objDb->pid);
            if ($objDb2->numRows)
            {
                //echo sprintf('UPDATE tl_famulatur_angebot SET %s=%s WHERE id=%s', $objDb->ff_name, $objDb->value, $objDb->pid) . '<br>';
                $objDb3 = Database::getInstance()->prepare('UPDATE tl_famulatur_angebot SET ' . $objDb->ff_name . '=? WHERE id=?')->execute($objDb->value, $objDb->pid);
            }
        }
    }
}