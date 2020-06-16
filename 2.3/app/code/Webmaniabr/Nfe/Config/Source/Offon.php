<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

class Offon implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
      public function toOptionArray()
      {
        return array(
          array('value'=> 0, 'label'=> 'Desativado'),
          array('value'=> 1, 'label'=> 'Ativado'),
        );
      }
}
 
?>