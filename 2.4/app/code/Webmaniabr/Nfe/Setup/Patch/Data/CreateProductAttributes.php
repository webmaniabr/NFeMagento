<?php declare(strict_types=1);

namespace Webmaniabr\Nfe\Setup\Patch\Data;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Creates product and category attributes for NFe functionality
 */
class CreateProductAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param SetFactory $attributeSetFactory
     * @param Attribute $eavAttribute
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        SetFactory $attributeSetFactory,
        Attribute $eavAttribute,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavAttribute = $eavAttribute;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Run code inside patch
     */
    public function apply()
    {
        // Start setup
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

            // Configure address lines
            $this->configureAddressLines();

            // Create product attributes
            $this->createProductAttributes($eavSetup, $categorySetup);

            // Create category attributes
            $this->createCategoryAttributes($eavSetup);

        } catch (Exception $e) {
            $this->logger->error('WebmaniaBR NFe Setup Error: ' . $e->getMessage());
            throw $e;
        }

        // End setup
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Configure address lines to 4 if not already set
     */
    private function configureAddressLines(): void
    {
        $addressLines = $this->scopeConfig->getValue(
            'customer/address/street_lines',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($addressLines < 4) {
            $this->configWriter->save(
                'customer/address/street_lines',
                '4',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
    }

    /**
     * Create product attributes for NFe
     *
     * @param EavSetup $eavSetup
     * @param mixed $categorySetup
     */
    private function createProductAttributes(EavSetup $eavSetup, $categorySetup): void
    {
        $attributes = [
            'ignorar_nfe' => [
                'type' => 'int',
                'label' => 'Ignorar Produto ao Emitir NF-e',
                'input' => 'boolean',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'default' => '',
                'sort_order' => 1
            ],
            'classe_imposto' => [
                'type' => 'varchar',
                'label' => 'Classe de Imposto',
                'input' => 'text',
                'sort_order' => 2
            ],
            'codigo_ean' => [
                'type' => 'varchar',
                'label' => 'Código de Barras EAN (GTIN)',
                'input' => 'text',
                'sort_order' => 3
            ],
            'codigo_ean_tributavel' => [
                'type' => 'varchar',
                'label' => 'GTIN tributável',
                'input' => 'text',
                'sort_order' => 4
            ],
            'codigo_ncm' => [
                'type' => 'varchar',
                'label' => 'Código NCM',
                'input' => 'text',
                'sort_order' => 5
            ],
            'codigo_cest' => [
                'type' => 'varchar',
                'label' => 'Código CEST',
                'input' => 'text',
                'sort_order' => 6
            ],
            'unidade_nfe' => [
                'type' => 'int',
                'label' => 'Unidade',
                'input' => 'select',
                'default' => '0',
                'sort_order' => 7,
                'option' => [
                    'values' => [
                        0 => 'Definir via Configurações Gerais',
                        1 => 'Unidade',
                        2 => 'Kilograma',
                    ],
                ],
            ],
            'cnpj_fabricante' => [
                'type' => 'varchar',
                'label' => 'CNPJ do Fabricante',
                'input' => 'text',
                'sort_order' => 8
            ],
            'indicador_escala_relevante' => [
                'type' => 'int',
                'label' => 'Indicador de Escala Relevante',
                'input' => 'select',
                'default' => '0',
                'sort_order' => 9,
                'option' => [
                    'values' => [
                        0 => 'Definir via Configurações Gerais',
                        1 => 'S - Produzido em Escala Relevante',
                        2 => 'N - Produzido em Escala NÃO Relevante',
                    ],
                ],
            ],
            'origem_produto' => [
                'type' => 'int',
                'label' => 'Origem do Produto',
                'input' => 'select',
                'default' => '0',
                'sort_order' => 10,
                'option' => [
                    'values' => [
                        0 => 'Definir via Configurações Gerais',
                        1 => '0 - Nacional, exceto as indicadas nos códigos 3, 4, 5, e 8',
                        2 => '1 - Estrangeira - Importação direta, exceto a indicada no código 6',
                        3 => '2 - Estrangeira - Adquirida no mercado interno, exceto a indica no código 7',
                        4 => '3 - Nacional, mercadoria ou bem com Conteúdo de Importação superior a 40% e inferior ou igual a 70%',
                        5 => '4 - Nacional, cuja produção tenha sido feita em conformidade com os processos produtivos básicos',
                        6 => '5 - Nacional, mercadoria ou bem com Conteúdo de Importação inferior ou igual a 40%',
                        7 => '6 - Estrangeira - Importação direta, sem similar nacional, constante em lista da CAMEX',
                        8 => '7 - Estrangeira - Adquirida no mercado interno, sem similar nacional, constante lista CAMEX',
                        9 => '8 - Nacional, mercadoria ou bem com Conteúdo de Importação superior a 70%',
                    ],
                ],
            ]
        ];

        foreach ($attributes as $code => $config) {
            if (!$this->eavAttributeExists($code)) {
                $attributeConfig = array_merge([
                    'backend' => '',
                    'frontend' => '',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                ], $config);

                $eavSetup->addAttribute(Product::ENTITY, $code, $attributeConfig);
            }
        }

        // Create attribute group and assign attributes
        $this->assignAttributesToGroup($eavSetup, $categorySetup, array_keys($attributes));
    }

    /**
     * Create category attributes
     *
     * @param EavSetup $eavSetup
     */
    private function createCategoryAttributes(EavSetup $eavSetup): void
    {
        $attributeGroupName = 'Informações Fiscais da Categoria (NF-e)';
        $entityTypeId = $eavSetup->getEntityTypeId(Category::ENTITY);

        $eavSetup->addAttributeGroup(
            $entityTypeId,
            $eavSetup->getDefaultAttributeSetId(Category::ENTITY),
            $attributeGroupName,
            99
        );

        if (!$this->eavAttributeExists('category_ncm')) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'category_ncm',
                [
                    'type' => 'varchar',
                    'label' => 'NCM',
                    'input' => 'text',
                    'source' => '',
                    'visible' => true,
                    'default' => '',
                    'required' => false,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => $attributeGroupName
                ]
            );
        }
    }

    /**
     * Assign attributes to product attribute group
     *
     * @param EavSetup $eavSetup
     * @param mixed $categorySetup
     * @param array $attributeCodes
     */
    private function assignAttributesToGroup(EavSetup $eavSetup, $categorySetup, array $attributeCodes): void
    {
        $attributeGroupName = 'Informações Fiscais (NF-e)';
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            // Create attribute group
            $eavSetup->addAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                $attributeGroupName,
                200
            );

            // Add attributes to group
            foreach ($attributeCodes as $attributeCode) {
                $categorySetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupName,
                    $attributeCode,
                    null
                );
            }
        }
    }

    /**
     * Check if EAV attribute exists
     *
     * @param string $attributeCode
     * @return bool
     */
    private function eavAttributeExists(string $attributeCode): bool
    {
        $entityType = "catalog_product";
        return (bool)$this->eavAttribute->getIdByCode($entityType, $attributeCode);
    }
}
