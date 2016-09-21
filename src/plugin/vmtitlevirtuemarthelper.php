<?php

/**
 * @version     vmtitlevirtuemarthelper.php 2016-09-21 12:34:00 UTC zanardi
 * @package     GiBi VMtitle
 * @author      GiBiLogic <info@gibilogic.com>
 * @authorUrl   http://www.gibilogic.com
 * @license     GNU/GPL v3 or later
 */
defined('_JEXEC') or die;

class VmtitleVirtuemartHelper
{
    /**
     * Get full category name for current category
     *
     * @return string
     */
    public function getCategoryName()
    {
        $this->loadVirtuemartConfig();
        $category = \VmModel::getModel('category')->getData();

        return $category->category_name;
    }

    /**
     * Get meta title set in VirtueMart for current category
     *
     * @return string
     */
    public function getCategoryMetaTitle()
    {
        $this->loadVirtuemartConfig();
        $category = VmModel::getModel('category')->getData();

        return $category->customtitle;
    }

    /**
     * Get full name for current product
     *
     * @return string
     */
    public function getProductName()
    {
        $this->loadVirtuemartConfig();
        $product = VmModel::getModel('product')->getData();

        return $product->product_name;
    }

    /**
     * Get manufacturer for current product
     */
    public function getManufacturerName()
    {
        $this->loadVirtuemartConfig();
        $product = VmModel::getModel('product')->getData();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('virtuemart_manufacturer_id')
            ->from('#__virtuemart_product_manufacturers')
            ->where('virtuemart_product_id = ' . $product->virtuemart_product_id);
        $db->setQuery($query);
        try {
            $manufacturer = VmModel::getModel('manufacturer')->getData($db->loadResult());

            return $manufacturer->mf_name;
        } catch (\Exception $ex) {
            return '';
        }
    }

    /**
     * Load VirtueMart config, which also initialize VirtueMart internal autoloader
     */
    private function loadVirtuemartConfig()
    {
        if (!class_exists('VmConfig')) {
            require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
        }
        VmConfig::loadConfig();
    }
}
