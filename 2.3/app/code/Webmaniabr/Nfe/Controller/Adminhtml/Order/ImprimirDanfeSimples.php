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

class ImprimirDanfeSimples extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

  protected $orderManagement;
    
  protected $scopeConfig;
    
  protected $nfeData;

  protected $authSession;

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
        $danfes[] = array('chave' => $data['chave_acesso'], 'url' => str_replace('/danfe/', '/danfe/simples/', $data['url_danfe']));
      }
    
    }

    $directory = \WebmaniaNFeSecurity::privateDirectory();
    $sources = array();
    $pdf = new PDFMerger();
    foreach ($danfes as $danfe) {
        $content = \WebmaniaNFeSecurity::download($danfe['url'], $this->nfeData->get_access_permissions());
        if ($content === false) { continue; }
        $source = $directory . \WebmaniaNFeSecurity::randomToken() . '.pdf';
        if (file_put_contents($source, $content) !== false) { $sources[] = $source; $pdf->addPDF($source, 'all'); }
    }
    $path = $directory . 'danfe.pdf';
    $result = false;
    try { if ($sources) { $result = $pdf->merge('file', $path); } }
    catch (\Exception $e) { $result = false; }
        catch (\Throwable $e) { $result = false; }
    foreach ($sources as $source) { if (is_file($source)) { unlink($source); } }
    $content = $result && is_file($path) ? file_get_contents($path) : false;
    if (is_file($path)) { unlink($path); }
    rmdir($directory);
    if ($content === false) {
        $this->messageManager->addError('Não foi possível gerar o PDF das notas fiscais.');
        $redirect = $this->resultRedirectFactory->create();
        return $redirect->setPath($this->getComponentRefererUrl());
    }
    return $objectManager->get('Magento\Framework\App\Response\Http\FileFactory')->create('danfe.pdf', $content, 'var', 'application/pdf');
}

}