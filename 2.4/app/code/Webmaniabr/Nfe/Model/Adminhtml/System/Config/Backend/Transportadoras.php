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

class Transportadoras extends Value
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
                   empty($data['transportadora_metodo_entrega'])
                || empty($data['transportadora_razao_social'])
                || empty($data['transportadora_cnpj'])
                || empty($data['transportadora_ie'])
                || empty($data['transportadora_endereco'])
                || empty($data['transportadora_cep'])
                || empty($data['transportadora_uf'])
                || empty($data['transportadora_cidade'])
                
                ) {
                continue;
            }
        
            $result[$aux]['transportadora_metodo_entrega'] = $data['transportadora_metodo_entrega'];
            $result[$aux]['transportadora_razao_social'] = $data['transportadora_razao_social'];
            $result[$aux]['transportadora_cnpj'] = $data['transportadora_cnpj'];
            $result[$aux]['transportadora_ie'] = $data['transportadora_ie'];
            $result[$aux]['transportadora_endereco'] = $data['transportadora_endereco'];
            $result[$aux]['transportadora_cep'] = $data['transportadora_cep'];
            $result[$aux]['transportadora_uf'] = $data['transportadora_uf'];
            $result[$aux++]['transportadora_cidade'] = $data['transportadora_cidade'];

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
                              'transportadora_metodo_entrega' => $componentCode["transportadora_metodo_entrega"],
                              'transportadora_razao_social' => $componentCode["transportadora_razao_social"],
                              'transportadora_cnpj' => $componentCode["transportadora_cnpj"],
                              'transportadora_ie' => $componentCode["transportadora_ie"],
                              'transportadora_endereco' => $componentCode["transportadora_endereco"],
                              'transportadora_cep' => $componentCode["transportadora_cep"],
                              'transportadora_uf' => $componentCode["transportadora_uf"],
                              'transportadora_cidade' => $componentCode["transportadora_cidade"],
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