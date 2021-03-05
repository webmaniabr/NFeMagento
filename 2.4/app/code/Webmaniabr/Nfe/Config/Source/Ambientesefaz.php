<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

class Ambientesefaz implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
      public function toOptionArray()
      {
        return array(
          array('value'=>1, 'label'=>'  Produção'),
          array('value'=>2, 'label'=>'  Desenvolvimento'),
        );
      }
}
 
?>