<?php
namespace Webmaniabr\Nfe\Controller\Adminhtml\Order;

use Webmaniabr\Nfe\Controller\Adminhtml\Order\pdf\PDFMerger;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;

class ImprimirDanfe extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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

    $username = $this->authSession->getUser()->getUsername();

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $dir = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    $connection = $resource->getConnection();
    $tableName = $resource->getTableName('webmaniabrnfe_orders_nfe');


    $danfes = array();
    foreach ($collection->getItems() as $order) {
    
      $order_id = (int) $order->getEntityId();
      $sql = "SELECT url_danfe, chave_acesso FROM {$tableName} WHERE order_id = {$order_id}";
      $result = $connection->fetchAll($sql);
      
      if (!empty($result)) {
        $data = end($result);
        $danfes[] = array('chave' => $data['chave_acesso'], 'url' => $data['url_danfe']);
      }
    
    }

    if (!empty($danfes)) {

      $directory = $dir->getPath('media') . '/pdf_files/';
      if (!file_exists($directory)) {
        mkdir($directory);
      }

      $pdf = new PDFMerger();

      foreach ($danfes as $danfe) {

        file_put_contents("{$directory}/{$danfe['chave']}.pdf", file_get_contents($danfe['url']));
			  $pdf->addPDF("{$directory}/{$danfe['chave']}.pdf", 'all');

      }

      $filename = time()."-".random_int(1, 10000000000);
		  $result = $pdf->merge('file', "{$directory}/{$filename}.pdf");

    }

    $resultRedirect = $this->resultRedirectFactory->create();
    if (!$result) {
      $this->messageManager->addError('Erro ao gerar o PDF do DANFE das notas fiscais');
      
      $resultRedirect->setPath($this->getComponentRefererUrl());
      return $resultRedirect;
    }

    $redirect_url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "pdf_files/{$filename}.pdf";
    $resultRedirect->setPath($redirect_url);
    return $resultRedirect;

  }

}