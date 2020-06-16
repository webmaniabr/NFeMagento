<?php
namespace Webmaniabr\Nfe\Model\Adminhtml\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;

/**
* Class CustomNotification
*/
class Tokens implements MessageInterface
{
  
  protected $scopeConfig;
  
  protected $nfeData;
  
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NfeData $nfeData
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->nfeData = $nfeData;
        $this->is_empty = false;
        $this->tokens = $this->nfeData->get_access_permissions();
    }
    
   /**
    * Message identity
    */
   const MESSAGE_IDENTITY = 'webmaniabr_nfe_messages_tokens';

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
        foreach ($this->tokens as $key => $token ) {
            
            if( empty($token) ) {
                $this->is_empty = true;
            }
            
        }
        
        // Return the message
        if( $this->is_empty ) {
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
        
        foreach ($this->tokens as $key => $token ) {
            
            if( empty($token) ) {
                $missing_keys[] = $key;
                $this->is_empty = true;
            }
            
        }
        
        if( $this->is_empty ) {
            // Return the message
            
            $loop = 1;
            $error_message = "WebmaniaBR NF-e: Configure as suas credenciais de acesso <br>";
            
            foreach ( $missing_keys as $missing_key ) {
                if ( $loop > 1 ) { $error_message .= " & "; }
                $loop++;
                $error_message .= $missing_key;
            }
            
            return $error_message;
        }

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