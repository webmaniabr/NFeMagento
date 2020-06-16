<?php

namespace Webmaniabr\Nfe\Model\Adminhtml\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Pagamentos extends Value
{
    /**
     * @var Random
     */
    protected $mathRandom;
    
    /**
     * DocumentNameMapping constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param Random                $mathRandom
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Random $mathRandom,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $aux = 0;
        
        foreach ($value as $data) {
            if ( 
                    empty($data['metodo_pagamento'])
                ||  empty($data['forma_pagamento'])
                ||  $data['metodo_pagamento'] == "nulo"
                ||  $data['forma_pagamento'] == "nulo"
                ) {
                continue;
            }
        
            $result[$aux]['metodo_pagamento'] = $data['metodo_pagamento'];
            $result[$aux++]['forma_pagamento'] = $data['forma_pagamento'];

        }
        
        if ( !empty($result) ) {
            $this->setValue(serialize($result));
            return $this;            
        } else {
            return null;
        }

    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {

        $value = unserialize($this->getValue());

        if (is_array($value)) {
        
            $value = $this->encodeArrayFieldValue($value);
            $this->setValue($value);
            
        }

        return $this;
    }

    /**
     * Encode value to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];

        foreach ($value as $documentName => $componentCode) {
          
            $id = $this->mathRandom->getUniqueHash('_');
            
            $result[$id] = [
                              'metodo_pagamento' => $componentCode["metodo_pagamento"],
                              'forma_pagamento' => $componentCode["forma_pagamento"],
                            ];

        }
        
        return $result;
    }

    /**
     * Append unique countries to list of exists and reindex keys
     *
     * @param array $docNamesList
     * @param array $compCodesList
     * @return array
     */
    private function appendUniqueCompCodes(array $docNamesList, array $compCodesList)
    {
        $result = array_merge($docNamesList, $compCodesList);
        return array_values(array_unique($result));
    }
    
}