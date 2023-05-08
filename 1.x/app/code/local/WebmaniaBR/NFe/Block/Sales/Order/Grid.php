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

        $this->getMassactionBlock()->addItem(
            'imprimir_danfe',
            array('label' => $this->__('Imprimir Danfe'),
                  'url'   => $this->getUrl('nfe/standard/imprimirDanfe') //this should be the url where there will be mass operation
            )
        );

        $this->getMassactionBlock()->addItem(
            'imprimir_danfe_simples',
            array('label' => $this->__('Imprimir Danfe Simples'),
                  'url'   => $this->getUrl('nfe/standard/imprimirDanfeSimples') //this should be the url where there will be mass operation
            )
        );

        $this->getMassactionBlock()->addItem(
            'imprimir_danfe_etiqueta',
            array('label' => $this->__('Imprimir Etiqueta'),
                  'url'   => $this->getUrl('nfe/standard/imprimirDanfeEtiqueta') //this should be the url where there will be mass operation
            )
        );

    }
}
