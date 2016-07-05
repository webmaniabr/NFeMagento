<?php
require_once 'Mage/Sales/Model/Observer.php';
class WebmaniaBR_NFe_Model_Observer extends Mage_Sales_Model_Observer {
    public function emitirNfe($order, $state = false, $status = false, $force = false)
    {
        
        include 'config.php';
        
        /* -----------------------------------------------------------
        ATENÇÃO: SOMENTE EDITE O CÓDIGO ABAIXO SE TIVER CONHECIMENTO
        Documentação: https://webmaniabr.com/docs/rest-api-nfe/
        ------------------------------------------------------------*/
        
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
        
        if ($state == $executar_quando || $force) {
        
            $orderno = $order->getIncrementId();
            $shipping_address = $order->getShippingAddress();
            $peso = $item->getWeight();
            
            $kg = explode('.', $peso);
            if (strlen($kg[0]) >= 3) { 
                
                $peso = $peso / 1000;
                
            }
            
            if (!$peso) $peso = '0.100';
            $peso = number_format($peso, 3, '.', '');

            $data = array(
                'ID' => (int) $orderno,
                'operacao' => 1,
                'natureza_operacao' => $natureza_operacao,
                'emissao' => 1,
                'finalidade' => 1,
                'ambiente' => $ambiente,
                'cliente' => array(
                    'cpf' => $order->getData('customer_taxvat'),
                    'nome_completo' => $shipping_address->getFirstname().' '.$shipping_address->getLastname(),
                    'endereco' => $shipping_address->getStreet(1),
                    'complemento' => $shipping_address->getStreet(3),
                    'numero' => (int) $shipping_address->getStreet(2),
                    'bairro' => $shipping_address->getStreet(4),
                    'cidade' => $shipping_address->getCity(),
                    'uf' => $shipping_address->getRegion(),
                    'cep' => $shipping_address->getPostcode(),
                    'telefone' => $shipping_address->getTelephone(),
                    'email' => $order->getCustomerEmail()
                ),
                'pedido' => array(
                    'pagamento' => 0,
                    'presenca' => 2,
                    'modalidade_frete' => 0,
                    'frete' => number_format($order->getShippingAmount(), 2, '.', ''),
                    'desconto' => number_format($order->getDiscountAmount(), 2, '.', ''),
                    'total' => number_format($order->getGrandTotal(), 2, '.', '')
                ),
            );

            $items = $order->getAllVisibleItems();
            foreach($items as $item):

                $total = $item->getPrice() * $item->getData('qty_ordered');

                $data['produtos'][] = array(
                    'nome' => $item->getName(),
                    'sku' => $item->getSku(),
                    'ncm' => $ncm,
                    'cest' => $cest,
                    'quantidade' => (int) $item->getData('qty_ordered'),
                    'unidade' => 'UN',
                    'peso' => $peso, // Peso em KG. Ex: 800 gramas = 0.800 KG
                    'origem' => $origem,
                    'subtotal' => number_format($item->getPrice(), 2, '.', ''),
                    'total' => number_format($total, 2, '.', ''),
                    'classe_imposto' => $classe_imposto
                );

            endforeach;

            $response = self::connect_webmaniabr( 'POST', 'https://webmaniabr.com/api/1/nfe/emissao/', $data );
            /* print_r($response); exit; // Debug */
            return $response;
        
        }
    }
    
    //	Conexão com a API
    function connect_webmaniabr( $request, $endpoint, $data ){

        @set_time_limit( 300 );
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);
        ini_set('memory_limit', '256M');

        $headers = array(
          'Content-Type:application/json',
          'X-Consumer-Key: '.$this->consumerKey,
          'X-Consumer-Secret: '.$this->consumerSecret,
          'X-Access-Token: '.$this->accessToken,
          'X-Access-Token-Secret: '.$this->accessTokenSecret,
        );

        $rest = curl_init();
        curl_setopt($rest, CURLOPT_CONNECTTIMEOUT , 300); 
        curl_setopt($rest, CURLOPT_TIMEOUT, 300);
        curl_setopt($rest, CURLOPT_URL, $endpoint.'?time='.time());
        curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rest, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($rest, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($rest, CURLOPT_CUSTOMREQUEST, $request);
        curl_setopt($rest, CURLOPT_POSTFIELDS, json_encode( $data ));
        curl_setopt($rest, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($rest);
        curl_close($rest);

        /* print_r($response); exit; // Debug */
        return json_decode($response);

    }

}