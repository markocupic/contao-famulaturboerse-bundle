<?php

/*
 * This file is part of Contao Famulaturbörse Bundle.
 *
 * (c) Marko Cupic
 * @author Marko Cupic <https://github.com/markocupic/contao-famulaturboerse-bundle>
 * @license MIT
 */

use Markocupic\Famulatur\Models\FamulaturAngebotModel;

/**
 * Class tl_famulatur_angebot
 */
class tl_famulatur_angebot extends Backend
{

    public function __construct()
    {
        parent::__construct();

        $this->import('BackendUser', 'User');
    }


    /**
     * @param $varValue
     * @param \Contao\DataContainer $dc
     * @return mixed|null|string|string[]
     * @throws Exception
     */
    public function generateAlias($varValue, Contao\DataContainer $dc)
    {

        return Markocupic\Famulatur\Classes\FamulaturHelper::generateAlias($varValue, $dc->id);

    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Contao\Input::get('tid')))
        {
            $this->toggleVisibility(Contao\Input::get('tid'), (Contao\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_famulatur_angebot::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }


    /**
     * @param $intId
     * @param $blnVisible
     * @param DataContainer|null $dc
     */
    public function toggleVisibility($intId, $blnVisible, Contao\DataContainer $dc = null)
    {
        // Set the ID and action
        Contao\Input::setGet('id', $intId);
        Contao\Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_famulatur_angebot']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_famulatur_angebot']['config']['onload_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_famulatur_angebot::published', 'alexf'))
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish Famulatur Angebot ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $this->Database->prepare("SELECT * FROM tl_famulatur_angebot WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Contao\Versions('tl_famulatur_angebot', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_famulatur_angebot']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_famulatur_angebot']['fields']['published']['save_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_famulatur_angebot SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_famulatur_angebot']['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_famulatur_angebot']['config']['onsubmit_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }


    /**
     * Return the "feature/unfeature element" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Contao\Input::get('fid')))
        {
            $this->toggleFeatured(Contao\Input::get('fid'), (Contao\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the fid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_famulatur_angebot::featured', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;fid=' . $row['id'] . '&amp;state=' . ($row['featured'] ? '' : 1);

        if (!$row['featured'])
        {
            $icon = 'featured_.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label, 'data-state="' . ($row['featured'] ? 1 : 0) . '"') . '</a> ';
    }


    /**
     * Feature/unfeature a famulatur item
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param Contao\DataContainer $dc
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleFeatured($intId, $blnVisible, Contao\DataContainer $dc = null)
    {
        // Check permissions to edit
        Contao\Input::setGet('id', $intId);
        Contao\Input::setGet('act', 'feature');

        //$this->checkPermission();

        // Check permissions to feature
        if (!$this->User->hasAccess('tl_famulatur_angebot::featured', 'alexf'))
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to feature/unfeature famulatur angebot item ID ' . $intId . '.');
        }

        $objVersions = new Contao\Versions('tl_famulatur_angebot', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_famulatur_angebot']['fields']['featured']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_famulatur_angebot']['fields']['featured']['save_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_famulatur_angebot SET tstamp=" . time() . ", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }

}
