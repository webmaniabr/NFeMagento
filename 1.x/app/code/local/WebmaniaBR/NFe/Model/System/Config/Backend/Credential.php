<?php
require_once __DIR__ . '/../../../../inc/Security.php';
class WebmaniaBR_NFe_Model_System_Config_Backend_Credential extends Mage_Core_Model_Config_Data
{
    protected function _afterLoad()
    {
        $this->setValue('');
        return parent::_afterLoad();
    }

    protected function _beforeSave()
    {
        $value = $this->getValue();
        $this->_dataSaveAllowed = is_string($value) && $value !== '' && !preg_match('/^\*+$/D', $value);
        if ($this->_dataSaveAllowed && preg_match('/[\x00-\x20\x7f]/', $value)) {
            throw new InvalidArgumentException('Credencial de acesso inválida.');
        }
        if ($this->_dataSaveAllowed) {
            $this->setValue(WebmaniaNFeSecurity::encryptCredential($value));
        }
        return $this;
    }
}
