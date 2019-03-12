<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

namespace Contao;

/**
 * Class FamulaturAngebotModel
 * @package Contao
 */
class FamulaturAngebotModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_famulatur_angebot';

}

class_alias(FamulaturAngebotModel::class, 'FamulaturAngebotModel');
