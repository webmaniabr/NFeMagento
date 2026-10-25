<?php
require_once dirname(__FILE__) . '/../../../../../../inc/Security.php';
class WebmaniaBR_NFe_Block_Adminhtml_Sales_Order_View_Info_Block extends Mage_Core_Block_Template {

    protected $order;

    function get_order_nfe_info(){
      $order = $this->getOrder();
      $data = $order->getData('all_nfe');
      $array = \WebmaniaNFeSecurity::invoices($data);
      return $array;
    }

    function __construct(){

    }

    public function getOrder() {

        if (is_null($this->order)) {
            if (Mage::registry('current_order')) {
                $order = Mage::registry('current_order');
            }
            elseif (Mage::registry('order')) {
                $order = Mage::registry('order');
            }
            else {
                $order = new Varien_Object();
            }
            $this->order = $order;
        }
        return $this->order;
    }
}
