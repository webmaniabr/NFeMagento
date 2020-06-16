<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

class Indicadorescalarelevante implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
      public function toOptionArray()
      {
        return array(
          array('value'=>-1, 'label'=>'Selecionar'),
          array('value'=>'S', 'label'=>'S - Produzido em Escala Relevante'),
          array('value'=>'N', 'label'=> 'N - Produzido em Escala NÃO Relevante'),
        );
      }
}
 
?>