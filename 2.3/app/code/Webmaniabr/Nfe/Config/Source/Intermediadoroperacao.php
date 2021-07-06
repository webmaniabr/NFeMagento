<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

class Intermediadoroperacao implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
      public function toOptionArray()
      {
        return array(
          array('value'=> '0', 'label'=> '0 - Operação sem intermediador (em site ou plataforma própria)'),
          array('value'=> '1', 'label'=> '1 - Operação em site ou plataforma de terceiros (intermediadores/marketplace)')
        );
      }
}
 
?>