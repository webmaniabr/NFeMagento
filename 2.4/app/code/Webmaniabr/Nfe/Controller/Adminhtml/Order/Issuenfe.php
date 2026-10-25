<?php
namespace Webmaniabr\Nfe\Controller\Adminhtml\Order;

use Webmaniabr\Nfe\Helper\NfeData;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Issuenfe extends \Magento\Backend\App\Action
{
	const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';
    protected $_pageFactory;
	protected $request;
    protected $nfeData;

	public function __construct(
        \Magento\Backend\App\Action\Context $context,
        NfeData $nfeData
    ){
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->nfeData = $nfeData;
        parent::__construct($context);
    }
    




	public function execute() {
        $input = $this->getRequest()->getParam('order_id');
        $order_id = is_scalar($input) && ctype_digit((string) $input) ? (int) $input : 0;
        if ($order_id < 1) {
            $this->messageManager->addErrorMessage('Pedido não informado.');
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        // Get the status order by id
        $result = $this->nfeData->get_status_order_by_id($order_id);
        
        // Obtain if are allowed to request duplicate NF-e
        $nfe_duplicada = $this->nfeData->get_nfe_duplicada();

        // Create response
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $order_id]);
        
        // Check if the actual order already has a NF-e with Status "Emitida"
        // and the option nfe_duplicada is allowed
        if( $this->nfeData->is_active_nfe_status($result) && !$nfe_duplicada ) {
            $this->messageManager->addNoticeMessage("Já existe uma nota fiscal emitida para o pedido #{$order_id}.");
            return $resultRedirect;
        }
        
        // Prepare the data for API
        $data = $this->nfeData->get_the_order_data_by_id($order_id);
        
        // Call function to connect to API
        $response = $this->nfeData->emitir_nfe($order_id, $data);
        
        // If there's an error
        if (is_array($response)) {
            if (isset($response["return"])) {
                $this->messageManager->addErrorMessage($response['message']);
                return $resultRedirect;
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
        $username = "Emitir NF-e - Order View";
        $this->nfeData->register_comment( $order_id, $username, $url_danfe );
        $this->nfeData->add_status_nfe( $order_id, $uuid, $chave_acesso, $n_recibo, $n_nfe, $n_serie, $url_xml, $url_danfe, $status, $date );

        // Add success message
        $this->messageManager->addSuccessMessage("Nota Fiscal do pedido #{$order_id} gerada com sucesso.");
        return $resultRedirect;
	}
}