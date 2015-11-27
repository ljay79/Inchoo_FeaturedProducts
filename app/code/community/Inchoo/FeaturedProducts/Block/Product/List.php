<?php

/**
 * @category     Inchoo
 * @package     Inchoo Featured Products
 * @author        Domagoj Potkoc, Inchoo Team <web@inchoo.net>
 * @modified    Mladen Lotar <mladen.lotar@surgeworks.com>, Vedran Subotic <vedran.subotic@surgeworks.com>
 */
class Inchoo_FeaturedProducts_Block_Product_List extends Mage_Catalog_Block_Product_List
{

    protected $_productCollection;
    protected $_productCategoryCollection;

    protected $_sort_by;

    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label' => Mage::helper('catalog')->__('Home'),
                'title' => Mage::helper('catalog')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ));
        }

        parent::_prepareLayout();
    }

    /*
     * Remove "Position" option from Sort By dropdown
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $toolbar = $this->getToolbarBlock();
        $toolbar->removeOrderFromAvailableOrders('position');
        return $this;
    }

    /*
     * Load featured products collection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();

            $collection->addAttributeToSelect($attributes)
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToFilter(array(
                array( // Flat Catalog Product workaround
                    'attribute' => 'inchoo_featured_product',
                    'eq' => 1
                )
            ), null, 'left')
                ->addStoreFilter();

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    /**
     * Load featured products collection for specified category
     * @param Mage_Catalog_Model_Category $category
     */
    protected function _getProductCollectionByCategory($category)
    {
        if (is_null($this->_productCategoryCollection)) {
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();

            $collection->addCategoryFilter($category)
                ->addAttributeToSelect($attributes)
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToFilter(array(
                    array( // Flat Catalog Product workaround
                        'attribute' => 'inchoo_featured_product',
                        'eq' => 1
                    )
                ), null, 'left')
                ->addStoreFilter()
            ;

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $this->_productCategoryCollection = $collection;
        }

        return $this->_productCategoryCollection;
    }

    /**
     * Retrieve loaded featured products collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getFeaturedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Retrieve loaded featured products collection for current category
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getFeaturedProductCollectionByCategory()
    {
        $categoryId = Mage::registry('current_category')->getId();
        if (null == $categoryId) $categoryId = Mage::app()->getStore()->getRootCategoryId();

        $category = Mage::getModel('catalog/category')->load($categoryId);

        return $this->_getProductCollectionByCategory($category);
    }

    /**
     * Get HTML if there's anything to show
     */
    protected function _toHtml()
    {
        if ($this->_getProductCollection()->count()) {
            return parent::_toHtml();
        }
        return '';
    }
}
