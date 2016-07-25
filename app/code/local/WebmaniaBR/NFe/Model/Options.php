<?php
class WebmaniaBR_NFe_Model_Options
{
  /**
   * Provide available options as a value/label array
   *
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
