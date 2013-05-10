<?php defined('_JEXEC') or die('The way is shut!');
/**
 * @version			  $Id: vmtitle.php 2013-01-11 15:34:00Z zanardi$
 * @package			  GiBi VMtitle
 * @author			  GiBiLogic
 * @authorEmail	  info@gibilogic.com
 * @authorUrl		  http://www.gibilogic.com
 * @license			  GNU/GPL v2 or later
 * @description   Joomla 2.5 en-GB backend system language file
 */

class plgSystemVmtitle extends JPlugin
{
	public function onAfterDispatch()
	{
    // frontend only
		$app = JFactory::getApplication();
		if( $app->getName() != 'site' ) return true;
    
    // VirtueMart only
    if(  JRequest::getCmd('option','') != "com_virtuemart" ) return true;
    
    // Get page type
    $title = null;
    switch( JRequest::getCmd('view','') ) 
    {
      case 'virtuemart':
        if( $this->params->get('virtuemart_enable',0) ) {
          $title = $this->_setVirtuemartViewTitle();
        }
        break;
      
      case 'categories':
        if( $this->params->get('categories_enable',0) ) {
          $title = $this->_setCategoriesViewTitle();
        }
        break;

      case 'category':
        if( $this->params->get('category_enable',0) ) {
          $title = $this->_setCategoryViewTitle();
        }
        break;
      
      case 'productdetails':
        if( $this->params->get('product_enable',0) ) {
          $title = $this->_setProductDetailsViewTitle();
        }
        break;
        
      default:
    }
    
    $document =& JFactory::getDocument();
    if( $title ) $document->setTitle( $title );
    return true;
  }
  
  private function _setCategoriesViewTitle()
  {
    $title_chunks = array();
    if( $this->params->get('categories_name_order',0) != 0 ) {
      $title_chunks[ $this->params->get('categories_name_order') ] = $this->_getCategoryName();
    }
    if( $this->params->get('categories_sitename_order',0) != 0 ) {
      $title_chunks[ $this->params->get('categories_sitename_order') ] = $this->_getSiteName();
    }
    if( $this->params->get('categories_customtext_order',0) != 0 ) {
      $title_chunks[ $this->params->get('categories_customtext_order') ] = $this->params->get('categories_customtext','');
    }
    ksort( $title_chunks );
    return ( join(' ', $title_chunks ) );
  }
  
  private function _setCategoryViewTitle()
  {
    $title_chunks = array();
    if( $this->params->get('category_name_order',0) != 0 ) {
      if(( $this->params->get('category_metatitle',0) != 0 ) && ( $metatitle = $this->_getCategoryMetaTitle() )) {
        $title_chunks[ $this->params->get('category_name_order') ] = $metatitle;
      } else {
        $title_chunks[ $this->params->get('category_name_order') ] = $this->_getCategoryName();
      }
    }
    if( $this->params->get('category_sitename_order',0) != 0 ) {
      $title_chunks[ $this->params->get('category_sitename_order') ] = $this->_getSiteName();
    }
    if( $this->params->get('category_customtext_order',0) != 0 ) {
      $title_chunks[ $this->params->get('category_customtext_order') ] = $this->params->get('category_customtext','');
    }
    ksort( $title_chunks );
    return ( join(' ', $title_chunks ) );
  }
  
  private function _setProductDetailsViewTitle()
  {
    $title_chunks = array();
    if( $this->params->get('productdetails_name_order',0) != 0 ) {
      $title_chunks[ $this->params->get('productdetails_name_order') ] = $this->_getProductName();
    }
    if( $this->params->get('productdetails_sitename_order',0) != 0 ) {
      $title_chunks[ $this->params->get('productdetails_sitename_order') ] = $this->_getSiteName();
    }
    if( $this->params->get('productdetails_customtext_order',0) != 0 ) {
      $title_chunks[ $this->params->get('productdetails_customtext_order') ] = $this->params->get('productdetails_customtext','');
    }
    ksort( $title_chunks );
    return ( join(' ', $title_chunks ) );
  }
  
  private function _setVirtuemartViewTitle()
  {
    $title_chunks = array();
    if( $this->params->get('virtuemart_sitename_order',0) != 0 ) {
      $title_chunks[ $this->params->get('virtuemart_sitename_order') ] = $this->_getSiteName();
    }
    if( $this->params->get('virtuemart_customtext_order',0) != 0 ) {
      $title_chunks[ $this->params->get('virtuemart_customtext_order') ] = $this->params->get('virtuemart_customtext','');
    }
    ksort( $title_chunks );
    return ( join(' ', $title_chunks ) );
  }
  
  private function _getCategoryName()
  {
    if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.'/components/com_virtuemart/helpers/config.php');
    $vm_config = VmConfig::loadConfig();
    $category = VmModel::getModel('category')->getData();
    return $category->category_name;
  }
  
  private function _getCategoryMetaTitle()
  {
    if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.'/components/com_virtuemart/helpers/config.php');
    $vm_config = VmConfig::loadConfig();
    $category = VmModel::getModel('category')->getData();
    return $category->customtitle;
  }

  private function _getProductName()
  {
    if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR.'/components/com_virtuemart/helpers/config.php');
    $vm_config = VmConfig::loadConfig();
    $product = VmModel::getModel('product')->getData();
    return $product->product_name;
  }
  
  private function _getSiteName()
  {
    $config = JFactory::getConfig();
    return( $config->get('sitename') );
  }
}
