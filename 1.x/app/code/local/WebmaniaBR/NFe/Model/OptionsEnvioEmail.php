<?php
class WebmaniaBR_NFe_Model_OptionsEnvioEmail
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value'=> 'off', 'label'=> 'Desativado'),
      array('value'=> 'on', 'label'=> 'Ativado'),
    );
  }
}
