<?php
class Magebright_Productrestriction_Helper_Data extends Mage_Core_Helper_Abstract
{
     public function getProductrestrictionData($productrestriction_id)
	{
		$valArray = $productrestriction_id;
		$collection = Mage::getModel('productrestriction/productrestriction')->getCollection()
		->addFieldToFilter('productrestriction_id',$productrestriction_id);

		return $collection;
	}
    public function checkProductrestrictionData($zipcode,$productId)
	{

	$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
	$collection->addFieldToFilter('pin_code',$zipcode);
	$collection->getSelect()->limit(1);
	$zipcodedata=$collection->getData();
    // print_r($zipcodedata);



	$isProduct=0;
	$invalidproduct=0;
	if(count($productId)>0 && $collection->getSize()>0){

		 /*$productArray=explode(',',$zipcodedata[0]['product_id']);

			foreach ($productId as $prodval){
			 if(!in_array($prodval,$productArray)){
			 	$isProduct++;
			 	$invalidproduct.=$prodval.',';
			 }
			} */
            $invalidproduct= $this->checkProductZipcode($zipcode,$productId);
		}

	$response = array();
	if($invalidproduct==''){

	if($collection->getSize()>0)
	{


		foreach($collection as $zipdata)
		{
			$DeliveryDays = $zipdata->getDeliveryDays();
			$city = $zipdata->getCity();
			$cashod= $zipdata->getCod();
		}

		$response['valid'] = 1;
		$response['Delivery_Days'] = $DeliveryDays;
		$response['city'] = $city;
		$response['cod-valid'] = $cashod;
		if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
		$response['cod'] = $cmsg;
	
	}
	else
	{
		$flag=0;
		/*Check for * validation */
		$key="*";
		$starcollection = Mage::getModel('productrestriction/productrestriction')->getCollection();
		//$collection->addFieldToFilter('pin_code',$zipcode);
		$starcollection->addFieldToFilter('pin_code',array('like'=>'%'.$key.'%'));

		if($starcollection->getSize()>0)
		{

			foreach($starcollection as $zipdata)
			{
			   $COD = $zipdata->getPinCode();
			 $starpos=stripos($COD,"*");

		   $admincode=substr($COD,0,$starpos);
		  $frontcode=substr($zipcode,0,$starpos);
				
				if($admincode  == $frontcode){
					  $invalidproduct= $this->checkProductZipcode($COD,$productId);
					if($invalidproduct==''){
						$DeliveryDays = $zipdata->getDeliveryDays();
						$FINALCOD = $zipdata->getCity();
						$cashod=$zipdata->getCod();
						
						$response['valid'] = 1;
						$response['Delivery_Days'] = $DeliveryDays;
						$response['city'] = $FINALCOD;
						$response['cod-valid'] = $cashod;
						if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
						$response['cod'] = $cmsg;
						$flag=1;
						break;
					}
				}
			}
		}
		
		/*Check for – validation */
		if($flag==0){

			$key='-';
			$descollection = Mage::getModel('productrestriction/productrestriction')->getCollection();
			//$collection->addFieldToFilter('pin_code',$zipcode);
			$descollection->addFieldToFilter('pin_code',array('like'=>'%'.$key.'%'));
			
			if($descollection->getSize()>0)
			{
				foreach($descollection as $szipdata)
				{
					$zipcodestr = $szipdata->getPinCode();
					$desadmincode = explode("-", $zipcodestr);
					$desstart=$desadmincode[0];
					$desend=$desadmincode[1];
					if($zipcode >= $desstart &&  $zipcode <= $desend){
					
					  $invalidproduct= $this->checkProductZipcode($zipcodestr,$productId);
				  if($invalidproduct==''){
							$des_DeliveryDays = $szipdata->getDeliveryDays();
							$DES_FINALCOD = $szipdata->getCity();
							$cashod=$szipdata->getCod();
							
							$response['valid'] = 1;
							$response['Delivery_Days'] = $des_DeliveryDays;
							$response['city'] = $DES_FINALCOD;
							$response['cod-valid'] = $cashod;
							if($cashod==1){$cmsg='Available';}else{$cmsg='Not Available';}
							$response['cod'] = $cmsg;
							$flag=1;
							break;
						  }
					}
				
				}
			}
		
		}
		
		if($flag==0){
		$response['valid'] = 0;
		$response['cod-valid'] = 0;
        $response['invalid-product']=$invalidproduct;
		}
	}
	
	}else{
		$response['valid'] = 0;
		$response['cod-valid'] = 0;
		$response['invalid-product']=$invalidproduct;
	}
	return $response;
		
    }
    
    public function checkCOD($zipcode){
    	
    	
    	$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
	$collection->addFieldToFilter('pin_code',$zipcode)
	->addFieldToFilter('cod',1);
	$collection->getSelect()->limit(1);
	$collection->getSize();
		if($collection->getSize()>0)
		{
			return 1;
		}else{
			return 0;
		}
	  
    }

    public function checkProductZipcode($zipcode,$productId){
                  /*	$collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
	$collection->addFieldToFilter('pin_code',$zipcode);
	$collection->getSelect()->limit(1);
	$zipcodedata=$collection->getData();*/
    //echo $zipcode;
       $zipcode_product = Mage::getModel('productrestriction/zipcodeproduct')->getCollection()
       ->addFieldToFilter('pin_code',$zipcode);
       $zipcode_product->getSelect()->where('product_id in (?)', $productId);

   /* echo $productdata=$zipcode _product->getSelect()->join(array('prodzipcode'=>'zipcodeproduct'),
'main_table.pin_code = prodzipcode.pin_code',
array('prodzipcode.*'))->where('main_table.pin_code ='. $zipcode)->where('prodzipcode.product_id in (?)', $productId);
*/
    // print_r($zipcode_product->getData());

	$isProduct=0;
    $validproduct=array();
	$invalidproduct='';
	if(count($productId)>0 && $zipcode_product->getSize()>0){
    foreach ($zipcode_product as $validpro){

			 	$validproduct[]=$validpro->getProductId();

			}
		}

        	foreach ($productId as $prodval){

			 if(!in_array($prodval,$validproduct)){
			 	$isProduct++;
			 	$invalidproduct.=$prodval[0].',';
			 }
			}

      return  $invalidproduct;
    }

    public function checkZipcodeForStore($zipcode){

    $collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
	$collection->addFieldToFilter('pin_code',$zipcode);
	$collection->getSelect()->limit(1);
	$storeId = Mage::app()->getStore()->getStoreId();
	$data=$collection->getData();
	$default_store=explode(',',$data[0]['store_id']);
		
		if(in_array($storeId,$default_store))
		{
			return  1; 
		 }else{ 
		 	return  0;
	     }
     }

     public function deleteProductZipcode($zipcode){
                     $collection_del= Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',array('eq'=>$zipcode));
                     $collection_del->walk('delete');
     }
  
}