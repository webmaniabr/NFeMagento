<?php
namespace Webmaniabr\Nfe\Helper;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class NfeData
{
    protected $scopeConfig;

    protected $productFactory;

    protected $productRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
       WriterInterface $configWriter,
       ProductRepositoryInterface $productRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_productFactory = $productFactory;
        $this->configWriter = $configWriter;
        $this->productRepository = $productRepository;
    }

    /* Return option nfe_duplicada saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return boolean
    */
    public function get_nfe_duplicada() {

         $nfe_duplicada = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/nfe_duplicada', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

         return $nfe_duplicada;

    }

    /* Return access permissions saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return array
    */
    public function get_access_permissions() {

        $consumer_key = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/consumer_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $consumer_secret = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/consumer_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $access_token = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $access_token_secret = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/access_token_secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $access_permissions = array (
            "consumer_key" => $consumer_key,
            "consumer_secret" => $consumer_secret,
            "access_token" => $access_token,
            "access_token_secret" => $access_token_secret
        );

        return $access_permissions;

    }

    /* Return option ambiente_sefaz saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return boolean
    */
    public function get_ambiente_sefaz() {

        $ambiente_sefaz = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/ambiente_sefaz', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $ambiente_sefaz;

    }

    /* Return option emissao_assincrona saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return boolean
    */
    public function get_emissao_assincrona() {

        $emissao_assincrona = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/emissao_assincrona', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $emissao_assincrona;

    }

    /* Return option emissao_automatica saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return boolean
    */
    public function get_emissao_automatica() {

        $emissao_automatica = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/emissao_automatica', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $emissao_automatica;

    }

    /* Return option envio_email saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return boolean
    */
    public function get_envio_email() {

        $envio_email = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/envio_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $envio_email;

    }

    /* Return Dados de Tributo saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return array
    */
    public function get_dados_tributo() {

        $natureza_operacao = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/natureza_operacao', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $modelo = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/modelo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $classe_imposto = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/classe_imposto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gtin = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/gtin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $gtin_tributavel = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/gtin_tributavel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $codigo_ncm = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/ncm', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $codigo_cest = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/cest', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $unidade = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/unidade_nfe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $indicador_escala_relevante = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/indicador_escala_relevante', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $origem_produto = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/origem_produto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $frete_padrao = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracao_padrao/frete_padrao', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $dados_tributo = array (
            "natureza_operacao" => $natureza_operacao,
            "modelo" => $modelo,
            "classe_imposto" => $classe_imposto,
            "gtin" => $gtin,
            "gtin_tributavel" => $gtin_tributavel,
            "codigo_ncm" => $codigo_ncm,
            "codigo_cest" => $codigo_cest,
            "unidade_nfe" => ($unidade == 1 ? 'UN' : 'KG'),
            "indicador_escala_relevante" => $indicador_escala_relevante,
            "origem_produto" => $origem_produto,
            "frete_padrao" => $frete_padrao
        );

        return $dados_tributo;

    }

    /* Return Informações Complementares saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return array
    */
    public function get_informacoes_complementares() {

        $informacoes_fisco = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracoes_complementares/informacoes_fisco', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $informacoes_complementares_consumidor = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_configuracoes_complementares/informacoes_complementares_consumidor', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $informacoes_complementares = array (
            "informacoes_fisco" => $informacoes_fisco,
            "informacoes_complementares_consumidor" => $informacoes_complementares_consumidor,
        );

        return $informacoes_complementares;

    }

    /* Return Transportadoras saved in Stores -> Configuration -> WebmaniaBR NF-e -> Transportadoras
    /*
    /* return array
    */
    public function get_transportadoras($shipping_method) {

        $transportadoras = unserialize($this->scopeConfig->getValue('webmaniabr_nfe_transportadoras/todas_transportadora/transportadoras', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        // If there's no shipping info maped, return null
        if( !$transportadoras ) {
            return null;
        }

        // Identify the data by shipping method
        $transportadoras_info = false;

        foreach ( $transportadoras as $transportadora ) {

            // Get the Title and Name of the method to compare with parameter
            $shipping_name = $this->scopeConfig->getValue('carriers/'.$transportadora["transportadora_metodo_entrega"].'/title') . " - " . $this->scopeConfig->getValue('carriers/'.$transportadora["transportadora_metodo_entrega"].'/name');

            if ( $shipping_name == $shipping_method ) {

                $transportadoras_info = array(
                    "transportadora_razao_social" => $transportadora["transportadora_razao_social"],
                    "transportadora_cnpj" => $transportadora["transportadora_cnpj"],
                    "transportadora_ie" => $transportadora["transportadora_ie"],
                    "transportadora_endereco" => $transportadora["transportadora_endereco"],
                    "transportadora_cep" => $transportadora["transportadora_cep"],
                    "transportadora_uf" => $transportadora["transportadora_uf"],
                    "transportadora_cidade" => $transportadora["transportadora_cidade"]
                );

                break;

            }

        }

        // If the transportadoras_info was not identified return null
        if( !$transportadoras_info ) {
            return null;

        // else, return the data
        } else {
            return $transportadoras_info;
        }

    }

    /* Return Transportadoras Info saved in order review
    /*
    /* return array
    */
    public function get_transportadoras_info ($order_id) {

        // Create an object to SQL use
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('webmaniabrnfe_orders_transportadora');

        $sql = "SELECT * FROM " . $tableName . " WHERE order_id='" . $order_id . "'";

        $connection->query($sql);

        $result = $connection->fetchAll($sql);

        // Check if $result is not empty to avoid crash code
        if( empty($result) ) {

            return null;

        } else {

            $transportadora_info["utilizar_transportadora"] = $result[0]["utilizar_transportadora"];
            $transportadora_info["volume"] = $result[0]["volume"];
            $transportadora_info["especie"] = $result[0]["especie"];
            $transportadora_info["peso_bruto"] = $result[0]["peso_bruto"];
            $transportadora_info["peso_liquido"] = $result[0]["peso_liquido"];

        }

        return $transportadora_info;

    }

    /* Return Payment Methods saved in Stores -> Configuration -> WebmaniaBR NF-e -> Métodos de Pagamento
    /*
    /* return array
    */
    public function get_metodos_pagamento() {

        $metodos_pagamento = $this->scopeConfig->getValue('webmaniabr_nfe_pagamentos/todos_metodos_pagamentos/metodos_pagamento', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return unserialize($metodos_pagamento);

    }

    /* Return if option is enabled in Stores -> Configuration -> WebmaniaBR NF-e -> Linhas de Endereço
    /*
    /* return boolean
    */
    public function get_linhas_endereco_enabled() {

        $linhas_endereco_enabled = $this->scopeConfig->getValue('webmaniabr_nfe_address/webmaniabr_nfe_address_lines/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $linhas_endereco_enabled;

    }

    /* Return the maped address options Stores -> Configuration -> WebmaniaBR NF-e -> Linhas de Endereço
    /*
    /* return array
    */
    public function get_linhas_endereco_maped() {

        $linha_endereco = $this->scopeConfig->getValue('webmaniabr_nfe_address/webmaniabr_nfe_address_lines/webmaniabr_nfe_address_enable_off/line_endereco', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $linha_numero = $this->scopeConfig->getValue('webmaniabr_nfe_address/webmaniabr_nfe_address_lines/webmaniabr_nfe_address_enable_off/line_numero', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $linha_complemento = $this->scopeConfig->getValue('webmaniabr_nfe_address/webmaniabr_nfe_address_lines/webmaniabr_nfe_address_enable_off/line_complemento', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $linha_bairro = $this->scopeConfig->getValue('webmaniabr_nfe_address/webmaniabr_nfe_address_lines/webmaniabr_nfe_address_enable_off/line_bairro', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $linhas_endereco_maped = array (
            "endereco" => $linha_endereco,
            "numero" => $linha_numero,
            "complemento" => $linha_complemento,
            "bairro" => $linha_bairro
        );

        return $linhas_endereco_maped;

    }

    /* Return option debug_enable saved in Stores -> Configuration -> WebmaniaBR NF-e
    /*
    /* return boolean
    */
    public function get_debug_enabled() {

        $debug_enabled = $this->scopeConfig->getValue('webmaniabr_nfe_avancado/webmaniabr_nfe_debug/debug_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $debug_enabled;

    }

    /* Create secret key to validate POST requisitions
    /* Also deletes keys older than specific date
    /*
    /* return (string) $secret_key
    */
    public function manage_secret_key ($order_id, $secret_type) {

        // Create an object to SQL use
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('webmaniabrnfe_secret_keys');

        // Get the actual date
        $date = date('Y-m-d H:i:s');

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_key = '';

        for ($i = 0; $i <= 10; $i++) {
            $random_key .= $characters[rand(0, $characters_length - 1)];
        }

        $secret_key = $order_id . "_" . $random_key;

        // Store the new key for secure request
        $sql = "INSERT INTO " . $tableName . " ( secret_type, secret_key, requested_at )
                VALUES ( '$secret_type', '$secret_key', '$date' )";

        // Delete older keys
        $delete_date = date("Y-m-d H:i:s", strtotime('-48 hours', time()));

        $delete_sql = "DELETE FROM " . $tableName . "
                WHERE secret_type='$secret_type' AND requested_at <= '$delete_date'";

        $connection->query($sql);

        $connection->query($delete_sql);

        return $secret_key;

    }

    public function validate_secret_key ( $secret_type, $secret_key ) {

            // Create an object to SQL use
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('webmaniabrnfe_secret_keys');

            $sql = "SELECT id_webmaniabrnfe_secret_keys FROM $tableName WHERE secret_type='$secret_type' AND secret_key='$secret_key'";

            $connection->query($sql);

            $result = $connection->fetchAll($sql);

            // If is empty, block the operation
            if ( empty($result) ) {
                return array(
                    "type" => "invalid",
                    "message" => "A chave secreta é inválida. Recarregue a página para prosseguir."
                );
            } else {
                return array(
                    "type" => "valid",
                    "message" => ""
                );
            }

    }

    /* Set cache to Certificado A1
    /*
    /* return array
    */
    public function set_cache_certificado_a1 ($expiration) {

        // Get the actual permissions keys
        $access_permissions = $this->get_access_permissions();

        // Set the date based on expiration date
        $expiration_date = date('d/m/Y', strtotime("+".$expiration." days"));

        // Prepare the array to save the cache
        $certificado_a1 = array(
            "consumer_key" => $access_permissions["consumer_key"],
            "consumer_secret" => $access_permissions["consumer_secret"],
            "access_token" => $access_permissions["access_token"],
            "access_token_secret" => $access_permissions["access_token_secret"],
            "expiration" => $expiration,
            "expiration_date" => $expiration_date
        );

        // Save the array
        $this->configWriter->save('webmaniabr_nfe_configs/group_access_token_configs/certificadoa1', serialize($certificado_a1), $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);

    }

    /* Get cache to Certificado A1
    /*
    /* return array
    */
    public function get_cache_certificado_a1 () {

        // Get the cache information
        $cache_certificado = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/certificadoa1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        // If there's no cache, return false
        if ( empty($cache_certificado) ) {

            return false;

        } else {

            // Get the actual permissions keys to compare with actual cache
            $access_permissions = $this->get_access_permissions();

            // Unserialize the cache
            $cache_details = unserialize($cache_certificado);

            // Check if the cache is registered for the same permisions keys
            if (
                   $cache_details["consumer_key"] != $access_permissions["consumer_key"]
                || $cache_details["consumer_secret"] != $access_permissions["consumer_secret"]
                || $cache_details["access_token"] != $access_permissions["access_token"]
                || $cache_details["access_token_secret"] != $access_permissions["access_token_secret"]
                || $cache_details["expiration"] == ""
                || $cache_details["expiration_date"] == ""
            ) {

                // If the cache is invalid, register a new one
                $this->certificado = $this->connect_webmaniabr( 'GET', 'https://webmaniabr.com/api/1/nfe/certificado/', "" );

                // Check if the tokens are valid
                if ( isset($this->certificado->error) ) {
                    return false;
                }

                // Set the new cache
                $this->set_cache_certificado_a1($this->certificado->expiration);

                // Return the expiration
                $cache_certificado = $this->scopeConfig->getValue('webmaniabr_nfe_configs/group_access_token_configs/certificadoa1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            }

            // Return the expiration in cache
            return $cache_details["expiration_date"];

        }

    }

    /* Get status from order by id
    /* @param (int) $order_id
    /*
    /* return (string) $status
    */
    public function get_status_order_by_id ( $order_id ) {

        // Create an object to SQL use
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('webmaniabrnfe_orders_nfe');

        // Consult if the actual order already has a NF-e with Status "Emitida"
        $sql = "SELECT status FROM " . $tableName . " WHERE order_id='" . $order_id . "' ORDER BY requested_at DESC LIMIT 1";
        $result = $connection->fetchAll($sql);

        // Check if $result is not empty to avoid crash code
        if( empty($result) ) { return ""; } else { return $result[0]['status']; }
    }

    /* Add NF-e informations on database
    /* @param (int) $order_id
    /* @param (string) $uuid
    /* @param (string) $chave_acesso
    /* @param (string|int) $n_recibo
    /* @param (string|int) $n_nfe
    /* @param (string|int) $n_serie
    /* @param (string) $url_xml
    /* @param (string) $url_danfe
    /* @param (string) $status
    /* @param (datetime) $date
    /*
    */
    public function add_status_nfe ($order_id, $uuid, $chave_acesso, $n_recibo, $n_nfe, $n_serie, $url_xml, $url_danfe, $status, $date) {

        // Create an object to SQL use
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('webmaniabrnfe_orders_nfe');

        $sql = "INSERT INTO " . $tableName . "
                (order_id, uuid, chave_acesso, n_recibo, n_nfe, n_serie, url_xml, url_danfe, status, requested_at)
                VALUES (" . $order_id . ",'" . $uuid . "','" . $chave_acesso . "', '" . $n_recibo . "', '" . $n_nfe . "', '" . $n_serie . "', '" . $url_xml . "', '" . $url_danfe . "', '" . $status . "', '" . $date . "')";

        $connection->query($sql);

    }
    
    public function get_product_info($order_id, $product_id, $qnty, $price, $total_price, $dados_tributo) {
        
        $orderItem = $this->productRepository->getById($product_id);
        
        // Calculate the weight
        $unit_weight = number_format($orderItem->getWeight(), 4, '.', '');

        if ( !$unit_weight || floatval($unit_weight) == "0" ) {
            // Return Error: PRODUCT HAS NO WEIGTH
            $message = "O produto <b>" . $orderItem->getName() . "</b> do pedido #" . $order_id . " não possui um peso válido, por favor, cadastre antes de continuar";
            
            return array(
                'error' => true,
                'message' => $message
            );
        }
        
        // Store the codigo_ncm value of the product
        $product_ncm = $orderItem->getData('codigo_ncm');

        // If is not set codigo_ncm in product, search in categories
        if( empty($product_ncm) ) {

            $product = $categories = null;

            $product = $this->_productFactory->create()->load($orderItem->getProductId());

            $categories = $product->getCategoryIds();

            if( isset($categories) ) {

                foreach($categories as $cat_id) {

                    $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($cat_id);

                    $product_ncm = $category->getData('category_ncm');
                    if($product_ncm) break;

                }

            }

            // If the codigo_ncm is still missing, add the global value in Stores -> Configuration -> WebmaniaBR NF-e
            if(empty($product_ncm)) $product_ncm = $dados_tributo["codigo_ncm"];

        }

        // Set the codigo_ean value
        $product_ean = $orderItem->getData('codigo_ean');
        if(empty($product_ean)) $product_ean = $dados_tributo["gtin"];

        // Set the codigo_cest value
        $product_cest = $orderItem->getData('codigo_cest');
        if(empty($product_cest)) $product_cest = $dados_tributo["codigo_cest"];

        // Set the unidade_nfe value
        $product_unidade = $orderItem->getAttributeText('unidade_nfe');
        if( !$product_unidade || $product_unidade == "Definir via Configurações Gerais") {
            $product_unidade = $dados_tributo["unidade_nfe"];
        }else{
            $product_unidade = ($product_unidade == "Kilograma" ? "KG" : "UN");
        }

        // Set the classe_imposto value
        $product_classe_imposto = $orderItem->getData('classe_imposto');
        if ( is_null($product_classe_imposto)) $product_classe_imposto = $dados_tributo["classe_imposto"];

        // Set the origem_produto value
        $product_origem = substr($orderItem->getAttributeText('origem_produto'), 0, 1);

        if ( (empty($product_origem) || $product_origem == "D") &&  $product_origem != "0" ) {
            $product_origem = substr($dados_tributo["origem_produto"], 0, 1);
        }

        if ( (empty($product_origem) || $product_origem == "D") &&  $product_origem != "0" ) {
            // Return Error: PRODUCT HAS NO PRODUCT_ORIGEM
            $message = "O produto <b>" . $orderItem->getName() . "</b> do pedido #" . $order_id . " não possui uma origem de produto válido, por favor, cadastre antes de continuar";
            
            return array(
                'error' => true,
                'message' => $message
            );
        }

        // Check if product is configurable
        $orderItemOptions = $orderItem->getProductOptions();

        if  ( isset($orderItemOptions['simple_name']) ) {
            $product_name = $orderItemOptions['simple_name'];
        } else {
            $product_name = $orderItem->getName();
        }

        if  ( isset($orderItemOptions['simple_sku']) ) {
            $product_sku = $orderItemOptions['simple_sku'];
        } else {
            $product_sku = $orderItem->getSku();
        }

        $order_details = array(
            'nome' => $product_name,
            'sku' => $product_sku,
            'ean' => $product_ean,
            'ncm' => $product_ncm,
            'cest' => $product_cest,
            'quantidade' => $qnty,
            'unidade' => $product_unidade,
            'peso' => $unit_weight,
            'origem' => $product_origem,
            'subtotal' => number_format($price, 2, '.', ''),
            'total' => number_format($total_price, 2, '.', ''),
            'classe_imposto' => $product_classe_imposto
        );
        
        return $order_details;
    }

    /* Return the product informations
    /*
    /* return array
    */
    public function get_the_order_data_by_id ( $order_id, $bath_process = false ) {

        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $total_weight = 0;
        $total_items_qnty = 0;

        // ---- Order Details ---- //

            $dados_tributo = $this->get_dados_tributo();

            $secret_key = $this->manage_secret_key($order_id, "nfe_callback");

            $order_details["ID"] = $order_id;
            $order_details["origem"] = "magento_2.3";
            $order_details['url_notificacao'] = $storeManager->getStore()->getBaseUrl() . "webmaniabrnfe/index/nfeactions/?nfe_callback=" . $secret_key . "&order_id=" . $order_id;
            $order_details["operacao"] = 1;
            $order_details["natureza_operacao"] = $dados_tributo["natureza_operacao"];
            $order_details["modelo"] = $dados_tributo["modelo"];
            $order_details["emissao"] = 1;
            $order_details["finalidade"] = 1;
            $order_details["ambiente"] = $this->get_ambiente_sefaz();

            if ($bath_process){
                $order_details["assincrono"] = 1;
            }

        // **** Order Details **** //

        // ---- Client Details ---- //

            $cliente_details = $order->getShippingAddress();

            // Get VAT from Shipping Address
            $cpf_cnpj = $cliente_details->getData('vat_id');

            // If Shipping address' VAT doesn't exist, get it from Customer
            if (is_null($cpf_cnpj) || empty($cpf_cnpj)) {

                $cpf_cnpj = $order->getData('customer_taxvat');

            }

            // If the field used is Tax VAT
            $cpf_cnpj = str_replace( array('/', '.', '-'), '', $cpf_cnpj);

            if (strlen($cpf_cnpj) == 14) {

                $order_details['cliente']['cnpj'] = $cpf_cnpj;
                $order_details['cliente']['razao_social'] = $cliente_details->getFirstname().' '.$cliente_details->getLastname();
                // if ($customerData->getData('inscest')) $data['cliente']['ie'] = $customerData->getData('inscest');

            } else {

                if( is_null($cpf_cnpj) || empty($cpf_cnpj) ) {
                    // Return Error: IF THE FIELD CPF (VAT_ID) IS NULL
                    return "O campo de CPF do pedido #". $order_id ." não está configurado corretamente, por favor, configure-o antes de prosseguir.";
                }

                $order_details['cliente']['cpf'] = $cpf_cnpj;
                $order_details['cliente']['nome_completo'] = $cliente_details->getFirstname().' '.$cliente_details->getLastname();

            }

            $cliente_details_address = $cliente_details->getStreet();

            // ---- Address Exceptions ---- //

                // If is not set 3 fields for address, return false and alert the user
                if( count($cliente_details_address) < 3 ) {
                    // Return Error: IF THE ADDRESS FIELDS SIZE IS LOWER THAN 3 ('Complemento' is opcional)
                    return "Os campos de endereço do pedido #". $order_id ." não estão configurados corretamente, por favor, configure-os antes de prosseguir.";
                }

                // If the state is not definied
                if( is_null($cliente_details->getRegion()) ) {
                    // Return Error: IF THE FIELD STATE ISN'T VALID
                    return "O campo de estado do pedido #". $order_id ." não está configurado corretamente, por favor, configure-os antes de prosseguir";
                }

            // **** Address Exceptions **** //

            // If the module has the normal behavior for address line
            if ( $this->get_linhas_endereco_enabled() ) {

                $endereco = $cliente_details_address[0];
                $numero = $cliente_details_address[1];

                if ( isset($cliente_details_address[2]) ) {
                    $complemento = $cliente_details_address[2];
                } else {
                    $complemento = "";
                }
                
                if ( !isset($cliente_details_address[3]) ) {
                    return "O campo de bairro do pedido #". $order_id ." não está configurado corretamente, por favor, configure-os antes de prosseguir";
                } else {
                    $bairro = $cliente_details_address[3];
                }
                

            // Else let the user define the order
            } else {

                $address_maped = $this->get_linhas_endereco_maped();

                $endereco = $cliente_details_address[$address_maped["endereco"]];
                $numero = $cliente_details_address[$address_maped["numero"]];

                if ( isset($cliente_details_address[$address_maped["complemento"]]) ) {
                    $complemento = $cliente_details_address[$address_maped["complemento"]];
                } else {
                    $complemento = "";
                }
                
                if ( !isset($cliente_details_address[$address_maped["bairro"]]) ) {
                    return "O campo de bairro do pedido #". $order_id ." não está configurado corretamente, por favor, configure-os antes de prosseguir";
                } else {
                    $bairro = $cliente_details_address[$address_maped["bairro"]];
                }

            }

            $envio_email = $this->get_envio_email();

            $order_details["cliente"]["endereco"] = $endereco;
            $order_details["cliente"]["numero"] = $numero;
            $order_details["cliente"]["complemento"] = $complemento;
            $order_details["cliente"]["bairro"] = $bairro;
            $order_details["cliente"]["cidade"] = $cliente_details->getCity();
            $order_details["cliente"]["uf"] = $this->getTheUf($cliente_details->getRegion());
            $order_details["cliente"]["uf"] = $cliente_details->getRegion();
            $order_details["cliente"]["cep"] = $cliente_details->getPostcode();
            $order_details["cliente"]["telefone"] = $cliente_details->getTelephone();
            $order_details["cliente"]["email"] = ($envio_email ? $order->getCustomerEmail() : '');

        // **** Client Details **** //

        // ---- Products Details ---- //

            $orderItems = $order->getAllVisibleItems();

            // Set the cost informations to recalculate if there is a product
            // that will ignore the loop
            $shipping_price = number_format($order->getShippingAmount(), 2, '.', '');
            $discount = number_format(abs($order->getDiscountAmount()), 2, '.', '');
            $total_order_price = number_format($order->getGrandTotal(), 2, '.', '');
            $order_details["produtos"] = [];

            foreach ($orderItems as $orderItem) {

                // Pre-build the data
                $product_id = $orderItem->getProductId();
                $product_type = $orderItem->getProductType(); // bundle
                $qnty = $orderItem->getQtyordered();
                $price = number_format($orderItem->getPrice(), 2, '.', '');
                $total_price = $price * $qnty;

                // Ignore the product
                if ( $orderItem->getProduct()->getData('ignorar_nfe') ) {
                    $total_order_price -= $total_price;
                    continue;
                }
                
                // If product is bundle
                if ( $product_type == 'bundle' ) {

                    $product_options = $orderItem->getProductOptions();
                    $products = [];

                    if ( isset($product_options['bundle_options']) ) {
    
                        // Get childrens ids from bundle     
                        $bundle_product = $this->productRepository->getById($product_id);
                        $typeInstance = $bundle_product->getTypeInstance();
                        $bundle_ids = $typeInstance->getChildrenIds($product_id, true);

                        // Build an array with childrens ids
                        if ($bundle_ids) {
                            $children_ids = [];
                            foreach ($bundle_ids as $key => $arr_id) {
                                foreach ($arr_id as $id) {
                                    $children_ids[$key] = $id;
                                }
                            }
                        }
                        
                        foreach ($product_options['bundle_options'] as $product) {
                            // Product Bundle information
                            $bundle_id = $product['option_id'];
                            $bundle_product_id = $children_ids[$bundle_id];

                            // Bundle options
                            $price = number_format($product['value'][0]['price'], 2, '.', '')/$product['value'][0]['qty'];
                            $bundle_product_qnty = (int) $product['value'][0]['qty'] * $qnty;
                            $total_price = number_format($bundle_product_qnty*$price, 2, '.', '');
                            
                            // Create the product array
                            $product_info = $this->get_product_info($order_id, $bundle_product_id, $bundle_product_qnty, $price, $total_price, $dados_tributo);

                            if (isset($product_info['error'])) {
                                return $product_info['message'];
                            }
                            
                            $unit_weight = $product_info['peso'];
                            $product_qnty = $product_info['quantidade'];
                            
                            $total_weight += ($unit_weight * $product_qnty);
                            $total_items_qnty += $product_qnty;
                            
                            $products[] = $product_info;
                        }

                    }

                    $order_details["produtos"] = array_merge($order_details["produtos"], $products);

                } else {
                    // Create the product array
                    $product_info = $this->get_product_info($order_id, $product_id, $qnty, $price, $total_price, $dados_tributo);
                    
                    if (isset($product_info['error'])) {
                        return $product_info['message'];
                    }
                    
                    $unit_weight = $product_info['peso'];
                    $product_qnty = $product_info['quantidade'];
                    
                    $total_weight += ($unit_weight * $product_qnty);
                    $total_items_qnty += $product_qnty;
                    
                    $order_details["produtos"][] = $product_info;
                }

            }

        // **** Products Details **** //

        // ---- Order Details ---- //

            // Get the payment method from order
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $methodCode = $method->getCode();
            $methodTitle = $method->getTitle();

            // Get the maped fields from configs
            $metodos_pagamento = $this->get_metodos_pagamento();

            // If there's no methods, return error
            if( !$metodos_pagamento ) {
                // Return Error: THERE'S NOT A PAYMENT METHOD MAPED
                return "Não existem métodos de pagamento mapeados, por favor, configure-o antes de prosseguir.";
            }

            // Identify the code by method code
            $metodo_codigo = false;

            foreach ( $metodos_pagamento as $metodo ) {
                if ( $metodo["metodo_pagamento"] == $methodCode ) {
                    $metodo_codigo = $metodo["forma_pagamento"];
                    break;
                }
            }

            // If the metodo_codigo was not identified
            if( !$metodo_codigo ) {
                // Return Error: THERE'S NOT A PAYMENT METHOD MAPED FOR THE ITEM
                return "Não existe método de pagamento mapeado para o método <b>". $methodTitle ."</b> no pedido #". $order_id .", por favor, configure-o antes de prosseguir.";
            }
            
            $order_details["pedido"]["pagamento"] = 0;
            $order_details["pedido"]["forma_pagamento"] = $metodo_codigo;
            $order_details["pedido"]["presenca"] = 2;
            $order_details["pedido"]["modalidade_frete"] = isset($dados_tributo['frete_padrao']) ? $dados_tributo['frete_padrao'] : 0;
            $order_details["pedido"]["frete"] = $shipping_price;
            $order_details["pedido"]["desconto"] = $discount;
            $order_details["pedido"]["total"] = $total_order_price;

            // Get the optionals informations
            $informacoes_complementares = $this->get_informacoes_complementares();

            if ( isset($informacoes_complementares["informacoes_fisco"]) ) {
                $order_details["pedido"]["informacoes_fisco"] = $informacoes_complementares["informacoes_fisco"];
            }

            if ( isset($informacoes_complementares["informacoes_complementares_consumidor"]) ) {
                $order_details["pedido"]["informacoes_complementares"] = $informacoes_complementares["informacoes_complementares_consumidor"];
            }

        // **** Order Details **** //

        // ---- Shipping Company Details ---- //

            $shipping_company = $this->get_transportadoras($order->getShippingDescription());
            $shipping_company_info = $this->get_transportadoras_info($order_id);

            if ( is_array($shipping_company) && $shipping_company_info["utilizar_transportadora"] ) {

                // Validation for shipping information
                if (
                    $shipping_company_info["volume"] == NULL ||
                    $shipping_company_info["especie"] == NULL ||
                    $shipping_company_info["peso_bruto"] == NULL ||
                    $shipping_company_info["peso_liquido"] == NULL
                ) {
                    return "As informações da transportadora não estão cadastradas no pedido em Página do Pedido -> [Aba] Transportadoras NF-e #". $order_id .", por favor, configure-o antes de prosseguir.";
                }

                // Validation for shipping company
                if (
                    $shipping_company["transportadora_cnpj"] == NULL ||
                    $shipping_company["transportadora_razao_social"] == NULL ||
                    $shipping_company["transportadora_ie"] == NULL ||
                    $shipping_company["transportadora_endereco"] == NULL ||
                    $shipping_company["transportadora_uf"] == NULL ||
                    $shipping_company["transportadora_cidade"] == NULL ||
                    $shipping_company["transportadora_cep"] == NULL
                ) {
                    return "As informações da transportadora não estão cadastradas em Lojas -> Configuração -> WebmaniaBR NF-e -> Transportadoras no pedido #". $order_id .", por favor, configure-o antes de prosseguir.";
                }

                // Store shipping information for 'transporte' array
                $order_details["transporte"]["volume"] = $shipping_company_info["volume"];
                $order_details["transporte"]["especie"] = $shipping_company_info["especie"];
                $order_details["transporte"]["peso_bruto"] = $shipping_company_info["peso_bruto"];
                $order_details["transporte"]["peso_liquido"] = $shipping_company_info["peso_liquido"];

                // Check if the field is registered as CPF or CNPJ
                $cpf_cnpj = str_replace( array('/', '.', '-'), '', $shipping_company["transportadora_cnpj"] );

                // Check if variable is CPF or CNPJ
                if ( strlen($cpf_cnpj) == 14 && is_numeric($cpf_cnpj) ) {

                    $order_details["transporte"]["cnpj"] = $shipping_company["transportadora_cnpj"];
                    $order_details["transporte"]["razao_social"] = $shipping_company["transportadora_razao_social"];

                } elseif ( strlen($cpf_cnpj) == 11 && is_numeric($cpf_cnpj) ) {

                    $order_details["transporte"]["cpf"] = $shipping_company["transportadora_cnpj"];
                    $order_details["transporte"]["nome_completo"] = $shipping_company["transportadora_razao_social"];

                } else {

                    // Return Error: IF THE INFORMATION IS NOT A NUMERIC VALUE OR THE SIZE DON'T MATCH
                    return "O CPF/CNPJ da transportadora " . $shipping_company["transportadora_razao_social"] . " não está cadastrado corretamente";

                }

                $order_details["transporte"]["ie"] = $shipping_company["transportadora_ie"];
                $order_details["transporte"]["endereco"] = $shipping_company["transportadora_endereco"];
                $order_details["transporte"]["uf"] = $shipping_company["transportadora_uf"];
                $order_details["transporte"]["cidade"] = $shipping_company["transportadora_cidade"];
                $order_details["transporte"]["cep"] = $shipping_company["transportadora_cep"];

            }

        // **** Shipping Company Details **** //

        // **** DEBUG SECTION **** //
        if ( $this->get_debug_enabled() ) {

            echo "<pre>";
                var_dump($order_details);
            echo "</pre>";
            die();

        }
        // **** DEBUG SECTION **** //

        return $order_details;

    }

    /* Return the UF based on the Region Name
    /*
    /* return string
    */
    public function getTheUf ( $region ) {

        $estados = array(
            "Acre" => "AC",
            "Alagoas" => "AL",
            "Amazonas" => "AM",
            "Amapá" => "AP",
            "Bahia" => "BA",
            "Ceará" => "CE",
            "Distrito Federal" => "DF",
            "Espírito Santo" => "ES",
            "Goiás" => "GO",
            "Maranhão" => "MA",
            "Mato Grosso" => "MT",
            "Mato Grosso do Sul" => "MS",
            "Minas Gerais" => "MG",
            "Pará" => "PA",
            "Paraíba" => "PB",
            "Paraná" => "PR",
            "Pernambuco" => "PE",
            "Piauí" => "PI",
            "Rio de Janeiro" => "RJ",
            "Rio Grande do Norte" => "RN",
            "Rondônia" => "RO",
            "Rio Grande do Sul" => "RS",
            "Roraima" => "RR",
            "Santa Catarina" => "SC",
            "Sergipe" => "SE",
            "São Paulo" => "SP",
            "Tocantins" => "TO"
        );

        return $estados[$region];

    }

    /* Validate the API response
    /* @param (array) $data
    /*
    /* return string
    */
    public function emitir_nfe ( $order_id, $data ) {

        if( is_array($data) ) {

            $response = $this->connect_webmaniabr( 'POST', 'https://webmaniabr.com/api/1/nfe/emissao/', $data );

            if ( isset($response->error) ) {

                    return array (
                            'return' => 'error',
                            'message' => "Pedido #". $order_id .": ". $response->error
                        );

                } elseif ( $response->status == 'reprovado' ) {

                    if (isset($response->log)){

                        if ($response->log->xMotivo){

                            if( isset($response->log->aProt[0]->xMotivo) ) {

                                return array (
                                        'return' => 'error',
                                        'message' => "Pedido #". $order_id .": ". $response->log->aProt[0]->xMotivo
                                    );

                            } else {

                                return array (
                                        'return' => 'error',
                                        'message' => "Pedido #". $order_id .": ". $response->log->xMotivo
                                    );

                            }

                        }

                    }

                }

                return $response;

            } else {

                return array (
                        'return' => 'error',
                        'message' => "Pedido #". $order_id .": " . $data
                    );

            }

    }

    /* Register comments on order
    /* @param (int) $order_id
    /* @param (string) $username
    /* @param (string) $url_danfe
    /*
    /* return string
    */
    public function register_comment ( $order_id, $username, $url_danfe ) {

        $model = $this->_objectManager->create('Magento\Sales\Model\Order');

        // Load the data from $order
        $loadedOrder = $model->load($order_id);

        // Save the recevied status as comment
        $loadedOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
        $order_status = $loadedOrder->getState();
        $loadedOrder->setStatus($order_status);
        date_default_timezone_set('America/Sao_Paulo');

        $comment = "NF-e gerada às " . date('d-m-Y H:i:s') . ". <a target='_blank' href='" . $url_danfe . "'>Clique aqui</a> para visualizar.";

        $loadedOrder->addStatusToHistory($order_status, $comment . " Criado por: " . $username);
        $loadedOrder->save();

    }

    /* Create the connection with API
    /*
    /* return array
    */
    public function connect_webmaniabr( $request, $endpoint, $data ) {

        // Verify cURL
        if (!function_exists('curl_version')){
          $curl_error = new \StdClass();
          $curl_error->error = 'cURL não localizado! Não é possível obter conexão na API da WebmaniaBR®. Verifique junto ao programador e a sua hospedagem. (PHP: '.phpversion().')';
          return $curl_error;
        }

        // Set limits
        @set_time_limit( 300 );
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);
        ini_set('memory_limit', '256M');
        if (
            strpos($endpoint, '/sefaz/') !== false ||
            strpos($endpoint, '/certificado/') !== false
        ){
            $timeout = 15;
        } else {
            $timeout = 300;
        }

        // Header
        $access_permissions = $this->get_access_permissions();

        $headers = array(
            'Cache-Control: no-cache',
            'Content-Type:application/json',
            'X-Consumer-Key: '.$access_permissions["consumer_key"],
            'X-Consumer-Secret: '.$access_permissions["consumer_secret"],
            'X-Access-Token: '.$access_permissions["access_token"],
            'X-Access-Token-Secret: '.$access_permissions["access_token_secret"],
        );

        // Init connection
        $rest = curl_init();
        curl_setopt($rest, CURLOPT_CONNECTTIMEOUT , 10);
        curl_setopt($rest, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($rest, CURLOPT_URL, $endpoint.'?time='.time());
        curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rest, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($rest, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($rest, CURLOPT_CUSTOMREQUEST, $request);
        curl_setopt($rest, CURLOPT_POSTFIELDS, json_encode( $data ));
        curl_setopt($rest, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($rest, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($rest, CURLOPT_FOLLOWLOCATION, 1);

        // Connect to API
        $response = curl_exec($rest);
        $http_status = curl_getinfo($rest, CURLINFO_HTTP_CODE);
        $curl_errno = (int) curl_errno($rest);
        if ($curl_errno){
            $curl_strerror = curl_strerror($curl_errno);
        }

        curl_close($rest);

        // Get cURL errors
        $curl_error = new \StdClass();
        if ($curl_errno){
          // Get User IP
          $ip = $_SERVER['CF-Connecting-IP']; // CloudFlare
          if (!$ip){
            $ip = $_SERVER['REMOTE_ADDR']; // Standard
          }
          if (is_array($ip)){
            $ip = $ip[0];
          }
          // cURL errors
          if (!$http_status){
            $curl_error->error = 'Não foi possível obter conexão na API da WebmaniaBR®, possível relação com bloqueio no Firewall ou versão antiga do PHP. Verifique junto ao programador e a sua hospedagem a comunicação na URL: https://webmaniabr.com/api/. (cURL: '.$curl_strerror.' | PHP: '.phpversion().' | cURL: '.curl_version().')';
          } elseif ($http_status == 500) {
            $curl_error->error = 'Ocorreu um erro ao processar a sua requisição. A nossa equipe já foi notificada, em caso de dúvidas entre em contato com o suporte da WebmaniaBR®. (cURL: '.$curl_strerror.' | HTTP Code: '.$http_status.' | IP: '.$ip.')';
          } elseif (!in_array($http_status, array(401, 403))) {
            $curl_error->error = 'Não foi possível se conectar na API da WebmaniaBR®. Em caso de dúvidas entre em contato com o suporte da WebmaniaBR®. (cURL: '.$curl_strerror.' | HTTP Code: '.$http_status.' | IP: '.$ip.')';
          }
        }

        // Return
        if ( isset($curl_error->error) ) {
            return $curl_error;
        } else {
            return json_decode($response);
        }

    }

}
