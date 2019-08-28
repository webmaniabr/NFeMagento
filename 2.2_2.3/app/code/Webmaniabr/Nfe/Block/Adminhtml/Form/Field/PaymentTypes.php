<?php

namespace Webmaniabr\Nfe\Block\Adminhtml\Form\Field;

class PaymentTypes extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Shipping Methods Available
     *
     * @var array
     */
    private $_paymentgMethods;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->addOption("nulo",addSlashes('Selecionar'));
        $this->addOption("01", addSlashes('Dinheiro'));
        $this->addOption("02", addSlashes('Cheque'));
        $this->addOption("03", addSlashes('Cartão de Crédito'));
        $this->addOption("04", addSlashes('Cartão de Débito'));
        $this->addOption("05", addSlashes('Crédito Loja'));
        $this->addOption("10", addSlashes('Vale Alimentação'));
        $this->addOption("11", addSlashes('Vale Refeição'));
        $this->addOption("12", addSlashes('Vale Presente'));
        $this->addOption("13", addSlashes('Vale Combustível'));
        $this->addOption("14", addSlashes('Duplicata Mercantil'));
        $this->addOption("15", addSlashes('Boleto Bancário'));
        $this->addOption("90", addSlashes('Sem Pagamento'));
        $this->addOption("99", addSlashes('Outros'));
        
        return parent::_toHtml();
    }
}
