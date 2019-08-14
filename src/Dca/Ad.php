<?php

/*
 * mobile.de bundle for Contao Open Source CMS
 *
 * Copyright (c) 2019 pdir / digital agentur // pdir GmbH
 *
 * @package    mobilede-bundle
 * @link       https://pdir.de/mobilede.html
 * @license    proprietary / pdir license - All-rights-reserved - commercial extension
 * @author     Mathias Arzberger <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\MobileDeBundle\Dca;

use Contao\Backend;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\Exception;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Ad
{
    private $Database;

    private $aliasKeys;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->Database = System::importStatic('Database');
        $this->aliasKeys = $container->getParameter('mobile_de_alias_keys');
    }

    /**
     * Auto-generate an article alias if it has not been set yet.
     *
     * @param mixed
     * @param DataContainer
     * @param mixed $varValue
     *
     * @throws Exception
     *
     * @return string
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        $alias = $dc->activeRecord->name;

        if ($this->aliasKeys){
            $keys = explode(",", $this->aliasKeys);
            $arrNewAlias = [];
            foreach($keys as $key)
            {
                $arrNewAlias[] = $dc->activeRecord->{$key};
            }

            $alias = implode("-", $arrNewAlias);
        }

        // Generate an alias if there is none
        if ('' === $varValue) {
            $autoAlias = true;
            $varValue = standardize(StringUtil::restoreBasicEntities($alias));
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_mobile_ad WHERE (id=? OR alias=?)')
            ->execute($dc->id, $varValue);

        // Check whether the page alias exists
        if ($objAlias->numRows > 0) {
            if (!$autoAlias) {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }
            $varValue .= '-'.$dc->id;
        }

        return $varValue;
    }

    /**
     * Auto-generate an article alias if it has not been set yet.
     *
     * @param mixed
     * @param DataContainer
     * @param mixed $varValue
     *
     * @throws Exception
     *
     * @return string
     */
    public function generateAliasFromArr($arr)
    {
        $alias = standardize(StringUtil::restoreBasicEntities($arr));

        if ($this->aliasKeys){
            $keys = explode(",", $this->aliasKeys);
            $arrNewAlias = [];
            foreach($keys as $key)
            {
                $arrNewAlias[] = $arr[$key];
            }

            $alias = implode("-", $arrNewAlias);
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_mobile_ad WHERE alias=?')
            ->execute($alias);
        // Check whether the page alias exists
        if ($objAlias->numRows > 0) {
            $alias .= '-'.mt_rand();
        }

        return $alias;
    }

    /**
     * Return the "toggle visibility" button.
     *
     * @param array  $row
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
        /*
        if (Input::get('tid') !== null && \strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }
        // Check permissions AFTER checking the tid, so hacking attempts are logged
        //if (!$this->User->hasAccess('tl_mobile_ad::published', 'alexf')) {
        //    return '';
        //}
        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);
        if (!$row['published']) {
            $icon = 'invisible.gif';
        }
        return '<a href="' . Backend::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
        */
    }

    /**
     * get vehicle category options by vehicle class.
     *
     * @param  $dc
     *
     * @return array
     */
    public function categoryOptionsCallback(DataContainer $dc)
    {
        return $GLOBALS['TL_LANG']['tl_mobile_ad']['vehicle_category_'.$dc->activeRecord->vehicle_class]['options'];
    }

    /**
     * get vehicle features options by vehicle class.
     *
     * @param  $dc
     *
     * @return array
     */
    public function featuresOptionsCallback(DataContainer $dc)
    {
        return $GLOBALS['TL_LANG']['tl_mobile_ad']['features_'.$dc->activeRecord->vehicle_class]['options'];
    }
}
