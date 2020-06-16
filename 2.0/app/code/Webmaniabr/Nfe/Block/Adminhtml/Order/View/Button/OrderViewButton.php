<?php
namespace Webmaniabr\Nfe\Block\Adminhtml\Order\View\Button;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Webmaniabr\Nfe\Helper\NfeData;

class OrderViewButton
{
    public function __construct (NfeData $nfeData) {
      $this->nfeData = $nfeData;
    }

    public function beforeSetLayout(OrderView $subject)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        // Retrieve the order id
        $its_id = false;
        $order_id = "";
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        foreach ( explode("/", $actual_link) as $url_exploded ) {
            
            if ( $its_id ) {
                $order_id = $url_exploded;
                break;
            }
            
            if ( $url_exploded == "order_id" ) {
                $its_id = true;
            }
            
        }
        
        $secret_key = $this->nfeData->manage_secret_key($order_id, "nfe_btn_emitir");
        
        $subject->addButton(
            'order_custom_button',
            [
                'label' => __('Emitir NF-e'),
                'class' => __('webmaniabr-emitir-nfe'),
                'id' => 'order-view-webmaniabr-nfe',
                'onclick' => 'window.open("' . $storeManager->getStore()->getBaseUrl() . 'webmaniabrnfe/index/nfeactions/?nfe_btn_emitir=' . $secret_key . '&order_id=' . $order_id . '")'
            ]
        );
    }
}