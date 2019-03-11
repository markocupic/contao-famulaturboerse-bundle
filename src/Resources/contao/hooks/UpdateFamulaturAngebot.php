<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */


namespace Markocupic\Famulatur\Hooks;

use Contao\Controller;
use Contao\Environment;
use Contao\FamulaturAngebotModel;
use Contao\MemberModel;
use Contao\Module;
use Contao\StringUtil;
use NotificationCenter\Model\Notification;
use Haste\Form\Form;


/**
 * Class UpdateFamulaturAngebot
 * @package Markocupic\Famulatur\Hooks
 */
class UpdateFamulaturAngebot
{

    /**
     * @param FamulaturAngebotModel $objAngebotModel
     * @param MemberModel|null $objMember
     * @param Form $objForm
     * @param Module|null $objModule
     */
    public static function updateFamulaturAngebot(FamulaturAngebotModel $objAngebotModel, MemberModel $objMember = null, Form $objForm, Module $objModule = null)
    {
        global $objPage;

        Controller::loadLanguageFile('tl_famulatur_angebot');

        if ($objModule !== null)
        {
            if ($objModule->notifyOnFamulaturAngebotInserts)
            {
                $objEmail = Notification::findByPk($objModule->insertFamulaturAngebotNotification);
                if ($objEmail !== null)
                {

                    // Set token array
                    $arrTokens = array(
                        'email_sender_name'  => '',
                        'email_sender_email' => '',
                        'reply_to'           => '',
                        'email_subject'      => '',
                        'attachment_tokens'  => '',
                        'link_backend'       => sprintf('%s/contao?do=famulatur_angebotn&act=edit&id=%s', Environment::get('url'), $objAngebotModel->id),
                    );

                    // Add wildcards anform_* & email_text
                    $strText = '';
                    $arrRow = $objAngebotModel->row();
                    $arrAllowedFields = StringUtil::deserialize($objModule->formFields, true);
                    foreach ($arrRow as $k => $v)
                    {

                        if(in_array($k, $arrAllowedFields))
                        {
                            $key = $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$k][0] != '' ? $GLOBALS['TL_LANG']['tl_famulatur_angebot'][$k][0] : $k;
                            $strText .= '[' . $key . ']';
                            $strText .= "\r\n";
                            $strText .= html_entity_decode($v) != '' ? html_entity_decode($v) : '----';
                            $strText .= "\r\n";
                            $strText .= "\r\n";
                        }

                        if (strpos($k, 'anform_') === 0)
                        {
                            $arrTokens[$k] = html_entity_decode($v);
                        }
                    }
                    $arrTokens['email_text'] = $strText;

                    $objEmail->send($arrTokens, $objPage->language);
                }
            }
        }
    }
}