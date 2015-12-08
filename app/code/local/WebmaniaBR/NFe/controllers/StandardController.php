<?

class WebmaniaBR_Nfe_StandardController extends Mage_Adminhtml_Controller_Action
{
    public function emitirAction(){
        
        $orders = $_POST['order_ids'];
        
        foreach ($orders as $number){
            
            $order = Mage::getModel('sales/order')->load($number);
            
            // Emissão automática de Nota Fiscal 
            $notafiscal = new WebmaniaBR_NFe_Model_Observer;
            $notafiscal->emitirNfe( $order, null, null, true );
            
        }
        
        if (count($orders) == 1) Mage::getSingleton('core/session')->addSuccess("Nota Fiscal emitida com sucesso.");
        else Mage::getSingleton('core/session')->addSuccess("Notas Fiscais emitidas com sucesso.");
        
        session_write_close();
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/');
        $this->_redirectUrl($url);

    }
    
}
