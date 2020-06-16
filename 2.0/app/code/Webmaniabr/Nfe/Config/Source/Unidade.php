<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

class Unidade implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
      public function toOptionArray()
      {
        return array(
          array('value'=>1, 'label'=>'Unidade'),
          array('value'=>2, 'label'=>'Kilograma'),
        );
      }
}
 
?>