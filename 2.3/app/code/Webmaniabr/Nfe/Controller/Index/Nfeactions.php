<?php
namespace Webmaniabr\Nfe\Controller\Index;

use Webmaniabr\Nfe\Helper\NfeData;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Nfeactions extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
	protected $nfeData;
	protected $_pageFactory;
	protected $request;

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        NfeData $nfeData
    ){
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->nfeData = $nfeData;
        parent::__construct($context);
    }
    
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

	public function execute() {
        // Validate only values consumed by routing/actions. Callback log/data may be arrays;
        // they are ignored because invoice fields are confirmed server-to-server via the API.
        foreach (array('nfe_callback', 'nfe_btn_emitir', 'order_id', 'action', 'utilizar_transportadora', 'volume', 'especie', 'peso_bruto', 'peso_liquido', 'key', 'uuid') as $field) {
            if ((isset($_GET[$field]) && !is_scalar($_GET[$field])) || (isset($_POST[$field]) && !is_scalar($_POST[$field]))) {
                $this->getResponse()->setHttpResponseCode(400); return;
            }
        }
        if ((isset($_GET['nfe_callback']) || isset($_GET['nfe_btn_emitir']) || isset($_POST['action'])) &&
            (!isset($_GET['order_id']) && !isset($_POST['order_id']))) { $this->getResponse()->setHttpResponseCode(400); return; }

	    
	    if ( isset($_POST['action']) && (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') === 'POST' ) {
	        
	        if ( $_POST['action'] == "updatetransportadora" ) {
	            $this->handle_transportadoras_info( (isset($_POST["utilizar_transportadora"]) ? $_POST["utilizar_transportadora"] : ""), (isset($_POST["order_id"]) ? $_POST["order_id"] : 0), (isset($_POST["volume"]) ? $_POST["volume"] : ""), (isset($_POST["especie"]) ? $_POST["especie"] : ""), (isset($_POST["peso_bruto"]) ? $_POST["peso_bruto"] : ""), (isset($_POST["peso_liquido"]) ? $_POST["peso_liquido"] : ""), (isset($_POST["key"]) ? $_POST["key"] : "") );
	            return;
	        }

	    }
	    
	    if ( isset($_GET['nfe_callback']) && (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') === 'POST' ) {
	        $this->nfe_callback( $_GET['nfe_callback'], (isset($_GET['order_id']) ? $_GET['order_id'] : 0) );
	        return;
	    }
	    
	    if ( isset($_GET['nfe_btn_emitir']) ) {
	        $this->emitir_nfe_btn( $_GET["nfe_btn_emitir"], (isset($_GET["order_id"]) ? $_GET["order_id"] : 0) );
	        return;
	    }
	    
	    $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        header("Location: " . $storeManager->getStore()->getBaseUrl());
        die();
        
	}
	
    /* Handle informations received by ajax of order view
    /* @param (int) $utilizar_transportadora
    /* @param (int) $order_id
    /* @param (string) $volume
    /* @param (string) $especie
    /* @param (float) $peso_bruto
    /* @param (float) $peso_liquido
    /* @param (string) $secret_key
    /*
    /* return (string) $message
    */
	public function handle_transportadoras_info($utilizar_transportadora, $order_id, $volume, $especie, $peso_bruto, $peso_liquido, $secret_key) {
        if (!is_scalar($order_id) || !ctype_digit((string) $order_id) || (int) $order_id < 1 ||
            !is_string($secret_key) || strpos($secret_key, (string) (int) $order_id . '_') !== 0) {
            $this->getResponse()->setHttpResponseCode(403); return;
        }
        $order_id = (int) $order_id;

	    
        // Create an object to SQL use
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        
        $validation_secret_key = $this->nfeData->validate_secret_key( "nfe_ajax", $secret_key );
        
        if ( $validation_secret_key["type"] == "invalid" ) {
            echo $validation_secret_key["message"];
            die();
        }
        
        $values = array('utilizar_transportadora' => (int) $utilizar_transportadora,
            'volume' => (string) $volume, 'especie' => (string) $especie,
            'peso_bruto' => (string) $peso_bruto, 'peso_liquido' => (string) $peso_liquido);
        $table = $resource->getTableName('webmaniabrnfe_orders_transportadora');
        $exists = $connection->fetchOne($connection->select()->from($table, array('order_id'))->where('order_id = ?', $order_id)->limit(1));
        if ($exists) { $connection->update($table, $values, array('order_id = ?' => $order_id)); }
        else { $values['order_id'] = $order_id; $connection->insert($table, $values); }
        echo 'Dados salvos com sucesso.';
}
	
    /* Handle informations received by API callback
    /* @param (int) $order_id
    /* @param (string) $secret_key
    /*
    /* return (string) $message
    */
	public function nfe_callback($secret_key, $order_id) {
        if (!is_scalar($order_id) || !ctype_digit((string) $order_id) || (int) $order_id < 1 ||
            !is_string($secret_key) || strpos($secret_key, (string) (int) $order_id . '_') !== 0) {
            $this->getResponse()->setHttpResponseCode(403); return;
        }
        $order_id = (int) $order_id;
        $validation = $this->nfeData->validate_secret_key('nfe_callback', $secret_key);
        if ($validation['type'] !== 'valid') { $this->getResponse()->setHttpResponseCode(403); return; }
        $uuid = isset($_POST['uuid']) ? $_POST['uuid'] : null;
        if (!\WebmaniaNFeSecurity::validUuid($uuid)) { $this->getResponse()->setHttpResponseCode(400); return; }
        $response = $this->nfeData->connect_webmaniabr('GET', 'https://webmaniabr.com/api/1/nfe/consulta/', array('uuid' => $uuid));
        if (isset($response->error) || !isset($response->uuid) || $response->uuid !== $uuid) {
            $this->getResponse()->setHttpResponseCode(502); return;
        }
        if (isset($response->ID) && (!is_scalar($response->ID) || (string) $response->ID !== (string) $order_id)) {
            $this->getResponse()->setHttpResponseCode(403); return;
        }
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table = $resource->getTableName('webmaniabrnfe_orders_nfe');
        $rows = $connection->fetchAll($connection->select()->from($table, array('order_id'))->where('uuid = ?', $uuid));
        foreach ($rows as $row) {
            if ((int) $row['order_id'] !== $order_id) { $this->getResponse()->setHttpResponseCode(403); return; }
        }
        $values = array('chave_acesso' => $response->chave, 'n_recibo' => $response->recibo,
            'n_nfe' => (int) $response->nfe, 'n_serie' => (int) $response->serie,
            'url_xml' => $response->xml, 'url_danfe' => $response->danfe,
            'status' => $response->status);
        if ($rows) { $connection->update($table, $values, array('uuid = ?' => $uuid, 'order_id = ?' => $order_id)); }
        else { $values['requested_at'] = date('Y-m-d H:i:s'); $values['uuid'] = $uuid; $values['order_id'] = $order_id; $connection->insert($table, $values); }
        echo 'NF-e atualizada com sucesso.';

}
	
    /* Trigger actions to emission of NF-e
    /* @param (int) $order_id
    /* @param (string) $secret_key
    /*
    /* return (string) $message
    */
    public function emitir_nfe_btn( $secret_key, $order_id ) {
        if (!is_scalar($order_id) || !ctype_digit((string) $order_id) || (int) $order_id < 1 ||
            !is_string($secret_key) || strpos($secret_key, (string) (int) $order_id . '_') !== 0) {
            $this->getResponse()->setHttpResponseCode(403); return;
        }
        $order_id = (int) $order_id;

        
        $validation_secret_key = $this->nfeData->validate_secret_key( "nfe_btn_emitir", $secret_key );
        
        if ( $validation_secret_key["type"] == "invalid" ) {
            echo $validation_secret_key["message"];
            die();
        }

        // Get the status order by id
        $result = $this->nfeData->get_status_order_by_id($order_id);
        
        // Obtain if are allowed to request duplicate NF-e
        $nfe_duplicada = $this->nfeData->get_nfe_duplicada();
        
        // Check if the actual order already has a NF-e with Status "Emitida"
        // and the option nfe_duplicada is allowed
        if( $this->nfeData->is_active_nfe_status($result) && !$nfe_duplicada ) return;
        
        // Prepare the data for API
        $data = $this->nfeData->get_the_order_data_by_id($order_id);
        
        // Call function to connect to API
        $response = $this->nfeData->emitir_nfe($order_id, $data);
        
        // If there's an error, die
        if ( is_array($response) ) {
            
            if ( isset($response["return"]) ) {
                
                var_dump($response);
                die();
                
            }
            
        }
        
        // Store the response of the WebmaniaBR REST API
        $uuid = $response->uuid;
        $chave_acesso = $response->chave;
        $n_recibo = ( isset($response->n_recibo) ? $response->n_recibo : "" );
        $n_nfe = $response->nfe;
        $n_serie = $response->serie;
        $url_xml = $response->xml;
        $url_danfe = $response->danfe;
        $status = $response->status;
        $date = date('Y-m-d H:i:s');
        
        $username = "Emitir NF-e - Order View";
        
        $this->nfeData->register_comment( $order_id, $username, $url_danfe );
        
        $this->nfeData->add_status_nfe( $order_id, $uuid, $chave_acesso, $n_recibo, $n_nfe, $n_serie, $url_xml, $url_danfe, $status, $date );
        
        ?>
        
        <html>
            <head>
                <title>Emissão de NF #<?php echo $order_id; ?></title>
                <meta name="robots" content="noindex">
                <meta name="googlebot" content="noindex">
            </head>

            <style>
                body {
                    text-align: center;
                    margin: 0 auto;
                    position: relative;
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                }
                                
                .box {
                    border: 1px solid;
                    max-width: 300px;
                    margin: 0 auto;
                    padding: 35px;
                    background: #188B55;
                    border-radius: 13px;
                    color: #FFF;
                    padding-bottom: 50px;
                }
            </style>

            <body>

                <div class='box'>
                    <h1>WebmaniaBR</h1>

                    Nota Fiscal do pedido nº <?php echo $order_id; ?> gerada com sucesso. 
                    
                    <br><br> Essa página irá fechar em <span id='counter'>5</span> segundos.
                </div>

            </body>
        </html>
        
        <script>
        
            function countdown() {
            
                var i = document.getElementById('counter');
                
                i.innerHTML = parseInt(i.innerHTML)-1;
                
                if (parseInt(i.innerHTML)<=0) {
                
                    window.close();
                
                }
            
            }
            
            setInterval(function(){ countdown(); },1000);
        
        </script>
        
        <?php
    }
	
}