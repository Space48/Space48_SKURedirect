<?php
require_once(Mage::getModuleDir('controllers','Mage_CatalogSearch').DS.'ResultController.php');
class Space48_SKURedirect_ResultController extends Mage_CatalogSearch_ResultController
{

    public function indexAction()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText() != '') {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $query->getQueryText());
                if ($product) {

                    // Make sure the event "catalog_product_load_after" is triggered
                    Mage::dispatchEvent('catalog_product_load_after', array('product' => $product));

                    // Remove the first "/" because _redirect() doesn't like it
                    $redirectURL = $product->getProductURL();
                    if(substr($redirectURL, 0, 1) == '/') {
                        $redirectURL = substr($redirectURL, 1);
                        $this->_redirect($redirectURL);
                    } elseif(substr($redirectURL, 0, 4) == 'http') {
                        $this->_redirectUrl($redirectURL);
                    }
                }

                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                } else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()){
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                } else {
                    $query->prepare();
                }
            }

            Mage::helper('catalogsearch')->checkNotes();

            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();

            if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->save();
            }

        } else {
            $this->_redirectReferer();
        }
    }


}