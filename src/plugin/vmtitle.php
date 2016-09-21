<?php

/**
 * @version         vmtitle.php 2016-09-21 12:34:00 UTC zanardi
 * @package            GiBi VMtitle
 * @author            GiBiLogic <info@gibilogic.com>
 * @authorUrl        http://www.gibilogic.com
 * @license            GNU/GPL v3 or later
 */
defined('_JEXEC') or die;
require_once __DIR__ .'/vmtitlevirtuemarthelper.php';

class plgSystemVmtitle extends JPlugin
{
    /**
     * @var \JApplicationCms
     */
    private $app;

    /**
     * @var \VmtitleVirtuemartHelper
     */
    private $virtuemartHelper;

    public function __construct($subject, array $config)
    {
        parent::__construct($subject, $config);
        $this->virtuemartHelper = new VmtitleVirtuemartHelper();
    }

    public function onAfterDispatch()
    {
        // frontend only
        $this->app = \JFactory::getApplication();
        if ($this->app->getName() != 'site') {
            return true;
        }

        // VirtueMart only
        if ($this->app->input->get('option', '') != "com_virtuemart") {
            return true;
        }

        $this->setTitle();
        $this->setDescription();

        return true;
    }

    /**
     * Set page title
     *
     * @return bool
     */
    private function setTitle()
    {
        // Should set title by request?
        $title = $this->app->input->get('title', '');
        if ($title) {
            \JFactory::getDocument()->setTitle($title);
            return true;
        }

        // set title by rules according to page type
        switch ($this->app->input->get('view', '')) {
            case 'virtuemart':
                if ($this->params->get('virtuemart_enable', 0)) {
                    $title = $this->setVirtuemartViewTitle();
                }
                break;

            case 'categories':
                if ($this->params->get('categories_enable', 0)) {
                    $title = $this->setCategoriesViewTitle();
                }
                break;

            case 'category':
                if ($this->params->get('category_enable', 0)) {
                    $title = $this->setCategoryViewTitle();
                }
                break;

            case 'productdetails':
                if ($this->params->get('product_enable', 0)) {
                    $title = $this->setProductDetailsViewTitle();
                }
                break;

            default:
        }

        if ($title) {
            JFactory::getDocument()->setTitle($title);
        }

        return true;
    }

    /**
     * Set meta description by request
     *
     * @return bool
     */
    private function setDescription()
    {
        if ($description = $this->app->input->get('metadescription')) {
            JFactory::getDocument()->setDescription($description);
        }

        return true;
    }

    /**
     * Build title for "categories" view
     *
     * @return string
     */
    private function setCategoriesViewTitle()
    {
        $title_chunks = array();
        if ($this->params->get('categories_name_order', 0) != 0) {
            $title_chunks[$this->params->get('categories_name_order')] = $this->virtuemartHelper->getCategoryName();
        }
        if ($this->params->get('categories_sitename_order', 0) != 0) {
            $title_chunks[$this->params->get('categories_sitename_order')] = $this->getSiteName();
        }
        if ($this->params->get('categories_customtext_order', 0) != 0) {
            $title_chunks[$this->params->get('categories_customtext_order')] = $this->params->get('categories_customtext', '');
        }
        ksort($title_chunks);

        return (join(' ', $title_chunks));
    }

    /**
     * Build title for "category" view
     *
     * @return string
     */
    private function setCategoryViewTitle()
    {
        $title_chunks = array();
        if ($this->params->get('category_name_order', 0) != 0) {
            if (($this->params->get('category_metatitle', 0) != 0) && ($metatitle = $this->virtuemartHelper->getCategoryMetaTitle())) {
                $title_chunks[$this->params->get('category_name_order')] = $metatitle;
            } else {
                $title_chunks[$this->params->get('category_name_order')] = $this->virtuemartHelper->getCategoryName();
            }
        }
        if ($this->params->get('category_sitename_order', 0) != 0) {
            $title_chunks[$this->params->get('category_sitename_order')] = $this->getSiteName();
        }
        if ($this->params->get('category_customtext_order', 0) != 0) {
            $title_chunks[$this->params->get('category_customtext_order')] = $this->params->get('category_customtext', '');
        }
        ksort($title_chunks);

        return (join(' ', $title_chunks));
    }

    /**
     * Set title for "productdetails" view
     *
     * @return string
     */
    private function setProductDetailsViewTitle()
    {
        $title_chunks = array();
        if ($this->params->get('productdetails_name_order', 0)) {
            $title_chunks[$this->params->get('productdetails_name_order')] = $this->virtuemartHelper->getProductName();
        }
        if ($this->params->get('productdetails_manufacturer_order', 0)) {
            $title_chunks[$this->params->get('productdetails_manufacturer_order')] = $this->virtuemartHelper->getManufacturerName();
        }
        if ($this->params->get('productdetails_sitename_order', 0)) {
            $title_chunks[$this->params->get('productdetails_sitename_order')] = $this->getSiteName();
        }
        if ($this->params->get('productdetails_customtext_order', 0)) {
            $title_chunks[$this->params->get('productdetails_customtext_order')] = $this->params->get('productdetails_customtext', '');
        }
        ksort($title_chunks);

        return (join(' ', $title_chunks));
    }

    /**
     * Set title for "virtuemart" view
     *
     * @return string
     */
    private function setVirtuemartViewTitle()
    {
        $title_chunks = array();
        if ($this->params->get('virtuemart_sitename_order', 0) != 0) {
            $title_chunks[$this->params->get('virtuemart_sitename_order')] = $this->getSiteName();
        }
        if ($this->params->get('virtuemart_customtext_order', 0) != 0) {
            $title_chunks[$this->params->get('virtuemart_customtext_order')] = $this->params->get('virtuemart_customtext', '');
        }

        ksort($title_chunks);

        return (join(' ', $title_chunks));
    }

    /**
     * Get site name
     *
     * @return string
     */
    private function getSiteName()
    {
        $config = JFactory::getConfig();
        return ($config->get('sitename'));
    }

}
