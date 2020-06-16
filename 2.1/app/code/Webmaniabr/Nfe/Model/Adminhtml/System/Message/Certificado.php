<?php

namespace Webmaniabr\Nfe\Model\Adminhtml\System\Message;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webmaniabr\Nfe\Helper\NfeData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
* Class AddressesLines
*/
class Certificado implements MessageInterface
{
  
  protected $scopeConfig;
  
  protected $nfeData;
  
  protected $date;
  
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        NfeData $nfeData,
        TimezoneInterface $date
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->nfeData = $nfeData;
        $this->_date =  $date;
        $this->type_error = "default";
        $this->get_certificado_expiration();
    }
    
   /**
    * Message identity
    */
   const MESSAGE_IDENTITY = 'webmaniabr_nfe_messages_certificado';

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
        return $this->get_certificado_expiration();
        
   }

   /**
    * Retrieve system message text
    *
    * @return \Magento\Framework\Phrase
    */
    public function getText()
    {
        $error_message = "";
        
        if ( $this->error_type == "chave_invalida" ) {
            $error_message = 'WebmaniaBR NF-e: As chaves de acesso são inválidas.';
        } elseif ( $this->error_type == "certificado_expirado" ) {
            
            if ( $this->expiration == 0 ) {
                $error_message = 'WebmaniaBR NF-e: O certificado A1 está vencido ou não é válido.';
            } elseif ( $this->expiration < 45 ) {
                $error_message = 'WebmaniaBR NF-e: O certificado A1 vence em ' . $this->expiration . ' dias.';  
            }
            
        }
        
        return ( $error_message ? $error_message : "" );
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
  
    public function get_certificado_expiration () {
        
        // Store the cache with the informations saved
        $this->cache = $this->nfeData->get_cache_certificado_a1();

        // If the cache is set
        if ( $this->cache ) {
            
            date_default_timezone_set('America/Sao_Paulo');
    
            // Calculate the difference of cache and today
            $data_inicial = $this->_date->date()->createFromFormat( 'd/m/Y', date('d/m/Y') );
            $data_final = $this->_date->date()->createFromFormat( 'd/m/Y', $this->cache );
            $interval = $data_inicial->diff($data_final);
            
            // Store the difference in days
            $this->interval = $interval->format("%r%a");
        
            // 1. Check if interval from today to certicate cache date is lower than 45 days
            // 2. If the interval is negative, force the system renew the cache
            if ( $this->interval <= 45 && $this->interval > 0 ) {
                
                $this->error_type = "certificado_expirado";
                $this->expiration = $this->interval;
                
            }
            
            // If the interval is greater than 45 days doesn't show alert
            if ( $this->interval > 45 ) {
                return false;
            }
        
        }
        
        // Retrieve certificate expiration with API
        $this->certificado = $this->nfeData->connect_webmaniabr( 'GET', 'https://webmaniabr.com/api/1/nfe/certificado/', "" );
        
        // Check if the tokens are valid
        if ( !is_object($this->certificado) || isset($this->certificado->error) ) {
            $this->error_type = "chave_invalida";
            return true;
        }
        
        // Set the cache
        $this->nfeData->set_cache_certificado_a1($this->certificado->expiration);
        
        // Check the validation date
        if ( $this->certificado->expiration <= 45 ) {
            $this->error_type = "certificado_expirado";
            $this->expiration = ( $this->certificado->expiration < 1 ? 0 : $this->certificado->expiration );
            return true;
        } else {
            return false;
        }
        
    }
    
}