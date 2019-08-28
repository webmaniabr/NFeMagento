<?php
namespace Webmaniabr\Nfe\Model\Adminhtml\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;

/**
* Class AddressesLines
*/
class ModuleConfig implements MessageInterface
{
  
  protected $scopeConfig;
  
  protected $nfeData;
  
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NfeData $nfeData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->nfeData = $nfeData;
        $this->tributos = $this->nfeData->get_dados_tributo();
    }
    
   /**
    * Message identity
    */
   const MESSAGE_IDENTITY = 'webmaniabr_nfe_messages_module_configs';

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
        if( empty($this->tributos["natureza_operacao"]) || empty($this->tributos["classe_imposto"]) || empty($this->tributos["codigo_ncm"]) || $this->tributos["origem_produto"] < 0 ){
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
        
        $error_message = 'WebmaniaBR NF-e:';
        
        if ( empty($this->tributos["natureza_operacao"]) ) {
            $error_message .= "<br>Configure a Natureza da Operação.";
        }
        
        if ( empty($this->tributos["classe_imposto"] ) ) {
            $error_message .= "<br>Configure a Classe de Imposto.";
        }
        
        if ( empty($this->tributos["codigo_ncm"]) ) {
            $error_message .= "<br>Configure o Código NCM.";
        }
        
        if ( $this->tributos["origem_produto"] < 0 ) {
            $error_message .= "<br>Configure a Origem do Produto.";
        }

        return $error_message;
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