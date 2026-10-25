<?php
namespace Webmaniabr\Nfe\Model\Adminhtml\System\Config\Backend;

require_once __DIR__ . '/../../../../../Helper/Security.php';

/** Credentials are encrypted at rest and never rendered back into the form. */
class Credential extends \Magento\Framework\App\Config\Value
{
    protected function _afterLoad()
    {
        $this->setValue('');
        return parent::_afterLoad();
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        $this->_dataSaveAllowed = is_string($value) && $value !== '' && !preg_match('/^\*+$/D', $value);
        if ($this->_dataSaveAllowed && preg_match('/[\x00-\x20\x7f]/', $value)) {
            throw new \InvalidArgumentException('Credencial de acesso inválida.');
        }
        if ($this->_dataSaveAllowed) {
            $this->setValue(\WebmaniaNFeSecurity::encryptCredential($value));
        }
        return $this;
    }
}
