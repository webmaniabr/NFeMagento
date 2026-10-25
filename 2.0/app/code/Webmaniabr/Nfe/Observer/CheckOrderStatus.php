<?php
namespace Webmaniabr\Nfe\Observer;
use Webmaniabr\Nfe\Helper\NfeData;
use Magento\Framework\Event\ObserverInterface;
 
class CheckOrderStatus implements ObserverInterface {
 
    protected $connector;

    protected $nfeData;

    /* Orders whose automatic emission is running in this request. register_comment() saves the
    /* order again, which re-dispatches sales_order_save_after before the NF-e row is stored. */
    private static $processing = array();

    public function __construct(
        NfeData $nfeData
    ) { 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $this->nfeData = $nfeData;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        // If is allowed automatic emission
        if ( $this->nfeData->get_emissao_automatica() ) {

            $order = $observer->getEvent()->getOrder();
            
            if ( $order && ( $order->getStatus() == "processing" || $order->getStatus() == "complete" ) ) {
                
                // Get the order ID
                $order_id = (int) $order->getId();
                if ( !$order_id || isset(self::$processing[$order_id]) ) return;
                
                // Get the status order by id
                $result = $this->nfeData->get_status_order_by_id($order_id);
                
                // Obtain if are allowed to request duplicate NF-e
                $nfe_duplicada = $this->nfeData->get_nfe_duplicada();
                
                // Check if the actual order already has a NF-e with Status "Emitida" (or one still
                // being processed by Sefaz) and the option nfe_duplicada is allowed
                if( $this->nfeData->is_active_nfe_status($result) && !$nfe_duplicada ) return;
                
                self::$processing[$order_id] = true;
                try {
                    $this->emitir($order_id);
                } finally {
                    unset(self::$processing[$order_id]);
                }
                
            }

        }

    }

    private function emitir($order_id) {
                
                // Prepare the data for API
                $data = $this->nfeData->get_the_order_data_by_id($order_id);
                
                // Call function to connect to API
                $response = $this->nfeData->emitir_nfe($order_id, $data);
                
                // Errors are arrays; uncertain attempts have already been marked pending by emitir_nfe().
                if ( !is_object($response) ) {
                    $message = is_array($response) && isset($response['message']) && is_scalar($response['message']) ? (string) $response['message'] : '';
                    \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->error('WebmaniaBR NFe: automatic emission failed for order ' . $order_id . ': ' . $message);
                    return;
                }
                
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