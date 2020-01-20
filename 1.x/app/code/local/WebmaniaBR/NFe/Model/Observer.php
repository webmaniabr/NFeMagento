<?php
include_once 'Mage/Sales/Model/Observer.php';
class WebmaniaBR_NFe_Model_Observer extends Mage_Sales_Model_Observer {

  static protected $_singletonFlag = false;

  public function emitirNfe($order, $state = false, $status = false, $force = false)
  {

    include 'config.php';
    $data = array();

    /* -----------------------------------------------------------
    ATENÇÃO: SOMENTE EDITE O CÓDIGO ABAIXO SE TIVER CONHECIMENTO
    Documentação: https://webmaniabr.com/docs/rest-api-nfe/
    ------------------------------------------------------------*/

    $this->consumerKey = Mage::getStoreConfig('nfe/section_one/consumer_key', Mage::app()->getStore());
    $this->consumerSecret = Mage::getStoreConfig('nfe/section_one/consumer_secret', Mage::app()->getStore());
    $this->accessToken = Mage::getStoreConfig('nfe/section_one/access_token', Mage::app()->getStore());
    $this->accessTokenSecret = Mage::getStoreConfig('nfe/section_one/access_token_secret', Mage::app()->getStore());
    $this->ambiente_sefaz = Mage::getStoreConfig('nfe/section_one/ambiente_sefaz', Mage::app()->getStore());
    $this->natureza_operacao = Mage::getStoreConfig('nfe/section_two/natureza_operacao', Mage::app()->getStore());
    $this->ncm = Mage::getStoreConfig('nfe/section_two/codigo_ncm', Mage::app()->getStore());
    $this->classe_imposto = Mage::getStoreConfig('nfe/section_two/classe_imposto', Mage::app()->getStore());
    $this->cest = Mage::getStoreConfig('nfe/section_two/codigo_cest', Mage::app()->getStore());
    $this->ean = Mage::getStoreConfig('nfe/section_two/codigo_barras_ean', Mage::app()->getStore());
    $this->origem = Mage::getStoreConfig('nfe/section_two/origem_produto', Mage::app()->getStore());
    $this->uniq_id = Mage::getStoreConfig('nfe/section_one/uniq_get_key', Mage::app()->getStore());
    $envio_email = Mage::getStoreConfig('nfe/section_two/envio_email', Mage::app()->getStore());
    $customer_id = $order->getCustomerId();
    $customerData = Mage::getModel('customer/customer')->load($customer_id);

    if(!$envio_email || $envio_email == '1') $envio_email = 'on';

    if(!$this->uniq_id){
      $this->uniq_id = md5(uniqid(rand(), true));
      Mage::getModel('core/config')->saveConfig('nfe/section_one/uniq_get_key', $this->uniq_id);
    }

    $notification_url = Mage::getBaseUrl().'?retorno_nfe='.$this->uniq_id.'&order_id='.$order->getIncrementId();

    if ($state == $executar_quando || $force) {

      $applied_rule_id = $order->applied_rule_ids; //Assign due to protected property (to check for empty)
      $percentages_applied = array();

      if(!empty($applied_rule_id)){
        $rule = Mage::getModel('salesrule/rule')->load($applied_rule_id);
        $rule_data = $rule->getData();
        if($rule_data['simple_action'] == 'by_percent'){
          $percentages_applied[] = $rule_data['discount_amount'];
        }
      }

      $orderno = $order->getIncrementId();
      $shipping_address = $order->getShippingAddress();

      $data = array(
        'ID' => (int) $orderno,
        'origem' => 'magento_1.9',
        'url_notificacao' => $notification_url,
        'operacao' => 1,
        'natureza_operacao' => $this->natureza_operacao,
        'emissao' => 1,
        'finalidade' => 1,
        'ambiente' => (int)$this->ambiente_sefaz,
        'cliente' => array(
          'endereco' => $shipping_address->getStreet(1),
          'complemento' => $shipping_address->getStreet(3),
          'numero' => (int) $shipping_address->getStreet(2),
          'bairro' => $shipping_address->getStreet(4),
          'cidade' => $shipping_address->getCity(),
          'uf' => $shipping_address->getRegion(),
          'cep' => $shipping_address->getPostcode(),
          'telefone' => $shipping_address->getTelephone(),
          'email' => ($envio_email == 'on' ? $order->getCustomerEmail() : '')
        ),
        'pedido' => array(
          'pagamento' => 0,
          'presenca' => 2,
          'modalidade_frete' => 0,
          'frete' => number_format($order->getShippingAmount(), 2, '.', ''),
          'desconto' => number_format(abs($order->getDiscountAmount()), 2, '.', ''),
          'total' => number_format($order->getGrandTotal(), 2, '.', '')
        ),
      );

      // Set user
      $cpf_cnpj = str_replace( array('/', '.', '-'), '', $order->getData('customer_taxvat'));

      // If the field CNPJ are set uses field CNPJ
      if ($customerData->getData('cnpj')) {

        $data['cliente']['cnpj'] = $customerData->getData('cnpj');
        $data['cliente']['razao_social'] = $customerData->getData('razaosocial');
        if ($customerData->getData('insestadual')) $data['cliente']['ie'] = $customerData->getData('insestadual');

      // If are set the taxvat field with 14 chars (CNPJ length) uses taxvat as CNPJ
      } else if(strlen($cpf_cnpj) == 14) {

        $data['cliente']['cnpj'] = $order->getData('customer_taxvat');
        $data['cliente']['razao_social'] = $shipping_address->getFirstname().' '.$shipping_address->getLastname();
        if ($customerData->getData('inscest')) $data['cliente']['ie'] = $customerData->getData('inscest');

      // Else uses taxvat as CPF
      } else {

        $data['cliente']['cpf'] = $order->getData('customer_taxvat');
        $data['cliente']['nome_completo'] = $shipping_address->getFirstname().' '.$shipping_address->getLastname();

      }

      //Informações Complementares ao Fisco
      $fiscoinf = Mage::getStoreConfig('nfe/section_three/fisco_inf', Mage::app()->getStore());

      if(!empty($fiscoinf) && strlen($fiscoinf) <= 2000){
        $data['pedido']['informacoes_fisco'] = $fiscoinf;
      }

      //Informações Complementares ao Consumidor
      $consumidorinf = Mage::getStoreConfig('nfe/section_three/cons_inf', Mage::app()->getStore());

      if(!empty($consumidorinf) && strlen($consumidorinf) <= 2000){
        $data['pedido']['informacoes_complementares'] = $consumidorinf;
      }

      $items = $order->getAllVisibleItems();
      $bundle_products = array();

      foreach($items as $item):
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        $total = $item->getPrice() * $item->getData('qty_ordered');

        $product_type = $product->getTypeId();


        if($product_type == 'bundle'){

          $bundle_products[] = $item;
          continue;
        }

        $ignorar = $product->getData('ignorar_nfe');
        if($ignorar == 1){
          $data['pedido']['total'] -= $total;

          foreach($percentages_applied as $percentage){
            $data['pedido']['total'] += ($percentage/100) * $total;
            $data['pedido']['desconto'] -= ($percentage/100) * $total;
          }

          $data['pedido']['total'] = number_format($data['pedido']['total'], 2);
          $data['pedido']['desconto'] = number_format($data['pedido']['desconto'], 2);
          continue;
        }

        $product_ncm = $product->getData('codigo_ncm');

        if(empty($product_ncm)){

          $categories = $product->getCategoryIds();
          foreach($categories as $cat_id){
            $cat = Mage::getModel('catalog/category')->load($cat_id);
            $product_ncm = $cat->getData('category_ncm');
            if($product_ncm) break;
          }

        }

        if(empty($product_ncm)) $product_ncm = $this->ncm;

        $product_ean = $product->getData('codigo_ean');
        if(empty($product_ean)) $product_ean = $this->ean;

        $product_cest = $product->getData('codigo_cest');
        if(empty($product_cest)) $product_cest = $this->cest;

        $product_classe_imposto = $product->getData('classe_imposto');
        if(empty($product_classe_imposto)) $product_classe_imposto = $this->classe_imposto;

        $product_origem = substr($product->getAttributeText('origem_produto'), 0, 1);
        if(!is_string($product_origem)) $product_origem = $this->origem;

        $data['produtos'][] = array(
          'nome' => $item->getName(),
          'sku' => $item->getSku(),
          'ean' => $product_ean,
          'ncm' => $product_ncm,
          'cest' => $product_cest,
          'quantidade' => (int) $item->getData('qty_ordered'),
          'unidade' => 'UN',
          'peso' => number_format($item->getWeight(), 3, '.', ''), // Peso em KG. Ex: 800 gramas = 0.800 KG
          'origem' => (int)$product_origem,
          'subtotal' => number_format($item->getPrice(), 2, '.', ''),
          'total' => number_format($total, 2, '.', ''),
          'classe_imposto' => $product_classe_imposto
        );

      endforeach;

      if(!empty($bundle_products)){

        $bundle_info = $this->get_bundled_products($bundle_products);
        $bundle_items = array_values($bundle_info['items']);
        $bundle_discount = $bundle_info['discount'];

        if(!isset($data['produtos'])) $data['produtos'] = array();
        $data['produtos'] = array_merge($data['produtos'], $bundle_items);


        if($bundle_discount > 0){
          $prev_discount = $data['pedido']['desconto'];
          $updated_discount = $prev_discount + $bundle_discount;
          $data['pedido']['desconto'] = number_format($updated_discount, 2, '.', '');
        }
      }

      $include_shipping_info = Mage::getStoreConfig('nfe/section_four/transp_include', Mage::app()->getStore());

      if($include_shipping_info){

        $method = Mage::getStoreConfig('nfe/section_four/transp_method', Mage::app()->getStore());
        $order_carrier = $order->getShippingMethod();

        if($method.'_'.$method == $order_carrier){
          $data['transporte'] = array(
            'cnpj'         => Mage::getStoreConfig('nfe/section_four/transp_cnpj'),
            'razao_social' => Mage::getStoreConfig('nfe/section_four/transp_rs'),
            'ie'           => Mage::getStoreConfig('nfe/section_four/transp_ie'),
            'endereco'     => Mage::getStoreConfig('nfe/section_four/transp_address'),
            'uf'           => Mage::getStoreConfig('nfe/section_four/transp_uf'),
            'cidade'       => Mage::getStoreConfig('nfe/section_four/transp_city'),
            'cep'          => Mage::getStoreConfig('nfe/section_four/transp_cep'),
          );

          $transporte_info = $order->getData('nfe_transporte_info');

          if(is_null($transporte_info)){
            $transporte_info = array(
              'volume' => '',
              'especie' => '',
              'peso_bruto' => '',
              'peso_liquido' => '',
              'modalidade_frete' => '',
            );
          }else{
            $transporte_info = json_decode($transporte_info, true);
          }

          foreach($transporte_info as $api_key => $value){

            if($api_key == 'modalidade_frete') continue;
            if($value){
              $data['transporte'][$api_key] = $value;
            }

          }

          if($transporte_info['modalidade_frete']){
            $data['pedido']['modalidade_frete'] = $transporte_info['modalidade_frete'];
          }

        }




      }

      $response = self::connect_webmaniabr( 'POST', 'https://webmaniabr.com/api/1/nfe/emissao/', $data );
      /* print_r($response); exit; // Debug */
      return $response;

    }
  }

