# Nota Fiscal Eletrônica para Magento

Através do emissor de Nota Fiscal da WebmaniaBR®, você conta com a emissão e arquivamento das suas notas fiscais, cálculo automático de impostos, geração do Danfe para impressão e envio automático de e-mails para os clientes. Instale o módulo grátis do Magento e contrate um dos planos de Nota Fiscal Eletrônica da WebmaniaBR® a partir de R$29,90/mês: [Assine agora mesmo](https://webmaniabr.com/smartsales/nota-fiscal-eletronica/).

- **Módulo compatível com as versões 1.7, 1.8, 1.9, 2.0 e 2.2**
- Emissor de NF-e da WebmaniaBR®: [Saiba mais](https://webmaniabr.com/smartsales/nota-fiscal-eletronica/)
- Documentação da REST API: [Visualizar](https://webmaniabr.com/docs/rest-api-nfe/)

## Requisitos

- Contrate um dos planos de Nota Fiscal Eletrônica da WebmaniaBR® a partir de R$29,90/mês: [Assine agora mesmo](https://webmaniabr.com/smartsales/nota-fiscal-eletronica/).
- Instale o módulo grátis do Magento da WebmaniaBR® e configure conforme instruções.

## Instalação do Módulo

Copie e cole as seguintes pastas e arquivos para a sua instalação do Magento:

```
/app/code/local/WebmaniaBR/
/app/etc/modules/WebmaniaBR_NFE.xml
```

Após mover os arquivos para as suas respectivas pastas, configure o arquivo ```/app/code/local/WebmaniaBR/NFe/Model/config.php``` com as informações da sua aplicação. Segue abaixo exemplo:

```php
/* Credenciais da Aplicação */
$consumerKey = 'SEUCONSUMERKEY';
$consumerSecret = 'SEUCONSUMERSECRET';
$accessToken = 'SEUACCESSTOKEN';
$accessTokenSecret = 'SEUTOKENSECRET';

/* Configurar Emissão */
$executar_quando = 'processing';
$natureza_operacao = 'Venda de produção do estabelecimento';
$ambiente = 1; 
$ncm = '6109.10.00'; 
$cest = '28.038.00';
$classe_imposto = 'REF1637'; 
$origem = 0;
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

## Ativar Emissão Automática (opcional)

Para ativar a emissão automática é necessário chamar a função no momento em que o pedido alterar o status. Para isso localize a função **_setState()** no arquivo ```/app/code/core/Mage/Sales/Model/Order.php``` e adicione a linha **Emissão automática de Nota Fiscal** no início da função:

```php
protected function _setState($state, $status = false, $comment = '',
    $isCustomerNotified = null, $shouldProtectState = false)
{

    /* Emissão automática de Nota Fiscal */
    $notafiscal = new WebmaniaBR_NFe_Model_Observer;
    $notafiscal->emitirNfe( $this, $state, $status );

    ...
    ...
    
}
```

## Controle das Notas Fiscais

Você pode gerenciar todas as Notas Fiscais e realizar a impressão do Danfe no painel da WebmaniaBR®. Simples e fácil.

<p align="center">
<img src="https://webmaniabr.com/wp-content/themes/wmbr/img/nf07.jpg">
</p>

## Suporte

Qualquer dúvida entre em contato na nossa [Central de Atendimento](https://webmaniabr.com/atendimento/) ou no e-mail suporte@webmaniabr.com.
