<?php

$installer = $this;
$groupName = 'Informações Fiscais (NFe)';
$entityTypeId = $installer->getEntityTypeId('catalog_product');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);

$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
if(!$attributeGroupId){
  $installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 100);
  $attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
}

$entity = 'catalog_product';

$ignorar_nfe  = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity, 'ignorar_nfe');
if (!$ignorar_nfe->getId()){
  $installer->addAttribute('catalog_product', 'ignorar_nfe', array(
          'group'                => $groupName,
          'label'                => 'Ignorar Produto ao emitir NF-e',
          'type'                 => 'int',
          'input'                => 'boolean',
          'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
          'visible'              => 1,
          'required'             => 0,
          'user_defined'         => 1,
          'searchable'           => 0,
          'filterable'           => 0,
          'comparable'           => 0,
          'visible_on_front'     => 0,
          'source'               => 'eav/entity_attribute_source_boolean',
          'visible_in_advanced_search'    => 0,
          'unique'            => 0,
          'default'            => 0,
  ));
}

$classe_imposto  = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity, 'classe_imposto');
if (!$classe_imposto->getId()){
  $installer->addAttribute('catalog_product', 'classe_imposto', array(
          'group'                => $groupName,
          'label'                => 'Classe de Imposto',
          'type'                 => 'varchar',
          'input'                => 'text',
          'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
          'visible'              => 1,
          'required'             => 0,
          'user_defined'         => 1,
          'searchable'           => 0,
          'filterable'           => 0,
          'comparable'           => 0,
          'visible_on_front'     => 0,
          'visible_in_advanced_search'    => 0,
          'unique'            => 0,
          'default'            => ''
  ));
}


$codigo_ean  = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity, 'codigo_ean');
if (!$codigo_ean->getId()){
  $installer->addAttribute('catalog_product', 'codigo_ean', array(
          'group'                => $groupName,
          'label'                => 'Código de Barras EAN',
          'type'                 => 'varchar',
          'input'                => 'text',
          'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
          'visible'              => 1,
          'required'             => 0,
          'user_defined'         => 1,
          'searchable'           => 0,
          'filterable'           => 0,
          'comparable'           => 0,
          'visible_on_front'     => 0,
          'visible_in_advanced_search'    => 0,
          'unique'            => 0,
          'default'            => ''
  ));
}

$codigo_ncm  = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity, 'codigo_ncm');
if (!$codigo_ncm->getId()){
  $installer->addAttribute('catalog_product', 'codigo_ncm', array(
          'group'                => $groupName,
          'label'                => 'Código NCM',
          'type'                 => 'varchar',
          'input'                => 'text',
          'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
          'visible'              => 1,
          'required'             => 0,
          'user_defined'         => 1,
          'searchable'           => 0,
          'filterable'           => 0,
          'comparable'           => 0,
          'visible_on_front'     => 0,
          'visible_in_advanced_search'    => 0,
          'unique'            => 0,
          'default'            => ''
  ));
}

$codigo_cest  = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity, 'codigo_cest');
if (!$codigo_cest->getId()){
  $installer->addAttribute('catalog_product', 'codigo_cest', array(
          'group'                => $groupName,
          'label'                => 'Código CEST',
          'type'                 => 'varchar',
          'input'                => 'text',
          'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
          'visible'              => 1,
          'required'             => 0,
          'user_defined'         => 1,
          'searchable'           => 0,
          'filterable'           => 0,
          'comparable'           => 0,
          'visible_on_front'     => 0,
          'visible_in_advanced_search'    => 0,
          'unique'            => 0,
          'default'            => ''
  ));
}

$origem_produto  = Mage::getResourceModel('catalog/eav_attribute')->loadByCode($entity, 'origem_produto');
if (!$origem_produto->getId()){
  $installer->addAttribute('catalog_product', 'origem_produto', array(
          'group'                => $groupName,
          'label'                => 'Origem do Produto',
          'type'                 => 'int',
          'input'                => 'select',
          'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
          'visible'              => 1,
          'required'             => 0,
          'user_defined'         => 1,
          'searchable'           => 0,
          'filterable'           => 0,
          'comparable'           => 0,
          'visible_on_front'     => 0,
          'visible_in_advanced_search'    => 0,
          'unique'            => 0,
          'default'            => '',
          'option' =>
            array (
              'values' =>
              array (
                1 => '0 - Nacional, exceto as indicadas nos códigos 3, 4, 5, e 8',
                2 => '1 - Estrangeira - Importação direta, exceto a indicada no código 6',
                3 => '2 - Estrangeira - Adquirida no mercado interno, exceto a indica no código 7',
                4 => '3 - Nacional, mercadoria ou bem com Conteúdo de Importação superior a 40% e inferior ou igual a 70%',
                5 => '4 - Nacional, cuja produção tenha sido feita em conformidade com os processos produtivos básicos de que tratam as legislações citadas nos Ajustes',
                6 => '5 - Nacional, mercadoria ou bem com Conteúdo de Importação inferior ou igual a 40%',
                7 => '6 - Estrangeira - Importação direta, sem similar nacional, constante em lista da CAMEX e gás natural',
                8 => '7 - Estrangeira - Adquirida no mercado interno, sem similar nacional, constante lista CAMEX e gás natural',
                9 => '8 - Nacional, mercadoria ou bem com Conteúdo de Importação superior a 70%',
              ),
            ),
  ));
}