  public function get_bundled_products( $bundles = array() ) {

    $bundled_product_ids = array();
    $bundle_items = array();
    $total_discount = 0;

    foreach($bundles as $item){

      $product = new Mage_Catalog_Model_Product();
      $product->load($item->getProductId());


      $bundled_products = $product->getTypeInstance(true)->getSelectionsCollection(
      $product->getTypeInstance(true)->getOptionsIds($product), $product
      );

      $item_total = $item->getPrice() * $item->getData('qty_ordered');
      $b_products_total = 0;


      foreach($bundled_products as $option){

        $product_id = $option->product_id;

        $b_product = Mage::getModel('catalog/product')->load($product_id);

        $product_qty = $option->selection_qty;
        $total_qty = $item->getData('qty_ordered') * $product_qty;
        $b_product_total = $b_product->getPrice() * $total_qty;

        $b_products_total += $b_product_total;


        if(!in_array($product_id, $bundled_product_ids)){

          $bundled_product_ids[] = $product_id;
          $b_product = Mage::getModel('catalog/product')->load($product_id);

          $b_product_ncm = $b_product->getData('codigo_ncm');

          if(empty($b_product_ncm)){

            $categories = $b_product->getCategoryIds();
            foreach($categories as $cat_id){
              $cat = Mage::getModel('catalog/category')->load($cat_id);
              $b_product_ncm = $cat->getData('category_ncm');
              if($b_product_ncm) break;
            }

          }

          if(empty($b_product_ncm)) $b_product_ncm = $this->ncm;

          $b_product_ean = $b_product->getData('codigo_ean');
          if(empty($b_product_ean)) $b_product_ean = $this->ean;

          $b_product_cest = $b_product->getData('codigo_cest');
          if(empty($b_product_cest)) $b_product_cest = $this->cest;

          $b_product_classe_imposto = $b_product->getData('classe_imposto');
          if(empty($b_product_classe_imposto)) $b_product_classe_imposto = $this->classe_imposto;

          $b_product_origem = substr($b_product->getAttributeText('origem_produto'), 0, 1);
          if(!is_string($b_product_origem)) $b_product_origem = $this->origem;

          $bundle_items[$product_id] = array(
            'nome' => $b_product->getName(),
            'sku' => $b_product->getSku(),
            'ean' => $b_product_ean,
            'ncm' => $b_product_ncm,
            'cest' => $b_product_cest,
            'quantidade' => (int) $total_qty,
            'unidade' => 'UN',
            'peso' => number_format($b_product->getWeight(), 3, '.', ''), // Peso em KG. Ex: 800 gramas = 0.800 KG
            'origem' => (int)$b_product_origem,
            'subtotal' => number_format($b_product->getPrice(), 2, '.', ''),
            'total' => number_format($b_product_total, 2, '.', ''),
            'classe_imposto' => $b_product_classe_imposto
          );

        }else{
          $prev_qty = $bundle_items[$product_id]['quantidade'];
          $updated_qty = $prev_qty + $total_qty;
          $bundle_items[$product_id]['quantidade'] = (int) $updated_qty;

          $prev_total = $bundle_items[$product_id]['total'];
          $updated_total = $prev_total + $b_product_total;
          $bundle_items[$product_id]['total'] = number_format($updated_total, 2, '.', '');
        }


      }

      $price_diff = $b_products_total - $item_total;

      if($price_diff > 0){
        $total_discount += $price_diff;
      }

    }

    return array('items' => $bundle_items, 'discount' => $total_discount);

  }

