<?php

/**
 * @version     vmtitle.php 2016-09-28 12:50:00 UTC zanardi
 * @package     GiBi VMtitle
 * @author      GiBiLogic <info@gibilogic.com>
 * @authorUrl   http://www.gibilogic.com
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die;
require_once __DIR__ . '/vmtitlevirtuemarthelper.php';

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

        $this->virtuemartHelper = new VmtitleVirtuemartHelper();

        $this->setTitle();
        $this->setMetaDescription();
        $this->setMetaKeywords();

        return true;
    }

    /**
     * Set page title
     *
     * @return bool
     */
    private function setTitle()
    {
        // Should we set title by request?
        $title = $this->app->input->get('title', '');
        if ($title) {
            \JFactory::getDocument()->setTitle($title);
            return true;
        }

        // set title by rules according to page type
        switch ($this->app->input->get('view', '')) {
            case 'virtuemart':
                if ($this->params->get('virtuemart_enable', 0)) {
                    $title = $this->buildVirtuemartViewTitle();
                }
                break;

            case 'categories':
                if ($this->params->get('categories_enable', 0)) {
                    $title = $this->buildCategoriesViewTitle();
                }
                break;

            case 'category':
                if ($this->params->get('category_enable', 0)) {
                    $title = $this->buildCategoryViewTitle();
                }
                break;

            case 'productdetails':
                if ($this->params->get('product_enable', 0)) {
                    $title = $this->buildProductDetailsViewTitle();
                }
                break;

            case 'manufacturer':
                if ($this->params->get('manufacturer_enable', 0)) {
                    $title = $this->buildManufacturerViewTitle();
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
     * Set meta description
     *
     * @return bool
     */
    private function setMetaDescription()
    {
        // Should we set meta description by request?
        if ($metadescription = $this->app->input->get('metadescription')) {
            JFactory::getDocument()->setDescription($metadescription);
            return true;
        }

        // set meta description by rules according to page type
        switch ($this->app->input->get('view', '')) {
            case 'productdetails':
                if ($this->params->get('product_metadescription_enable', 0)) {
                    $metadescription = $this->buildProductDetailsViewMetaDescription();
                }
                break;

            case 'manufacturer':
                if ($this->params->get('manufacturer_metadescription_enable', 0)) {
                    $metadescription = $this->buildManufacturerViewMetaDescription();
                }
                break;


            default:
        }

        if ($metadescription) {
            JFactory::getDocument()->setDescription($metadescription);
        }

        return true;
    }

    /**
     * Set meta keywords
     *
     * @return bool
     */
    private function setMetaKeywords()
    {
        // Should we set meta keywords by request?
        if ($metakeywords = $this->app->input->get('metakeywords')) {
            JFactory::getDocument()->setMetaData('keywords', $metakeywords);
            return true;
        }

        // set meta description by rules according to page type
        switch ($this->app->input->get('view', '')) {
            case 'productdetails':
                if ($this->params->get('product_keywords_enable', 0)) {
                    $metakeywords = $this->buildProductDetailsViewMetaKeywords();
                }
                break;
            case 'manufacturer':
                if ($this->params->get('manufacturer_keywords_enable', 0)) {
                    $metakeywords = $this->buildManufacturerViewMetaKeywords();
                }
                break;

            default:
        }

        if ($metakeywords) {
            JFactory::getDocument()->setMetaData('keywords', $metakeywords);
        }

        return true;
    }

    /**
     * Build title for "categories" view
     *
     * @return string
     */
    private function buildCategoriesViewTitle()
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
    private function buildCategoryViewTitle()
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
     * Build title for "productdetails" view
     *
     * @return string
     */
    private function buildProductDetailsViewTitle()
    {
        $title_chunks = array();
        if ($this->params->get('productdetails_name_order', 0)) {
            $title_chunks[$this->params->get('productdetails_name_order')] = $this->virtuemartHelper->getProductName();
        }
        if ($this->params->get('productdetails_manufacturer_order', 0)) {
            $title_chunks[$this->params->get('productdetails_manufacturer_order')] = $this->virtuemartHelper->getManufacturerNameByProduct();
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
     * Build title for "manufacturer" view
     *
     * @return string
     */
    private function buildManufacturerViewTitle()
    {
        $title_chunks = array();
        if ($this->params->get('manufacturer_name_order', 0)) {
            $title_chunks[$this->params->get('manufacturer_name_order')] = $this->virtuemartHelper->getManufacturerName();
        }
        if ($this->params->get('manufacturer_customtext_order', 0)) {
            $title_chunks[$this->params->get('manufacturer_customtext_order')] = $this->params->get('manufacturer_customtext', '');
        }
        ksort($title_chunks);

        return (join(' ', $title_chunks));
    }

    /**
     * Build meta description for "productdetails" view
     *
     * @return string
     */
    private function buildProductDetailsViewMetaDescription()
    {
        $chunks = array();
        if ($this->params->get('productdetails_metadescription_description_order', 0)) {
            $chunks[$this->params->get('productdetails_metadescription_description_order')] = $this->virtuemartHelper->getProductDescription();
        }
        if ($this->params->get('productdetails_metadescription_name_order', 0)) {
            $chunks[$this->params->get('productdetails_metadescription_name_order')] = $this->virtuemartHelper->getProductName();
        }
        if ($this->params->get('productdetails_metadescription_description_order', 0)) {
            $chunks[$this->params->get('productdetails_metadescription_description_order')] = $this->virtuemartHelper->getProductDescription();
        }
        ksort($chunks);

        return (join(' ', $chunks));
    }

    /**
     * Build meta description for "manufacturer" view
     *
     * @return string
     */
    private function buildManufacturerViewMetaDescription()
    {
        $chunks = array();
        if ($this->params->get('manufacturer_metadescription_description_order', 0)) {
            $chunks[$this->params->get('manufacturer_metadescription_description_order')] = $this->virtuemartHelper->getProductDescription();
        }
        if ($this->params->get('manufacturer_metadescription_name_order', 0)) {
            $chunks[$this->params->get('manufacturer_metadescription_name_order')] = $this->virtuemartHelper->getManufacturerName();
        }

        ksort($chunks);

        return (join(' ', $chunks));
    }

    /**
     * Build meta keywords for "productdetails" view
     *
     * @return string
     */
    private function buildProductDetailsViewMetaKeywords()
    {
        $chunks = array();
        if ($this->params->get('productdetails_keywords_name_order', 0)) {
            $chunks[$this->params->get('productdetails_keywords_name_order')] = $this->virtuemartHelper->getProductName();
        }
        if ($this->params->get('productdetails_keywords_manufacturer_order', 0)) {
            $chunks[$this->params->get('productdetails_keywords_manufacturer_order')] = $this->virtuemartHelper->getManufacturerNameByProduct();
        }
        if ($this->params->get('productdetails_keywords_category_order', 0)) {
            $chunks[$this->params->get('productdetails_keywords_category_order')] = $this->virtuemartHelper->getCategoryNameByProduct();
        }

        ksort($chunks);

        return (join(',', $chunks));
    }

    /**
     * Build meta keywords for "manufacturer" view
     *
     * @return string
     */
    private function buildManufacturerViewMetaKeywords()
    {
        $chunks = array();
        if ($this->params->get('manufacturer_keywords_name_order', 0)) {
            $chunks[$this->params->get('manufacturer_keywords_name_order')] = $this->virtuemartHelper->getManufacturerName();
        }
        if ($this->params->get('manufacturer_keywords_description_order', 0)) {
            $chunks[$this->params->get('manufacturer_keywords_description_order')] = implode(",", explode(" ", $this->virtuemartHelper->getManufacturerDescription()));
        }
        if ($this->params->get('manufacturer_keywords_category_order', 0)) {
            $chunks[$this->params->get('manufacturer_keywords_category_order')] = $this->virtuemartHelper->getCategoryNameByManufacturer();
        }

        ksort($chunks);

        return (join(',', $chunks));
    }

    /**
     * Set title for "virtuemart" view
     *
     * @return string
     */
    private function buildVirtuemartViewTitle()
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
