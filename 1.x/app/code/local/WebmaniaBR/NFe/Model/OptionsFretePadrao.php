<?php
class WebmaniaBR_NFe_Model_OptionsFretePadrao
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
        array('value'=>-1, 'label' => 'Não informar'),
        array('value'=>0, 'label' => 'Contratação do Frete por conta do Remetente (CIF)'),
        array('value'=>1, 'label' => 'Contratação do Frete por conta do Destinatário (FOB)'),
        array('value'=>2, 'label' => 'Contratação do Frete por conta de Terceiros'),
        array('value'=>3, 'label' => 'Transporte Próprio por conta do Remetente'),
        array('value'=>4, 'label' => 'Transporte Próprio por conta do Destinatário'),
        array('value'=>9, 'label' => 'Sem Ocorrência de Transporte'),
    );
  }
}