  function updateNotaFiscal($chave_acesso){

    $data['chave'] = $chave_acesso;
    $this->consumerKey = Mage::getStoreConfig('nfe/section_one/consumer_key', Mage::app()->getStore());
    $this->consumerSecret = Mage::getStoreConfig('nfe/section_one/consumer_secret', Mage::app()->getStore());
    $this->accessToken = Mage::getStoreConfig('nfe/section_one/access_token', Mage::app()->getStore());
    $this->accessTokenSecret = Mage::getStoreConfig('nfe/section_one/access_token_secret', Mage::app()->getStore());

    $response = self::connect_webmaniabr( 'GET', 'https://webmaniabr.com/api/1/nfe/consulta/', $data );
    return $response;

  }

  //	Conexão com a API
  function connect_webmaniabr( $request, $endpoint, $data ){

      // Verify cURL
      if (!function_exists('curl_version')){
        $curl_error = new StdClass;
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
          $timeout = 5;
      } else {
          $timeout = 300;
      }

      // Header
      $headers = array(
        'Cache-Control: no-cache',
        'Content-Type:application/json',
        'X-Consumer-Key: '.$this->consumerKey,
        'X-Consumer-Secret: '.$this->consumerSecret,
        'X-Access-Token: '.$this->accessToken,
        'X-Access-Token-Secret: '.$this->accessTokenSecret
      );

      // Init connection
      $rest = curl_init();
      curl_setopt($rest, CURLOPT_CONNECTTIMEOUT , $timeout);
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
      $curl_error = new StdClass;
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
      if ( isset($curl_error->error) ){
          return $curl_error;
      } else {
          return json_decode($response);
      }

  }

