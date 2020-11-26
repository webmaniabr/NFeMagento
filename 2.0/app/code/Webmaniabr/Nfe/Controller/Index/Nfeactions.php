<?php
namespace Webmaniabr\Nfe\Controller\Index;

use Webmaniabr\Nfe\Helper\NfeActionsHelper;
use Webmaniabr\Nfe\Helper\NfeData;

class Nfeactions extends \Magento\Framework\App\Action\Action
{
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

	public function execute() {
	    
	    if ( isset($_POST['action']) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	        
	        if ( $_POST['action'] == "updatetransportadora" ) {
	            $this->handle_transportadoras_info( $_POST["utilizar_transportadora"], $_POST["order_id"], $_POST["volume"], $_POST["especie"], $_POST["peso_bruto"], $_POST["peso_liquido"], $_POST["key"] );
	            return;
	        }

	    }
	    
	    if ( isset($_GET['nfe_callback']) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	        $this->nfe_callback( $_GET['nfe_callback'], $_GET['order_id'] );
	        return;
	    }
	    
	    if ( isset($_GET['nfe_btn_emitir']) ) {
	        $this->emitir_nfe_btn( $_GET["nfe_btn_emitir"], $_GET["order_id"] );
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
	    
        // Create an object to SQL use
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        
        $validation_secret_key = $this->nfeData->validate_secret_key( "nfe_ajax", $secret_key );
        
        if ( $validation_secret_key["type"] == "invalid" ) {
            echo $validation_secret_key["message"];
            die();
        }
        
        // Retrieve the order id
        $tableName = $resource->getTableName('webmaniabrnfe_orders_transportadora');
        
        $sql = "SELECT order_id FROM " . $tableName . " WHERE order_id='" . $order_id . "'";
        
        $connection->query($sql);
        
        $result = $connection->fetchAll($sql);
        
        // Check if $result is not empty to avoid crash code
        if( empty($result) ) {
            
            // Store the response of the WebmaniaBR REST API
            $sql = "INSERT INTO " . $tableName . " (order_id, utilizar_transportadora, volume, especie, peso_bruto, peso_liquido)
                    VALUES (" . $order_id . ",'" . $utilizar_transportadora . "','" . $volume . "','" . $especie . "', '" . $peso_bruto . "', '" . $peso_liquido . "')";
                    
            $connection->query($sql);
            
            echo "Dados adicionados com sucesso.";
            
        } else {
            
            $sql = "UPDATE " . $tableName . "
                    SET utilizar_transportadora='" . $utilizar_transportadora . "', volume='" . $volume . "', especie='" . $especie . "', peso_bruto='" . $peso_bruto . "', peso_liquido='" . $peso_liquido . "'
                    WHERE order_id ='" . $order_id . "';";
                    
            $connection->query($sql);
            
            echo "Dados atualizados com sucesso.";
            
        }
        
        die();

	}
	
    /* Handle informations received by API callback
    /* @param (int) $order_id
    /* @param (string) $secret_key
    /*
    /* return (string) $message
    */
	public function nfe_callback($secret_key, $order_id) {

        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        
        // Create an object to SQL use
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        
        $validation_secret_key = $this->nfeData->validate_secret_key( "nfe_callback", $secret_key );
        
        if ( $validation_secret_key["type"] == "invalid" ) {
            echo $validation_secret_key["message"];
            die();
        }
        
        // Store the response of the WebmaniaBR REST API
        $uuid = (string) $_POST['uuid'];
        $chave_acesso = (string) $_POST['chave'];
        $n_recibo = ( isset($_POST['recibo']) ? $_POST['recibo'] : "" );
        $n_nfe = (int) $_POST['nfe'];
        $n_serie = (int) $_POST['serie'];
        $url_xml = (string) $_POST['xml'];
        $url_danfe = (string) $_POST['danfe'];
        $status = (string) $_POST['status'];
        $date = date('Y-m-d H:i:s');
        
        // Retrieve the order id
        $tableName = $resource->getTableName('webmaniabrnfe_orders_nfe');
        
        $sql = "SELECT uuid FROM $tableName WHERE uuid='$uuid'";
        
        $connection->query($sql);
        
        $result = $connection->fetchAll($sql);
        
        // Check if $result is not empty to avoid crash code
        if( empty($result) ) {
            
            $sql = "INSERT INTO $tableName
                    (order_id, uuid, chave_acesso, n_recibo, n_nfe, n_serie, url_xml, url_danfe, status, requested_at)
                    VALUES ($order_id,'$uuid','$chave_acesso', '$n_recibo', '$n_nfe', '$n_serie', '$url_xml', '$url_danfe', '$status', '$date')";
                    
            $connection->query($sql);
            
            echo "NF-e criada com sucesso.";
            
        } else {
            
            $sql = "UPDATE $tableName
                    SET chave_acesso='$chave_acesso', n_recibo='$n_recibo', n_nfe='$n_nfe', n_serie='$n_serie', url_xml='$url_xml', url_danfe='$url_danfe', status='$status', requested_at='$date'
                    WHERE uuid ='$uuid';";
                    
            $connection->query($sql);
                    
            echo "NF-e atualizada com sucesso.";

        }
        
        die();
        
	}
	
    /* Trigger actions to emission of NF-e
    /* @param (int) $order_id
    /* @param (string) $secret_key
    /*
    /* return (string) $message
    */
    public function emitir_nfe_btn( $secret_key, $order_id ) {
        
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
        if( $result == "Emitida" && !$nfe_duplicada ) return;
        
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