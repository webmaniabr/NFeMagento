<?php

class WebmaniaBR_NFe_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->getMassactionBlock()->addItem(
            'nfe',
            array('label' => $this->__('Emitir NF-e'),
                  'url'   => $this->getUrl('nfe/standard/emitir') //this should be the url where there will be mass operation
            )
        );

    }
}
