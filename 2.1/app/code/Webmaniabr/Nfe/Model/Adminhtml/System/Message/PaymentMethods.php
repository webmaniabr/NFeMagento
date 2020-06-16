<?php
namespace Webmaniabr\Nfe\Model\Adminhtml\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;

/**
* Class AddressesLines
*/
class PaymentMethods implements MessageInterface
{
  
  protected $scopeConfig;
  
  protected $nfeData;
  
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NfeData $nfeData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->nfeData = $nfeData;
        $this->payment_methods = $this->nfeData->get_metodos_pagamento();
    }
    
   /**
    * Message identity
    */
   const MESSAGE_IDENTITY = 'webmaniabr_nfe_messages_payment_methods';

   /**
    * Retrieve unique system message identity
    *
    * @return string
    */
   public function getIdentity()
   {
       return self::MESSAGE_IDENTITY;
   }

   /**
    * Check whether the system message should be shown
    *
    * @return bool
    */
   public function isDisplayed()
   {
        // The message will be shown
        if( empty($this->payment_methods) ){
            return true;
        } else {
            return false;
        }
   }

   /**
    * Retrieve system message text
    *
    * @return \Magento\Framework\Phrase
    */
    public function getText()
    {

        return 'WebmaniaBR NF-e: Configure o mapeamento dos métodos de pagamento do seu site. <br>';

    }

   /**
    * Retrieve system message severity
    * Possible default system message types:
    * - MessageInterface::SEVERITY_CRITICAL
    * - MessageInterface::SEVERITY_MAJOR
    * - MessageInterface::SEVERITY_MINOR
    * - MessageInterface::SEVERITY_NOTICE
    *
    * @return int
    */
   public function getSeverity()
   {
       return self::SEVERITY_MAJOR;
   }
}