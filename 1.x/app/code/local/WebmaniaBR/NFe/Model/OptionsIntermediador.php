<?php
class WebmaniaBR_NFe_Model_OptionsIntermediador
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value'=> 0, 'label'=> '0 - Operação sem intermediador (em site ou plataforma própria)'),
      array('value'=> 1, 'label'=> '1 - Operação em site ou plataforma de terceiros (intermediadores/marketplace)'),
    );
  }
}
