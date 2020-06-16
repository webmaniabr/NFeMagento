<?php

namespace Webmaniabr\Nfe\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;
 
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
 
    /**
     * Attribute set factory
     *
     * @var SetFactory
     */
    private $attributeSetFactory;
    
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;
    
    /**
     *  @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
 
    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param SetFactory $attributeSetFactory
     * @param Attribute $eavAttribute
     */
    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        SetFactory $attributeSetFactory,
        Attribute $eavAttribute,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configWriter = $configWriter;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->scopeConfig = $scopeConfig;
    }
 
    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
 
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        
        /**
         * Check if the Magento has the 4 lines configured
         * If is not, set the option to 4 lines
         */
        $address_lines = $this->scopeConfig->getValue('customer/address/street_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if ( $address_lines < 4 ) {
            $this->configWriter->save('customer/address/street_lines', '4', $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        }

        /**
         * Create custom fields to store the tax information in Product Edit
         */
        
        // New Field: Ignorar NFe
        if ( !$this->eav_attribute_exists("ignorar_nfe") ) {
            
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ignorar_nfe',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Ignorar Produto ao Emitir NF-e',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 1
                ]
            );
            
        }
        
        // New field: Classe de Imposto
        if ( !$this->eav_attribute_exists("classe_imposto" ) ) {
            
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'classe_imposto',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Classe de Imposto',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 2
                ]
            );
            
        }
        
        // New field: EAN (GTIN)
        if ( !$this->eav_attribute_exists("codigo_ean") ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'codigo_ean',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Código de Barras EAN (GTIN)',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 3
                ]
            );
            
        }
        
        // New field: EAN
        if ( !$this->eav_attribute_exists("codigo_ean_tributavel") ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'codigo_ean_tributavel',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'GTIN tributável',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 4
                ]
            );
            
        }
        
        // New field: NCM
        if ( !$this->eav_attribute_exists("codigo_ncm") ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'codigo_ncm',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Código NCM',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 5
                ]
            );
            
        }
        
        // New field: CEST
        if ( !$this->eav_attribute_exists("codigo_cest") ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'codigo_cest',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Código CEST',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 6
                ]
            );
        
        }
        
        // New field: Unidade
        if ( !$this->eav_attribute_exists("unidade_nfe") ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'unidade_nfe',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Unidade',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 7,
                    'option' =>
                        array (
                            'values' =>
                                array (
                                    0 => 'Definir via Configurações Gerais',
                                    1 => 'Unidade',
                                    2 => 'Kilograma',
                                ),
                        ),
                ]
            );
        
        }
            
        // New field: CNPJ do Fabricante
        if ( !$this->eav_attribute_exists("cnpj_fabricante" ) ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'cnpj_fabricante',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'CNPJ do Fabricante',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 8
                ]
            );
            
        }
        
       // New field: Indicador de Escala Relevante
        if ( !$this->eav_attribute_exists("indicador_escala_relevante" ) ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'indicador_escala_relevante',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Indicador de Escala Relevante',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 9,
                    'option' =>
                        array (
                            'values' =>
                                array (
                                    0 => 'Definir via Configurações Gerais',
                                    1 => 'S - Produzido em Escala Relevante',
                                    2 => 'N - Produzido em Escala NÃO Relevante',
                                   
                                ),
                        ),
                ]
            );
            
        }
        
        // New field: Origem do Produto
        if ( !$this->eav_attribute_exists("origem_produto") ) {
                
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'origem_produto',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Origem do Produto',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'sort_order' => 10,
                    'option' =>
                        array (
                            'values' =>
                                array (
                                    0 => 'Definir via Configurações Gerais',
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
                ]
            );
        
        }
        
        /**
         * Create a custom attribute group in all attribute sets
         * And, Add attribute to that attribute group for all attribute sets
         */
 
        // Add this attribute to all attribute sets
        $attributeCodes = array(
                0 => "ignorar_nfe",
                1 => "classe_imposto",
                2 => "codigo_ean",
                3 => "codigo_ean_tributavel",
                4 => "codigo_ncm",
                5 => "codigo_cest",
                6 => "unidade_nfe",
                7 => "cnpj_fabricante",
                8 => "indicador_escala_relevante",
                9 => "origem_produto"
            );
 
        //
        $attributeGroupName = 'Informações Fiscais (NF-e)';
 
        // get the catalog_product entity type id/code
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
 
        // get the attribute set ids of all the attribute sets present in your Magento store
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
 
        foreach($attributeSetIds as $attributeSetId) {
            
            $eavSetup->addAttributeGroup(
                $entityTypeId, 
                $attributeSetId, 
                $attributeGroupName, 
                200
            );
            
            // get the newly create attribute group id
            $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroupName);
 
            // add attribute to group
            foreach ( $attributeCodes as $attributeCode ) {
                
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupName,
                    $attributeCode,
                    null
                );
                
            }

        }
        
        
        $attributeGroupName = 'Informações Fiscais da Categoria (NF-e)';
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        
        $eavSetup->addAttributeGroup(
            $entityTypeId,
            $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Category::ENTITY),
            $attributeGroupName,
            99
        );
        
        if ( !$this->eav_attribute_exists("category_ncm") ) {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'category_ncm',
                [
                    'type'     => 'varchar',
                    'label'    => 'NCM',
                    'input'    => 'text',
                    'source'   => '',
                    'visible'  => true,
                    'default'  => '',
                    'required' => false,
                    'global'   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group'    => $attributeGroupName
                ]
            );
        
        }
        
        /**
         * End setup
         */
        $setup->endSetup();
    }
    
    function eav_attribute_exists( $attributeCode ) {

        $entityType = "catalog_product";

        return $this->_eavAttribute->getIdByCode($entityType, $attributeCode);

    }
}