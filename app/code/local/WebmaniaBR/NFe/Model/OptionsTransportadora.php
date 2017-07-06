<?php
class WebmaniaBR_NFe_Model_OptionsTransportadora
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {

    $carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
    $shipping_methods = array();
    foreach($carriers as $id => $carrier){
      $title = Mage::getStoreConfig("carriers/$id/title");
      if(!$title) $title = $id;

      $shipping_methods[$id] = $title;

    }

    $options = array();
    $options[] = array(
      'value' => '',
      'label' => 'Selecionar'
    );
    
    foreach($shipping_methods as $id => $title){
      $options[] = array(
        'value' => $id,
        'label' => $title
      );
    }

    return $options;

  }
}
