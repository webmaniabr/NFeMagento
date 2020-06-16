<?php
namespace Webmaniabr\Nfe\Block\Adminhtml\Form\Field;
  
class ListAllShippingMethods extends \Magento\Framework\View\Element\Template
{    
    protected $storeManager;
  
    protected $shippingConfig;
 
    protected $scopeConfig;
          
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }
      
    public function getAllCarriers() {
        $allCarriers = $this->shippingConfig->getAllCarriers($this->storeManager->getStore());
 
        $shippingMethodsArray = array();
        foreach ($allCarriers as $shippigCode => $shippingModel) {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $shippingMethodsArray[$shippigCode] = array(
                'label' => $shippingTitle,
                'value' => $shippigCode
            );
        }
        return $shippingMethodsArray;
    }
  
    public function getActiveCarriers() {
        $activeCarriers = $this->shippingConfig->getActiveCarriers($this->storeManager->getStore());
 
        $shippingMethodsArray = array();
        foreach ($activeCarriers as $shippigCode => $shippingModel) {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippigCode.'/title');
            $shippingMethodsArray[$shippigCode] = array(
                'label' => $shippingTitle,
                'value' => $shippigCode
            );
        }
        return $shippingMethodsArray;
    }
}