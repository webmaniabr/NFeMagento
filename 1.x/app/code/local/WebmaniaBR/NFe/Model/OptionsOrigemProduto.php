<?php
class WebmaniaBR_NFe_Model_OptionsOrigemProduto
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value'=>-1, 'label'=>'Selecionar Origem dos Produtos'),
      array('value'=>0, 'label'=>'0 - Nacional, exceto as indicadas nos códigos 3, 4, 5, e 8'),
      array('value'=>1, 'label'=> '2 - Estrangeira - Adquirida no mercado interno, exceto a indica no código 7'),
      array('value'=>2, 'label'=> '1 - Estrangeira - Importação direta, exceto a indicada no código 6'),
      array('value'=>3, 'label' => '3 - Nacional, mercadoria ou bem com Conteúdo de Importação superior a 40% e inferior ou igual a 70%'),
      array('value'=>4, 'label' => '4 - Nacional, cuja produção tenha sido feita em conformidade com os processos produtivos básicos de que tratam as legislações citadas nos Ajustes'),
      array('value'=>5, 'label' => '5 - Nacional, mercadoria ou bem com Conteúdo de Importação inferior ou igual a 40%'),
      array('value'=>6, 'label' => '6 - Estrangeira - Importação direta, sem similar nacional, constante em lista da CAMEX e gás natural'),
      array('value'=>7, 'label' => '7 - Estrangeira - Adquirida no mercado interno, sem similar nacional, constante lista CAMEX e gás natural'),
      array('value'=>8, 'label' => '8 - Nacional, mercadoria ou bem com Conteúdo de Importação superior a 70%'),
    );
  }
}
