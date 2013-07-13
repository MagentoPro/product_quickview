<?php
require_once('Mage/Checkout/controllers/CartController.php');
class MV_Quickview_IndexController extends Mage_Checkout_CartController {

    public function preDispatch() {
        parent::preDispatch();
    }

    public function windowAction() {

        // Get data from request and load product
        $product_id = $this->getRequest()->getParams();
        $product = Mage::getModel('catalog/product')->load($product_id['product_id']);
	  
        // Prepare helper and params
        $params = new Varien_Object();
        $params->setSpecifyOptions(false);
        //$params->setCategoryId($categoryId);
  
        //initilize product and render page
        $viewHelper = Mage::helper('catalog/product_view');
        $viewHelper->prepareAndRender($product->getId(), $this, $params);

    }
	
	public function addAction()
	{
		$cart   = $this->_getCart();
		$params = $this->getRequest()->getParams();
		if($params['isAjax'] == 1){
			$response = array();
			try {
				if (isset($params['qty'])) {
					$filter = new Zend_Filter_LocalizedToNormalized(
					array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$params['qty'] = $filter->filter($params['qty']);
				}

				$product = $this->_initProduct();
				$related = $this->getRequest()->getParam('related_product');

				
			    //Check product availability
				if (!$product) {
					$response['status'] = 'ERROR';
					$response['message'] = $this->__('Unable to find Product ID');
				}

				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}

				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);

				Mage::dispatchEvent('checkout_cart_add_product_complete',
				array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);

				if (!$cart->getQuote()->getHasError()){
					$message = $this->__('%s was added to the shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
					$response['status'] = 'SUCCESS';
					$response['message'] = $message;
                    $response['message'] = '<ul class="messages"><li class="success-msg"><ul><li><span>'.$product->getName().' was added to your shopping cart.</span></li></ul></li></ul>';
					$this->loadLayout();
                    //$this->getLayout()->getBlock('cart_sidebar')->setTemplate('quickview/checkout/cart/sidebar/default.phtml');
					$toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                    $sidecart = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
					//Mage::register('referrer_url', $this->_getRefererUrl());
					$response['toplink'] = $toplink;
                    $response['sidecart'] = $sidecart;
                    
				}
			} catch (Mage_Core_Exception $e) {
				$msg = "";
				if ($this->_getSession()->getUseNotice(true)) {
					$msg = $e->getMessage();
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$msg .= $message.'<br/>';
					}
				}

				$response['status'] = 'ERROR';
				$response['message'] = $msg;
			} catch (Exception $e) {
				$response['status'] = 'ERROR';
				$response['message'] = $this->__('Cannot add the item to shopping cart.');
				Mage::logException($e);
			}
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
			return;
		}else{
			return parent::addAction();
		}
	}
    
    public function removeAction() {
        $id = (int) $this->getRequest()->getParam('itemid');
        $encode = $this->getRequest()->getParam('unec');
        $url = $this->getRequest()->getParam('url');
        Mage::log($encode);
     //$url = Mage::getModel("catalog/category")->load($catId)->getUrl(); 
     //Mage::log($url);
                        $this->_getCart()->removeItem($id)->save();
                        $pieces = explode("/quickview/", $url);
                        $this->_redirect($pieces[1]);
    }
    
    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        $session = Mage::getSingleton('core/session',  array('name'=>'frontend')); Mage::log($session->getData('catid'));
        if($session->getData('catid') && $session->getData('catid') != 0) {
                $catId = $session->getData('catid');
                $url = Mage::getModel('catalog/category')->load($catId)->getUrl();
                $rout = explode(Mage::getBaseUrl(), $url);
                $id = (int) $this->getRequest()->getParam('id');
                if ($id) {
                    try {
                        $this->_getCart()->removeItem($id)->save();
                    } catch (Exception $e) {
                        $this->_getSession()->addError($this->__('Cannot remove the item.'));
                        Mage::logException($e);
                    }
                }
                $this->_redirect($rout[1]);
        }
        else {
            parent::deleteAction();
        }
    }
}