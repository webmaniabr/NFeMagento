<?php
require_once dirname(__FILE__) . '/../inc/Security.php';

class WebmaniaBR_Nfe_StandardController extends Mage_Adminhtml_Controller_Action
{

  protected function _isAllowed() {
    return Mage::getSingleton('admin/session')->isAllowed('sales/order');
  }

  public function get_referer_url() {

    $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');

    if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
      $refererUrl = $url;
    }
    if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
      $refererUrl = Mage::helper('core')->urlDecode($url);
    }
    if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
      $refererUrl = Mage::helper('core')->urlDecode($url);
    }

    if (!$this->_isUrlInternal($refererUrl)) {
      $refererUrl = Mage::app()->getStore()->getBaseUrl();
    }

    return $refererUrl;

  }

  public function updateTransporteAction(){
    if (!$this->getRequest()->isPost() || !WebmaniaNFeSecurity::equals(Mage::getSingleton('core/session')->getFormKey(), $this->getRequest()->getParam('form_key'))) {
      $this->getResponse()->setHttpResponseCode(403); return;
    }


    $order_id = (int) $this->getRequest()->getParam('order_id');

    $order = Mage::getModel('sales/order')->load($order_id);

    $data = $order->getData('nfe_transporte_info');


    if(is_null($data)){

      $attribute  = array(
        'type' => 'text',
        'input' => 'text',
        'label' => 'Informações de Transporte',
        'global' => 0,
        'visible' => 0,
        'required' => 0,
        'user_defined' => 1,
        'visible_on_front' => 0,
      );

      $setup = new Mage_Sales_Model_Resource_Setup('core_setup');
      $setup->addAttribute('order', 'nfe_transporte_info', $attribute);
      $setup->endSetup();

    }

    $transporte_info = array(
      'volume'           => $this->getRequest()->getPost('nfe_transporte_volume'),
      'especie'          => $this->getRequest()->getPost('nfe_transporte_especie'),
      'peso_bruto'       => $this->getRequest()->getPost('nfe_transporte_peso_bruto'),
      'peso_liquido'     => $this->getRequest()->getPost('nfe_transporte_peso_liquido'),
      'modalidade_frete' => $this->getRequest()->getPost('nfe_transporte_modalidade_frete'),
    );
    // Only plain values are stored (they are printed back in the order view form)
    foreach ($transporte_info as $field => $value) {
      $transporte_info[$field] = is_scalar($value) ? (string) $value : '';
    }

    $json_info = json_encode($transporte_info);
    $order->setData('nfe_transporte_info', $json_info);
    $order->save();

    Mage::getSingleton('core/session')->addSuccess("Informações salvas com sucesso.");

    $referer_url = $this->get_referer_url();
    $this->_redirectUrl($referer_url);

  }

  public function updateAction() {

    $chave_acesso = $this->getRequest()->getParam('chave_acesso');
    $order_id = (int) $this->getRequest()->getParam('order_id');

    $notafiscal = new WebmaniaBR_NFe_Model_Observer;
    $response = $notafiscal->updateNotaFiscal($chave_acesso);
    if (isset($response->error)){

      Mage::getSingleton('core/session')->addError("Erro ao atualizar nota: ".$response->error);

    }else{

      $new_status = $response->status;
      $order = Mage::getModel('sales/order')->load($order_id);

      $nfe_data = \WebmaniaNFeSecurity::invoices($order->getData('all_nfe'));

      foreach($nfe_data as &$order_nfe){
        if($order_nfe['chave_acesso'] == $chave_acesso){
          $order_nfe['status'] = $new_status;
        }
      }

      $nfe_data_str = base64_encode(serialize($nfe_data));
      $order->setData('all_nfe', $nfe_data_str);
      $order->save();
      Mage::getSingleton('core/session')->addSuccess("Nota Fiscal atualizada com sucesso.");

    }


    $referer_url = $this->get_referer_url();
    $this->_redirectUrl($referer_url);
  }

  public function emitirAction(){
    if (!$this->getRequest()->isPost() || !WebmaniaNFeSecurity::equals(Mage::getSingleton('core/session')->getFormKey(), $this->getRequest()->getParam('form_key'))) {
      $this->getResponse()->setHttpResponseCode(403); return;
    }



    $orders = isset($_POST['order_ids']) && is_array($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : array();

    foreach ($orders as $number){

      $order = Mage::getModel('sales/order')->load($number);

      // Emissão automática de Nota Fiscal
      $notafiscal = new WebmaniaBR_NFe_Model_Observer;
      $response = $notafiscal->emitirNfe( $order, null, null, true );

      $orderno = (int) $order->getIncrementId();
      if (isset($response->error)){
        Mage::getSingleton('core/session')->addError("Nota Fiscal #".$orderno.': '.$response->error);
      }elseif($response->status == 'reprovado'){
        if (isset($response->log)){
          if (!empty($response->log->xMotivo)){
            if(isset($response->log->aProt[0]->xMotivo)){
							$error = $response->log->aProt[0]->xMotivo;
						}else{
							$error = $response->log->xMotivo;
						}

            // Admin session messages are rendered as HTML in Magento 1
            Mage::getSingleton('core/session')->addError("- ".Mage::helper('core')->escapeHtml($error));
          }
        }
      }else {

        $setup = new Mage_Sales_Model_Resource_Setup('core_setup');

        $attribute  = array(
          'type' => 'text',
          'input' => 'text',
          'label' => 'NFe emitidas',
          'global' => 0,
          'visible' => 1,
          'required' => 0,
          'user_defined' => 1,
          'visible_on_front' => 0,
        );

        $setup->addAttribute('order', 'all_nfe', $attribute);
        $setup->endSetup();

        $existing_nfe = \WebmaniaNFeSecurity::invoices($order->getData('all_nfe'));
        if(!$existing_nfe) $existing_nfe = array();
        $status = (string) $response->status;

        $nfe_info = array(
          'status'       => $status,
          'chave_acesso' => $response->chave,
          'n_recibo'     => (int) $response->recibo,
          'n_nfe'        => (int) $response->nfe,
          'n_serie'      => (int) $response->serie,
          'url_xml'      => (string) $response->xml,
          'url_danfe'    => (string) $response->danfe,
          'data'         => date('d/m/Y'),
        );

        $history = Mage::getModel('sales/order_status_history')
            ->setStatus($order->getStatus())
            ->setComment('Chave de Acesso: ' . $response->chave)
            ->setEntityName(Mage_Sales_Model_Order::HISTORY_ENTITY_NAME)
            ->setIsCustomerNotified(false)
            ->setCreatedAt(date('Y-m-d H:i:s', time() - 60*60*24));

        $order->addStatusHistory($history);
        $order->save();

        $existing_nfe[] = $nfe_info;

        $nfe_info_str = base64_encode(serialize($existing_nfe));
        $order->setData('all_nfe', $nfe_info_str);
        $order->save();
        Mage::getSingleton('core/session')->addSuccess("Nota Fiscal #".$orderno.': Emitida com sucesso. (Chave de acesso: '.Mage::helper('core')->escapeHtml((string) $response->chave).' | Status: '.Mage::helper('core')->escapeHtml($status).')');
      }

    }

    session_write_close();
    $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/');
    $this->_redirectUrl($url);

  }

  public function imprimirDanfeAction() {

    $orders = isset($_POST['order_ids']) && is_array($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : array();

    $notafiscal = new WebmaniaBR_NFe_Model_Observer;
    $notafiscal->imprimirDanfe($orders, 'danfe');

  }

  public function imprimirDanfeSimplesAction() {

    $orders = isset($_POST['order_ids']) && is_array($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : array();

    $notafiscal = new WebmaniaBR_NFe_Model_Observer;
    $notafiscal->imprimirDanfe($orders, 'danfe_simples');

  }

  public function imprimirDanfeEtiquetaAction() {

    $orders = isset($_POST['order_ids']) && is_array($_POST['order_ids']) ? array_map('intval', $_POST['order_ids']) : array();

    $notafiscal = new WebmaniaBR_NFe_Model_Observer;
    $notafiscal->imprimirDanfe($orders, 'danfe_etiqueta');

  }

}
