<?php
namespace Webmaniabr\Nfe\Observer;
use Webmaniabr\Nfe\Helper\NfeData;
use Magento\Framework\Event\ObserverInterface;
 
class CheckOrderStatus implements ObserverInterface {
 
    protected $connector; public function __construct(
        NfeData $nfeData
    ) { 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $this->nfeData = $nfeData;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        // If is allowed automatic emission
        if ( $this->nfeData->get_emissao_automatica() ) {

            $order = $observer->getEvent()->getOrder();
            
            if ( $order->getStatus() == "processing" || $order->getStatus() == "complete" ) {
                
                // Get the order ID
                $order_id = $order->getId();
                
                // Get the status order by id
                $result = $this->nfeData->get_status_order_by_id($order_id);
                
                // Obtain if are allowed to request duplicate NF-e
                $nfe_duplicada = $this->nfeData->get_nfe_duplicada();
                
                // Check if the actual order already has a NF-e with Status "Emitida"
                // and the option nfe_duplicada is allowed
                if( $result == "Emitida" && !$nfe_duplicada ) return;
                
                // Prepare the data for API
                $data = $this->nfeData->get_the_order_data_by_id($order_id);
                
                // Call function to connect to API
                $response = $this->nfeData->emitir_nfe($order_id, $data);
                
                // Store the response of the WebmaniaBR REST API
                $uuid = $response->uuid;
                $chave_acesso = $response->chave;
                $n_recibo = ( isset($response->n_recibo) ? $response->n_recibo : "" );
                $n_nfe = $response->nfe;
                $n_serie = $response->serie;
                $url_xml = $response->xml;
                $url_danfe = $response->danfe;
                $status = $response->status;
                $date = date('Y-m-d H:i:s');
                
                $username = "Módulo WebmaniaBR - Emissão Automática";
                
                $this->nfeData->register_comment( $order_id, $username, $url_danfe );
                
                $this->nfeData->add_status_nfe( $order_id, $uuid, $chave_acesso, $n_recibo, $n_nfe, $n_serie, $url_xml, $url_danfe, $status, $date );
                
            }

        }

    }
}