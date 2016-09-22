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
     * @var JApplicationCms
     */
    private $app;

    /**
     * VmtitleVirtuemartHelper constructor.
     */
    public function __construct()
    {
        $this->loadVirtuemartConfig();
        $this->app = \JFactory::getApplication();
        $this->productId = $this->getProductId();
        $this->categoryId = $this->getCategoryId();
    }

    /**
     * Get current product id (if we're in productdetails view)
     *
     * @return string
     */
    public function getProductId()
    {
        if ($this->app->input->get('view', '') != 'productdetails') {
            return 0;
        }

        return $this->app->input->get('virtuemart_product_id');
    }

    /**
     * Get current category id (if we're in category view)
     *
     * @return string
     */
    public function getCategoryId()
    {
        if ($this->app->input->get('view', '') != 'category') {
            return 0;
        }

        return $this->app->input->get('virtuemart_category_id');
    }

    /**
     * Get full category name for current category
     *
     * @return string
     */
    public function getCategoryName()
    {
        $category = VmModel::getModel('category')->getData($this->categoryId);

        return $category->category_name;
    }

    /**
     * Get meta title set in VirtueMart for current category
     *
     * @return string
     */
    public function getCategoryMetaTitle()
    {
        $category = VmModel::getModel('category')->getData($this->categoryId);

        return $category->customtitle;
    }

    /**
     * Get full name for current product
     *
     * @return string
     */
    public function getProductName()
    {


        $product = VmModel::getModel('product')->getData($this->productId);

        return $product->product_name;
    }

    /**
     * Get description for current product
     *
     * @return string
     */
    public function getProductDescription()
    {
        $product = VmModel::getModel('product')->getData($this->productId);

        return strip_tags($product->product_desc);
    }

    /**
     * Get short description for current product
     *
     * @return string
     */
    public function getProductShortDescription()
    {
        $product = VmModel::getModel('product')->getData($this->productId);

        return strip_tags($product->product_s_desc);
    }

    /**
     * Get manufacturer for current product
     */
    public function getManufacturerNameByProduct()
    {
        $product = VmModel::getModel('product')->getData($this->productId);

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
     * Get category name for current product. If product belongs to multiple categories, the name will be the union
     * of all the categories names.
     */
    public function getCategoryNameByProduct()
    {
        $virtuemartModelProduct = VmModel::getModel('product');
        $virtuemartModelCategory = VmModel::getModel('category');
        $categoryIds = $virtuemartModelProduct->getProductCategories($this->productId, true);
        if (empty($categoryIds)) {
            $product = VmModel::getModel('product')->getData($this->productId);
            $categoryIds = $virtuemartModelProduct->getProductCategories($product->product_parent_id, true);
        }

        $categoryNames = array();
        foreach ($categoryIds as $categoryId) {
            $virtuemartModelCategory->setId($categoryId);
            $category = VmModel::getModel('category')->getData();
            if (empty($category)) {
                continue;
            }
            if (is_array($category)) {
                $category = array_shift($category);
            }
            $categoryNames[] = $category->category_name;
        }

        return implode(",",$categoryNames);
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
