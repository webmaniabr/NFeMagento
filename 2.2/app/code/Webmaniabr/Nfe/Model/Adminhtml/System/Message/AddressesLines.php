<?php
namespace Webmaniabr\Nfe\Model\Adminhtml\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;

/**
* Class AddressesLines
*/
class AddressesLines implements MessageInterface
{
  
  protected $scopeConfig;
  
  protected $nfeData;
  
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NfeData $nfeData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->nfeData = $nfeData;
    }
    
   /**
    * Message identity
    */
   const MESSAGE_IDENTITY = 'webmaniabr_nfe_messages_addresses_lines';

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
        $address_lines = $this->scopeConfig->getValue('customer/address/street_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $custom_address = $this->scopeConfig->getValue('webmaniabr_nfe_address/webmaniabr_nfe_address_lines/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        // The message will be shown
        if ($custom_address){

            return false;
            
        } elseif ( $address_lines && $address_lines < 4 ){

            return true;

        }
        
   }

   /**
    * Retrieve system message text
    *
    * @return \Magento\Framework\Phrase
    */
    public function getText()
    {

        return 'WebmaniaBR NF-e: Configure o mapeamento das linhas de endereço do site. Obrigatório: Endereço, Número, Complemento e Bairro. <br>';

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