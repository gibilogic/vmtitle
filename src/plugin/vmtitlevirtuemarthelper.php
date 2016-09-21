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
     * VmtitleVirtuemartHelper constructor.
     */
    public function __construct()
    {
        $this->loadVirtuemartConfig();
    }

    /**
     * Get full category name for current category
     *
     * @return string
     */
    public function getCategoryName()
    {
        $category = VmModel::getModel('category')->getData();

        return $category->category_name;
    }

    /**
     * Get meta title set in VirtueMart for current category
     *
     * @return string
     */
    public function getCategoryMetaTitle()
    {
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
        $product = VmModel::getModel('product')->getData();

        return $product->product_name;
    }

    /**
     * Get description for current product
     *
     * @return string
     */
    public function getProductDescription()
    {
        $product = VmModel::getModel('product')->getData();

        return strip_tags($product->product_desc);
    }

    /**
     * Get short description for current product
     *
     * @return string
     */
    public function getProductShortDescription()
    {
        $product = VmModel::getModel('product')->getData();

        return strip_tags($product->product_s_desc);
    }

    /**
     * Get manufacturer for current product
     */
    public function getManufacturerNameByProduct()
    {
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
     * Get category for current product
     */
    public function getCategoryNameByProduct()
    {
        $product = VmModel::getModel('product')->getData();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('virtuemart_category_id')
            ->from('#__virtuemart_product_categories')
            ->where('virtuemart_product_id = ' . $product->virtuemart_product_id);
        $db->setQuery($query);
        try {
            $category = VmModel::getModel('category')->getData($db->loadResult());
            if (is_array($category)) {
                $category = array_shift($category);
            }

            return $category->category_name;
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
