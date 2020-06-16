<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

class Modelo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
      public function toOptionArray()
      {
        return array(
          array('value'=> 1, 'label'=> 'NF-e'),
          array('value'=> 2, 'label'=> 'NFC-e'),
        );
      }
}
 
?>