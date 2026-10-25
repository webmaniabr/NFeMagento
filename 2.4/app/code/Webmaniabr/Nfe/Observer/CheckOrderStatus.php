<?php
declare(strict_types=1);

namespace Webmaniabr\Nfe\Observer;

use Webmaniabr\Nfe\Helper\NfeData;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class CheckOrderStatus implements ObserverInterface
{
    /**
     * @var NfeData
     */
    protected $nfeData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Orders whose automatic emission is running in this request. register_comment() saves the
     * order again, which re-dispatches sales_order_save_after before the NF-e row is stored.
     *
     * @var array
     */
    private static $processing = [];

    /**
     * @param NfeData $nfeData
     * @param LoggerInterface $logger
     */
    public function __construct(
        NfeData $nfeData,
        LoggerInterface $logger
    ) { 
        $this->nfeData = $nfeData;
        $this->logger = $logger;
    }
 
    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $orderId = 0;
        $guarded = false;
        try {
            // If is allowed automatic emission
            if (!$this->nfeData->get_emissao_automatica()) {
                return;
            }

            $order = $observer->getEvent()->getOrder();
            
            if (!$order || (!in_array($order->getStatus(), ['processing', 'complete']))) {
                return;
            }
            
            // Get the order ID
            $orderId = (int)$order->getId();
            if (!$orderId || isset(self::$processing[$orderId])) {
                return;
            }
            
            // Get the status order by id
            $result = $this->nfeData->get_status_order_by_id($orderId);
            
            // Obtain if are allowed to request duplicate NF-e
            $nfeDuplicada = (bool)$this->nfeData->get_nfe_duplicada();
            
            // Check if the actual order already has a NF-e with Status "Emitida" (or one still
            // being processed by Sefaz) and the option nfe_duplicada is allowed
            if ($this->nfeData->is_active_nfe_status($result) && !$nfeDuplicada) {
                return;
            }
            
            self::$processing[$orderId] = true;
            $guarded = true;

            // Prepare the data for API
            $data = $this->nfeData->get_the_order_data_by_id($orderId);
            
            // Call function to connect to API
            $response = $this->nfeData->emitir_nfe($orderId, $data);
            
            if (!$response) {
                $this->logger->error('WebmaniaBR NFe: Empty response from API for order ' . $orderId);
                return;
            }

            // Errors are arrays; uncertain attempts have already been marked pending by emitir_nfe().
            if (!is_object($response)) {
                $message = is_array($response) && isset($response['message']) && is_scalar($response['message']) ? (string) $response['message'] : '';
                $this->logger->error('WebmaniaBR NFe: automatic emission failed for order ' . $orderId . ': ' . $message);
                return;
            }
            
            // Store the response of the WebmaniaBR REST API
            $uuid = $response->uuid ?? '';
            $chaveAcesso = $response->chave ?? '';
            $nRecibo = $response->n_recibo ?? '';
            $nNfe = $response->nfe ?? '';
            $nSerie = $response->serie ?? '';
            $urlXml = $response->xml ?? '';
            $urlDanfe = $response->danfe ?? '';
            $status = $response->status ?? '';
            $date = date('Y-m-d H:i:s');
            
            $username = "Módulo WebmaniaBR - Emissão Automática";
            
            $this->nfeData->register_comment($orderId, $username, $urlDanfe);
            $this->nfeData->add_status_nfe(
                $orderId, 
                $uuid, 
                $chaveAcesso, 
                $nRecibo, 
                $nNfe, 
                $nSerie, 
                $urlXml, 
                $urlDanfe, 
                $status, 
                $date
            );
            
        } catch (\Exception $e) {
            $this->logger->error('WebmaniaBR NFe Observer Error: ' . $e->getMessage());
        } finally {
            if ($guarded) {
                unset(self::$processing[$orderId]);
            }
        }
    }
}