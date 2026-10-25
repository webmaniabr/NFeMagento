<?php
namespace Webmaniabr\Nfe\Block\Adminhtml\Order\View\Button;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Webmaniabr\Nfe\Helper\NfeData;

class OrderViewButton
{
    protected $nfeData;

    protected $_objectManager;

    public function __construct (NfeData $nfeData) {
      $this->nfeData = $nfeData;
    }

    public function beforeSetLayout(OrderView $subject)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $order_id = (int) $subject->getOrderId();
        if ($order_id < 1) { return; }
        $url = $subject->getUrl('webmaniabrnfe/order/issuenfe', ['order_id' => $order_id]);
        $url = $subject->escapeJs($url);

        $subject->addButton(
            'order_custom_button',
            [
                'label' => __('Emitir NF-e'),
                'class' => __('webmaniabr-emitir-nfe'),
                'id' => 'order-view-webmaniabr-nfe',
                'onclick' => "setLocation('{$url}')"
            ]
        );
    }
}