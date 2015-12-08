<p align="center">
<img src="https://webmaniabr.com/wp-content/uploads/2015/12/logomarca_alta1.png">
</p>

# NFeMagento - Nota Fiscal para Magento

Emissão automática de Nota Fiscal Eletrônica para Magento através da REST API de Nota Fiscal Eletrônica da WebmaniaBR®. Emita as suas Nota Fiscais sempre que receber um pagamento.

Documentação: https://webmaniabr.com/docs/rest-api-nfe/ 

## Requisitos

- Escolha um plano que se adeque as necessidades da sua empresa. Para saber mais: http://webmania.me/1kDM9hi
- Obtenha as credenciais de acesso da sua aplicação.
- Instale o módulo da WebmaniaBR® e configure conforme instruções.

## Instalação do Módulo

Copie e cole as seguintes pastas e arquivos para a sua instalação do Magento:

```
/app/code/local/WebmaniaBR/
/app/code/etc/WebmaniaBR_NFE.xml
```

Após mover os arquivos para as suas respectivas pastas, configure o arquivo ```/app/code/local/WebmaniaBR/NFe/Model/config.php``` com as informações da sua aplicação. Segue abaixo exemplo:

```
/* Credenciais da Aplicação */
$consumerKey = 'libDIsUqeSbZ4qY3i7fRwjeQBBwyjp44';
$consumerSecret = 'S6GtVsdNwg5IWGbQJSfSEHx6BSieB8J8eO318RbLvq4ye25L';
$accessToken = 'XX-zykM818Nc6RmHgDyazxhaCmFHehR77mvQvmYbO6GCg43xPCO';
$accessTokenSecret = 'KnRBlvwE2rXObZPk8VORPldqcqufHk1BXIkyNs7DlDrzUHIP';

/* Configurar Emissão */
$executar_quando = 'processing';
$natureza_operacao = 'Venda de produção do estabelecimento';
$ambiente = 1; 
$ncm = '6109.10.00'; 
$classe_imposto = 'REF1637'; 
$origem = 0;
```

## Ativar Emissão Automática

Para ativar a emissão automática é necessário chamar a função no momento em que o pedido alterar o status. Para isso localize a função **_setState()** no arquivo ```/app/code/core/Mage/Sales/Model/Order.php``` e altere para o seguinte código:

```
protected function _setState($state, $status = false, $comment = '',
    $isCustomerNotified = null, $shouldProtectState = false)
{

    /* Emissão automática de Nota Fiscal */
    $notafiscal = new WebmaniaBR_NFe_Model_Observer;
    $notafiscal->emitirNfe( $this, $state, $status );

    // attempt to set the specified state
    if ($shouldProtectState) {
        if ($this->isStateProtected($state)) {
            Mage::throwException(
                Mage::helper('sales')->__('The Order State "%s" must not be set manually.', $state)
            );
        }
    }
    $this->setData('state', $state);

    // add status history
    if ($status) {
        if ($status === true) {
            $status = $this->getConfig()->getStateDefaultStatus($state);
        }
        $this->setStatus($status);
        $history = $this->addStatusHistoryComment($comment, false); // no sense to set $status again
        $history->setIsCustomerNotified($isCustomerNotified); // for backwards compatibility
    }


    return $this;
}
```

## Emissão de Nota Fiscal Manual

Caso deseje, também é possível realizar a emissão da Nota Fiscal Eletrônica de forma manual no painel de controle Magento. Após instalado o módulo, automaticamente será ativado a opção **Emitir NF-e** nas ações gerais dos pedidos.

<p align="center">
<img src="https://webmaniabr.com/wp-content/uploads/2015/12/img_5666427f8ea34.png">
</p>

## Adaptar página Finalizar Compra

Para a emissão correta da Nota Fiscal Eletrônica é importante ter os seguintes campos obrigatórios na página Finalizar Compra:

- CPF
- Nome completo
- Endereço
- Número
- Complemento (caso houver)
- Bairro
- Cidade
- Estado
- CEP (utilizado como padrão o campo Tax VAT)

Na pasta ```/app/design/``` possui exemplos de como deve ser a página Finalizar Compra e a amostragem dos campos no painel de controle do Magento. Mais informações de configuração você encontra no site Comunidade Magento: http://www.comunidademagento.com.br/portal/adicionando-campos-de-endereco/

**Exemplo de campos na página Finalizar Compra**
<p><img src="https://webmaniabr.com/wp-content/uploads/2015/12/img_56662bb04a8a0.png"></p>

**Exemplo de amostragem no Back-end**
<p><img src="https://webmaniabr.com/wp-content/uploads/2015/12/img_56663c7472e3f.png"></p>

## Controle das Notas Fiscais

Você pode gerenciar todas as Notas Fiscais e realizar a impressão do Danfe no painel da WebmaniaBR®. Simples e fácil.

<p align="center">
<img src="https://webmaniabr.com/wp-content/themes/wmbr/img/nf01.jpg">
</p>

## Suporte

Qualquer dúvida estamos à disposição e abertos para melhorias e sugestões, em breve teremos um fórum para discussões. Qualquer dúvida entre em contato na nossa Central de Atendimento: https://webmaniabr.com/atendimento/.
