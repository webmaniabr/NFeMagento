<?php

namespace Webmaniabr\Nfe\Block\Adminhtml\Form\Field;

use Webmaniabr\Nfe\Block\Adminhtml\Form\Field\ListAllPaymentMethods;

class PaymentMethods extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Shipping Methods Available
     *
     * @var array
     */
    private $_paymentgMethods;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        ListAllPaymentMethods $allPaymentMethods,
        array $data = []
    ) {
        $this->allPaymentMethods = $allPaymentMethods;
        parent::__construct($context, $data);

    }

    /**
     * Retrieve enabled Shipping Methods
     *
     *
     * @return array|string
     */
    protected function _getPaymentMethods()
    {
        
        $allPaymentMethods = $this->allPaymentMethods->getAllMethods();
        
        if( null !== $allPaymentMethods ) {
            
            foreach( $allPaymentMethods as $paymentOptions ) {
                $this->_paymentMethods[$paymentOptions["value"]] = $paymentOptions["label"];
            }
            
        } else {
            
            return null;
            
        }

        return $this->_paymentMethods;
        
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->addOption(
            "nulo",
            "Selecionar"
        );
        
        if( null !== $this->_getPaymentMethods() ) {
            
            foreach ($this->_getPaymentMethods() as $groupId => $groupLabel) {
                $this->addOption($groupId, addslashes($groupLabel));
            }
            
        }

        return parent::_toHtml();
    }
}
