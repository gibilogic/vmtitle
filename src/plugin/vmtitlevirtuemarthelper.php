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
        $this->manufacturerId = $this->getManufacturerId();
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
     * Get current manufacturer id (if we're in manufacturer view)
     *
     * @return string
     */
    public function getManufacturerId()
    {
        if ($this->app->input->get('view', '') != 'manufacturer') {
            return 0;
        }

        return $this->app->input->get('virtuemart_manufacturer_id');
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
        $product = $this->getProduct($this->productId);

        return $product->product_name;
    }
    
    /**
     * Get full name for current manufacturer
     *
     * @return string
     */
    public function getManufacturerName()
    {
        $manufacturer = $this->getManufacturer($this->manufacturerId);

        return $manufacturer->mf_name;
    }

    /**
     * Get description for current manufacturer
     *
     * @return string
     */
    public function getManufacturerDescription()
    {
        $manufacturer = $this->getManufacturer($this->manufacturerId);

        return strip_tags($manufacturer->mf_desc);
    }

    /**
     * Get description for current product
     *
     * @return string
     */
    public function getProductDescription()
    {
        $product = $this->getProduct($this->productId);
        $description = $product->product_desc;
        if (empty($description) && !empty($product->product_parent_id)) {
            $parentProduct = $this->getProduct($product->product_parent_id);
            $description = $parentProduct->product_desc;
        }

        return strip_tags($description);
    }

    /**
     * Get short description for current product
     *
     * @return string
     */
    public function getProductShortDescription()
    {
        $product = $this->getProduct($this->productId);
        $shortDescription = $product->product_s_desc;
        if (empty($shortDescription) && !empty($product->product_parent_id)) {
            $parentProduct = $this->getProduct($product->product_parent_id);
            $shortDescription = $parentProduct->product_s_desc;
        }

        return strip_tags($shortDescription);
    }

    /**
     * Get manufacturer for current product
     */
    public function getManufacturerNameByProduct()
    {
        $product = $this->getProduct($this->productId);
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
            $virtuemartModelProduct->setId($this->productId);
            $product = $virtuemartModelProduct->getData();
            $categoryIds = $virtuemartModelProduct->getProductCategories($product->product_parent_id, true);
        }

        $categoryNames = array();
        foreach ($categoryIds as $categoryId) {
            $virtuemartModelCategory->setId($categoryId);
            $category = $virtuemartModelCategory->getData();
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
     * Get category name for current manufacturer. If manufacturer belongs to multiple categories, the name will be the
    *  union of all the categories names.
     */
    public function getCategoryNameByManufacturer()
    {
        $manufacturer = $this->getManufacturer($this->manufacturerId);
        /** @var VirtuemartModelManufacturercategories $virtuemartModelManufacturer */
        $virtuemartModelManufacturercategories = VmModel::getModel('manufacturercategories');
        $virtuemartModelManufacturercategories->setId($manufacturer->virtuemart_manufacturercategories_id);
        $virtuemartManufacturerCategory = $virtuemartModelManufacturercategories->getData();

        return $virtuemartManufacturerCategory->mf_category_name;
    }

    /**
     * @param int $productId
     * @return \stdClass mixed
     */
    private function getProduct($productId)
    {
        $productModel = VmModel::getModel('product');
        $productModel->setId($productId);

        return $productModel->getData();
    }
    
    /**
     * @param int $manufacturerId
     * @return \stdClass mixed
     */
    private function getManufacturer($manufacturerId)
    {
        $manufacturerModel = VmModel::getModel('manufacturer');
        $manufacturerModel->setId($manufacturerId);

        return $manufacturerModel->getData();
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
