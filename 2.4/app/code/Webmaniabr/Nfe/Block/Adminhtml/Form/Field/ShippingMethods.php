<?php

namespace Webmaniabr\Nfe\Block\Adminhtml\Form\Field;

use Webmaniabr\Nfe\Block\Adminhtml\Form\Field\ListAllShippingMethods;

class ShippingMethods extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Shipping Methods Available
     *
     * @var array
     */
    private $_shippingMethods;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        ListAllShippingMethods $allShippingMethods,
        array $data = []
    ) {
        $this->allShippingMethods = $allShippingMethods;
        parent::__construct($context, $data);

    }

    /**
     * Retrieve enabled Shipping Methods
     *
     *
     * @return array|string
     */
    protected function _getShippingMethods()
    {
        
        $shippingMethods = $this->allShippingMethods->getActiveCarriers();
        
        if( null !== $shippingMethods ) {
            
            foreach( $shippingMethods as $shippingOptions ) {
                $this->_shippingMethods[$shippingOptions["value"]] = $shippingOptions["label"];
            }
            
        } else {
            
            return null;
            
        }

        return $this->_shippingMethods;
        
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
            "all",
            "Todos"
        );
        
        if( null !== $this->_getShippingMethods() ) {
            
            foreach ($this->_getShippingMethods() as $groupId => $groupLabel) {
                $this->addOption($groupId, addslashes($groupLabel));
            }
            
        }

        return parent::_toHtml();
    }
}