  public function getSalesOrderViewInfo( Varien_Event_Observer $observer ) {

    $block = $observer->getBlock();
    $observer->getOrder();

    if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('nfe.order.info.custom.block'))) {
      $transport = $observer->getTransport();
      if ($transport) {
        $html = $transport->getHtml();
        $html .= $child->toHtml();
        $transport->setHtml($html);
      }
    }

  }

  public function getOrderTransporteViewInfo( Varien_Event_Observer $observer){

    $block = $observer->getBlock();
    $observer->getOrder();

    if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('nfe.order.transporte.info'))) {
      $transport = $observer->getTransport();
      if ($transport) {
        $html = $transport->getHtml();
        $html .= $child->toHtml();
        $transport->setHtml($html);
      }
    }

  }

  public function listenNotification(){

   if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['retorno_nfe']) && $_GET['order_id']){


      $order_id = (int) $_GET['order_id'];
      $uniq_key = Mage::getStoreConfig('nfe/section_one/uniq_get_key', Mage::app()->getStore());

      if($_GET['retorno_nfe'] == $uniq_key){
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $order_nfe_info = unserialize(base64_decode($order->getData('all_nfe')));

        if(!$order_nfe_info) $order_nfe_info = array();

        foreach($order_nfe_info as $key => $nfe){
          $numero_nfe = $nfe['n_nfe'];

          $current_status = $nfe['status'];
          $received_status = $_POST['status'];

          if($numero_nfe == $_POST['nfe'] && $current_status != $received_status){

            $order_nfe_info[$key]['status'] = $received_status;
            $nfe_info_str = base64_encode(serialize($order_nfe_info));
            $order->setData('all_nfe', $nfe_info_str);
            $order->save();
            break;
          }

        }

      }

      die();

    }

}

}
