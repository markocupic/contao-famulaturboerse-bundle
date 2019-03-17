<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

// Config
$GLOBALS['TL_DCA']['tl_member']['config']['ondelete_callback'][] = array('tl_member_famulatur', 'onDeleteCallback');
