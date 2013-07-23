<?php
/**
 * StoreFront DropShip Commerce Magento Extension
 *
 * @category	SFC
 * @package    	SFC_DropshipCommerce
 * @website 	http://www.storefrontconsulting.com/
 * @copyright 	Copyright (C) 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 */

class MV_Quickview_Model_System_Config_Source_Producttypes
{

  public function toOptionArray()
  {
    return array(
      array('value' => 'simple', 'label' => 'simple'),
      array('value' => 'configurable', 'label' => 'configurable'),
      array('value' => 'grouped', 'label' => 'grouped'),
      array('value' => 'virtual', 'label' => 'virtual'),
      array('value' => 'bundle', 'label' => 'bundle'),
      array('value' => 'downloadable', 'label' => 'downloadable'),
    );
  }
}
