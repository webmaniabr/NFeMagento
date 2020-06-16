<?php
/**
 * My own options
 *
 */
namespace Webmaniabr\Nfe\Config\Source;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class Linhasendereco implements \Magento\Framework\Option\ArrayInterface
{
  
    protected $options = "static";
    protected $collectionFactory;
    
    public function __construct(
          CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }
    
    public function toOptionArray()
    {
      
        if ($this->options === null) {
            $status = $this->collectionFactory->create();
            
            foreach ($status as $stat) {
                $this->options[] = [
                    'value' => $stat->getStatus(),
                    'label' => $stat->getLabel(),
                ];
            }
            
        } else {
          
          return array(
            array('value'=>0, 'label'=>'Street Line 1'),
            array('value'=>1, 'label'=>'Street Line 2'),
            array('value'=>2, 'label'=>'Street Line 3'),
            array('value'=>3, 'label'=>'Street Line 4'),
          );
          
        }
        
        return $this->options;
    }
    
}
 
?>