<?php

include_once('Mage/Checkout/controllers/OnepageController.php');
class Magebright_Productrestriction_OnepageController extends Mage_Checkout_OnepageController
{
    public function saveBillingAction()
    {

        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
         $data = $this->getRequest()->getPost('billing', array());
         $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
         $zipcode=$data['postcode'];
			if($customerAddressId!=''){
				$addressdetail=Mage::getModel('customer/address')->load($customerAddressId);
				$zipcode=$addressdetail->getPostcode();
			}
			
            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
           // print_r($result);
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            //print_r($customer->getData());
            //

            /* Add By Pr */
            //echo "zipcode : ".$data['postcode'];
           if(Mage::getStoreConfig('productrestriction/general/enabled') == 1 ){
           
            $msg = Mage::getStoreConfig('productrestriction/general/zipcode_msg');
            $ids=array();
            $quote = Mage::getSingleton('checkout/session')->getQuote();
       		$cartItems = $quote->getAllVisibleItems();
        	foreach ($cartItems as $item)
        	{
            	$ids[] = $item->getProductId();
            	
            }
                     
            
            if($data['use_for_shipping'] == 1){
            	
	       $result['allow_sections'] = array('shipping');
	       $checkresult= Mage::helper('productrestriction')->checkProductrestrictionData($zipcode,$ids);
	      
              if($checkresult['valid']==0){
              $error_msg=''; 	
              $invalidproduct=explode(',',$checkresult['invalid-product']);
              $j=0;
	              foreach ($invalidproduct as $inproductid){
	              	$product = Mage::getModel('catalog/product')->load($inproductid);
	              	if($j==0){
	              	$error_msg.=$product->getName();
	              	}else{
	              		$error_msg.=','.$product->getName();
	              	}
	              	$j++;
	              }
              $msg='Zipcode is not valid for '.$error_msg.' Please remove it from cart';
	          $result['error']=array("-1");
	          $result['message']=array($msg);
	      }
	    }
      }
            /* Add By Pr */
            
            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    public function saveShippingAction()
    {
     
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $zipcode=$data['postcode'];
			if($customerAddressId!=''){
				$addressdetail=Mage::getModel('customer/address')->load($customerAddressId);
				$zipcode=$addressdetail->getPostcode();
			}
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            /* Add By Pr */
           if(Mage::getStoreConfig('productrestriction/general/enabled') == 1 ){
           	
           	
           
	       $msg = Mage::getStoreConfig('productrestriction/general/zipcode_msg');
	        $ids=array();
            $quote = Mage::getSingleton('checkout/session')->getQuote();
       		$cartItems = $quote->getAllVisibleItems();
        	foreach ($cartItems as $item)
        	{
            	$ids[] = $item->getProductId();
            	
            }
            
            $urlPath = explode(",",$zcode);
             $checkresult= Mage::helper('productrestriction')->checkProductrestrictionData($zipcode,$ids);
			
	      if($checkresult['valid']==0){
	      	
	      	 $error_msg=''; 	
              $invalidproduct=explode(',',$checkresult['invalid-product']);
              $j=0;
	              foreach ($invalidproduct as $inproductid){
	              	$product = Mage::getModel('catalog/product')->load($inproductid);
	              	if($j==0){
	              	$error_msg.=$product->getName();
	              	}else{
	              		$error_msg.=','.$product->getName();
	              	}
	              	$j++;
	              }
              $msg='Zipcode is not valid for '.$error_msg.' Please remove it from cart';
	      	
	       $result['error']=array("-1");
	       $result['message']=array($msg);
	    }
       }
            /* Add By Pr */
            
            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
}