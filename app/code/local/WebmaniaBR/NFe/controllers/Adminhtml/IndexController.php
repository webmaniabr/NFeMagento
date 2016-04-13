<?php

class WebmaniaBR_Nfe_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        
        $orders = $_POST['order_ids'];
        
        if (!is_array($orders)){
            
            if ($orders) $orders  = explode(',', $orders);
            
        }
        
        if ($orders && count($orders) > 0){
            
            foreach ($orders as $number){
            
                $order = Mage::getModel('sales/order')->load($number);

                // Emissão automática de Nota Fiscal 
                $notafiscal = new WebmaniaBR_NFe_Model_Observer;
                $response = $notafiscal->emitirNfe( $order, null, null, true );
                $orderno = (int) $order->getIncrementId();
                if ($response->error) { 
                    Mage::getSingleton('core/session')->addError("Nota Fiscal #".$orderno.': '.$response->error); 
                    if ($response->log){
                        foreach ($response->log as $erros){
                            foreach ($erros as $erro) {
                                Mage::getSingleton('core/session')->addError("- ".$erro);
                            }
                        }
                    }
                } else {
                    Mage::getSingleton('core/session')->addSuccess("Nota Fiscal #".$orderno.': Emitida com sucesso.');
                }

            }
        
        }
        
        session_write_close();
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/');
        $this->_redirectUrl($url);

    }
    
}
