<?php

namespace Webmaniabr\Nfe\Block\Adminhtml\Form\Field;

class Transportadoras extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {

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
        $this->removeFiels();
    }
    
    /**
     * Retrieve group column renderer
     *
     * @return Customergroup
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                'Webmaniabr\Nfe\Block\Adminhtml\Form\Field\ShippingMethods',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );

        }
        return $this->_groupRenderer;
    }

    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender() {

         $this->addColumn(
            'transportadora_metodo_entrega',
            [
                'label' => __('Método de Entrega'),
                'renderer' => $this->_getGroupRenderer()
            ]
        );
        
        $this->addColumn('transportadora_razao_social', ['label' => __('Nome Completo / Razão Social')]);
        $this->addColumn('transportadora_cnpj', ['label' => __('CPF / CNPJ')]);
        $this->addColumn('transportadora_ie', ['label' => __('Inscrição Estadual')]);
        $this->addColumn('transportadora_endereco', ['label' => __('Endereço')]);
        $this->addColumn('transportadora_cep', ['label' => __('CEP')]);
        $this->addColumn('transportadora_uf', ['label' => __('UF')]);
        $this->addColumn('transportadora_cidade', ['label' => __('Cidade')]);
        


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
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('transportadora_metodo_entrega'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
    
    function removeFiels() {
        ?>
        <style>
        
            #row_webmaniabr_nfe_transportadoras_todas_transportadora_transportadoras .label,
            #row_webmaniabr_nfe_transportadoras_todas_transportadora_transportadoras .scope-label,
            #row_webmaniabr_nfe_transportadoras_todas_transportadora_transportadoras td:nth-child(4)
            { display:none; }
            
            #row_webmaniabr_nfe_transportadoras_todas_transportadora_transportadoras .value {
                width: 100%;
            }
            
            #row_webmaniabr_nfe_transportadoras_todas_transportadora_transportadoras .value td {
                display: table-cell;
            }
        
        </style>
        <?php
    }
}