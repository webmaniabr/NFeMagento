<?php

namespace Webmaniabr\Nfe\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class StatusNfe extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $order  = $this->_orderRepository->get($item["entity_id"]);
                $order_id = $item['entity_id'];

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('webmaniabrnfe_orders_nfe');
        
                $sql = "SELECT status FROM " . $tableName . " WHERE order_id='" . $order_id . "' ORDER BY requested_at DESC LIMIT 1";
                $result = $connection->fetchAll($sql);

                if( empty($result) ) { $result = ""; } else { $result = $result[0]['status']; }

                switch (strtolower($result)) {
                    
                    case "aprovado":
                        $nfe_status = "Aprovada";
                        break;
                    case "cancelado":
                        $nfe_status = "cancelado";
                        break;
                    case "reprovado":
                        $nfe_status = "Reprovado";
                        break;
                    case "processamento":
                        $nfe_status = "Processamento";
                        break;
                    case "contingencia":
                        $nfe_status = "Contingência";
                        break;
                    default:
                        $nfe_status = "Não Emitida";
                        break;

                }

                $item[$this->getData('name')] = $nfe_status;
            }
        }

        return $dataSource;
    }
}