<?php 
namespace Webmaniabr\Nfe\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface{

    public function install(SchemaSetupInterface $setup,ModuleContextInterface $context) {
      
        $setup->startSetup();
        $conn = $setup->getConnection();
        $tableName = $setup->getTable('webmaniabrnfe_orders_nfe');
        
        // Table Nfe
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                  'id_webmaniabrnfe_orders_nfe',
                                  Table::TYPE_INTEGER,
                                  null,
                                  ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                  'order_id',
                                  Table::TYPE_INTEGER,
                                  null,
                                  ['nullable'=>false],
                                  'ID do pedido'
                                )
                            ->addColumn(
                                  'uuid',
                                  Table::TYPE_TEXT,
                                  100,
                                  ['nullbale'=>false],
                                  'UUID'
                                )
                            ->addColumn(
                                  'chave_acesso',
                                  Table::TYPE_TEXT,
                                  50,
                                  ['nullbale'=>false],
                                  'Chave de Acesso'
                                )
                            ->addColumn(
                                  'n_recibo',
                                  Table::TYPE_TEXT,
                                  50,
                                  ['nullbale'=>false],
                                  'UUID'
                                )
                            ->addColumn(
                                  'n_nfe',
                                  Table::TYPE_TEXT,
                                  20,
                                  ['nullbale'=>false],
                                  'Número da Emissão'
                                )
                            ->addColumn(
                                  'n_serie',
                                  Table::TYPE_TEXT,
                                  10,
                                  ['nullbale'=>false],
                                  'Série de Emissão'
                                )
                            ->addColumn(
                                  'url_xml',
                                  Table::TYPE_TEXT,
                                  250,
                                  ['nullbale'=>false],
                                  'URL do XML'
                                )
                            ->addColumn(
                                  'url_danfe',
                                  Table::TYPE_TEXT,
                                  250,
                                  ['nullbale'=>false],
                                  'URL do DANFE'
                                )
                            ->addColumn(
                                  'status',
                                  Table::TYPE_TEXT,
                                  15,
                                  ['nullbale'=>false],
                                  'Status'
                                )
                              ->addColumn(
                                'requested_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                                'Requested At'
                              )
                            ->setOption('charset','utf8');
            $conn->createTable($table);
        }
        
        // Table Transportadora
        $tableName = $setup->getTable('webmaniabrnfe_orders_transportadora');
                
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                  'id_webmaniabrnfe_orders_transportadora',
                                  Table::TYPE_INTEGER,
                                  null,
                                  ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                  'order_id',
                                  Table::TYPE_INTEGER,
                                  null,
                                  ['nullable'=>false],
                                  'ID do pedido'
                                )
                            ->addColumn(
                                  'utilizar_transportadora',
                                  Table::TYPE_INTEGER,
                                  1,
                                  ['nullable'=>false, 'default'=>'0'],
                                  'Utilizar Transportadora'
                                )
                            ->addColumn(
                                  'volume',
                                  Table::TYPE_TEXT,
                                  10,
                                  ['nullbale'=>false],
                                  'Volume'
                                )
                            ->addColumn(
                                  'especie',
                                  Table::TYPE_TEXT,
                                  15,
                                  ['nullbale'=>false],
                                  'Espécie'
                                )
                            ->addColumn(
                                  'peso_bruto',
                                  Table::TYPE_TEXT,
                                  15,
                                  ['nullbale'=>false],
                                  'Peso Bruto'
                                )
                            ->addColumn(
                                  'peso_liquido',
                                  Table::TYPE_TEXT,
                                  15,
                                  ['nullbale'=>false],
                                  'Peso Líquido'
                                )
                            ->setOption('charset','utf8');
            $conn->createTable($table);
        }
        
        // Table Transportadora
        $tableName = $setup->getTable('webmaniabrnfe_secret_keys');
        
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                  'id_webmaniabrnfe_secret_keys',
                                  Table::TYPE_INTEGER,
                                  null,
                                  ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                  'secret_type',
                                  Table::TYPE_TEXT,
                                  15,
                                  ['nullable'=>false],
                                  'Tipo da Chave'
                                )
                            ->addColumn(
                                  'secret_key',
                                  Table::TYPE_TEXT,
                                  50,
                                  ['nullable'=>false],
                                  'Chave de Segurança'
                                )
                              ->addColumn(
                                'requested_at',
                                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                                null,
                                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                                'Requested At'
                              )
                            ->setOption('charset','utf8');
            $conn->createTable($table);
        }
        
        $setup->endSetup();
    }
}
 ?>