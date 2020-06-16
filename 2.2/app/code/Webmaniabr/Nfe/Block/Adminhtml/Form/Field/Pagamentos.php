<?php

namespace Webmaniabr\Nfe\Block\Adminhtml\Form\Field;

class Pagamentos extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {

    /**
     * @var bool
     */
    protected $_addAfter = TRUE;

    /**
     * @var
     */
    protected $_addButtonLabel;

    /**
     * @var Customergroup
     */
    protected $_groupRenderer;
    
    /**
     * Construct
     */
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }
    
    /**
     * Retrieve group column renderer
     *
     * @return Customergroup
     */
    protected function _getGroupRenderer($type = null)
    {
        
        if ( $type == "metodo" ) {
            
            $this->_groupRenderer1 = $this->getLayout()->createBlock(
                'Webmaniabr\Nfe\Block\Adminhtml\Form\Field\PaymentMethods',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            
            return $this->_groupRenderer1;
            
        } else {
            
            $this->_groupRenderer2 = $this->getLayout()->createBlock(
                'Webmaniabr\Nfe\Block\Adminhtml\Form\Field\PaymentTypes',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            
            return $this->_groupRenderer2;
            
        }

        
    }
    
    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender() {

         $this->addColumn(
            'metodo_pagamento',
            [
                'label' => __('Método de Pagamento'),
                'renderer' => $this->_getGroupRenderer("metodo")
            ]
        );
        
         $this->addColumn(
            'forma_pagamento',
            [
                'label' => __('Formas de Pagamento'),
                'renderer' => $this->_getGroupRenderer("forma")
            ]
        );

        $this->_addAfter       = FALSE;
        $this->_addButtonLabel = __('Add');
    }
    
    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        if ( null !== $row->getData('metodo_pagamento') ) {
            $customAttribute = $row->getData('metodo_pagamento');
            $key = 'option_' . $this->_groupRenderer1->calcOptionHash($customAttribute);
            $options[$key] = 'selected="selected"';
            $row->setData('option_extra_attrs', $options);
        } 
        
        if ( null !== $row->getData('forma_pagamento') ) {
            $customAttribute = $row->getData('forma_pagamento');
            $key = 'option_' . $this->_groupRenderer2->calcOptionHash($customAttribute);
            $options[$key] = 'selected="selected"';
            $row->setData('option_extra_attrs', $options);
        }

    }

}