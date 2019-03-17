<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

/**
 * Class tl_member_famulatur
 */
class tl_member_famulatur
{
    /**
     * @param $dc
     */
    public static function onDeleteCallback($dc)
    {
        if ($dc->id != '')
        {
            $objFamulatur = Contao\Database::getInstance()->prepare('SELECT * FROM tl_famulatur_angebot WHERE fd_member=?')->execute($dc->id);
            while ($objFamulatur->next())
            {
                Contao\Database::getInstance()->prepare('DELETE FROM tl_famulatur_angebot WHERE id=?')->execute($objFamulatur->id);
                \Contao\System::log(sprintf('DELETE FROM tl_famulatur_angebot WHERE id=%s', $objFamulatur->id), __METHOD__, TL_GENERAL);
            }
        }
    }
}