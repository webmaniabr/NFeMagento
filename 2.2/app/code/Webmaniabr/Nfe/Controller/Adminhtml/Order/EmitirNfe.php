<?php
namespace Webmaniabr\Nfe\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;

class EmitirNfe extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    protected $orderManagement;
    
    protected $scopeConfig;
    
    protected $nfeData;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        \Magento\Backend\Model\Auth\Session $authSession,
        ScopeConfigInterface $scopeConfig,
        NfeData $nfeData
    ) {
        parent::__construct($context, $filter);
        $this->authSession = $authSession;
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->scopeConfig = $scopeConfig;
        $this->nfeData = $nfeData;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $print_br_success = $print_br_error = $print_br_nfe_duplicada = $print_br_error_address = $print_br_error_nfe = false;
        $response_success = $response_error = $response_nfe_duplicada = $response_error_address = $response_error_nfe = '';

        $username = $this->authSession->getUser()->getUsername();

        foreach ($collection->getItems() as $order) {

            // Get nfe_duplicada option
            $nfe_duplicada = $this->nfeData->get_nfe_duplicada();
            
            // Retrieve the order id
            $order_id = (int) $order->getEntityId();
            
            // If the order don't exists ignore the iteration
            if (!$order->getEntityId()) {
                
                // If is the first success, dont use the auxiliar breakline
                if( $print_br_error ) { $response_error .= "<br>"; } else { $print_br_error = true; }
                
                // Store the success message
                $response_error .= "O pedido possui um erro em seu identificador.";
                
                continue;
            }
            
            $result = $this->nfeData->get_status_order_by_id($order_id);
            
            // Check if the actual order already has a NF-e with Status "Emitida"
            // and the option nfe_duplicada is allowed
            if( ($result == "Emitida" || $result == "aprovado") && !$nfe_duplicada ) {
                
                $response_nfe_duplicada .= "A NF-e para o pedido #". $order_id . " já foi emitida.<br>";

                // If is, cancel the iteration
                continue;
                
            }

            // Prepare the data for API
            $data = $this->nfeData->get_the_order_data_by_id($order_id);
            
            // Call function to connect to API
            $response = $this->nfeData->emitir_nfe($order_id, $data);
            
            // If is set a error, store message and continue
            if ( is_array($response) ) {
                
                if ( isset($response["return"]) ) {
                    
                    // If is the first success, dont use the auxiliar breakline
                    if( $print_br_error_nfe ) { $response_error_nfe .= "<br>"; } else { $print_br_error_nfe = true; }
                    
                    // Store the success message
                    $response_error_nfe .= $response["message"];
                    
                    continue;
                    
                }
                
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
            
            $this->nfeData->register_comment($order_id, $username, $url_danfe);
            
            $this->nfeData->add_status_nfe( $order_id, $uuid, $chave_acesso, $n_recibo, $n_nfe, $n_serie, $url_xml, $url_danfe, $status, $date );

            // If is the first success, dont use the auxiliar breakline
            if( $print_br_success ) { $response_success .= "<br>"; } else { $print_br_success = true; }
            
            // Store the success message
            if ( $this->nfeData->get_emissao_assincrona() ) {
                $response_success .= "[Assíncrono] A NF-e do pedido #".$order_id." foi comunicada e seu status será atualizado quando processado pela Sefaz. ";
            } else {
                $response_success .= "A NF-e do pedido #".$order_id." foi gerada com sucesso. ";    
            }
            

        }
        
        // ---- ERRORS ---- //
        
            // If has $response_success, print
            if( $response_success && $data ) {
                $this->messageManager->addSuccess($response_success);
            }
            
            // If has $response_error, print
            if( $response_error ) {
                $this->messageManager->addError($response_error);
            }
            
            // If has $response_nfe_duplicada, print
            if( $response_nfe_duplicada ) {
                
                $msg_nfe_duplicada = "<br>Se deseja emitir novamente libere a opção <b>Permitir Emissão Dupla de NF-e</b> em:<br>
                                        <b>Lojas -> Configurações -> WebmaniaBR NF-e -> Configurações Gerais</b>";
     
                $this->messageManager->addError($response_nfe_duplicada . $msg_nfe_duplicada);
     
            }
            
            if( $response_error_address ) {
                $this->messageManager->addError($response_error_address);
            }
            
            if( $response_error_nfe ) {
                $this->messageManager->addError($response_error_nfe);
            }
            
        // **** ERRORS **** //
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
    
}