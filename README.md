# Nota Fiscal Eletrônica para Magento

Através do emissor de Nota Fiscal da WebmaniaBR®, você conta com a emissão e arquivamento das suas notas fiscais, cálculo automático de impostos, geração do Danfe para impressão e envio automático de e-mails para os clientes.

- **Módulo compatível com as versões 1.7, 1.8 e 1.9**
- **Faça download da última versão do módulo: [Clique aqui](https://github.com/webmaniabr/NFeMagento/releases)**
- Emissor de NF-e da WebmaniaBR®: [Saiba mais](https://webmaniabr.com/smartsales/nota-fiscal-eletronica/)
- Documentação da REST API: [Visualizar](https://webmaniabr.com/docs/rest-api-nfe/)

## Requisitos

- Contrate um dos planos de Nota Fiscal Eletrônica da WebmaniaBR® a partir de R$29,90/mês: <br>[Avaliação por 30 dias grátis!](https://webmaniabr.com/smartsales/nota-fiscal-eletronica/)
- Instale o módulo grátis do Magento da WebmaniaBR® e configure conforme instruções.

## Instalação

Após realizar o download da última versão ([Clique aqui](https://github.com/webmaniabr/NFeMagento/releases)), descompacte o arquivo zip e envie todos os arquivos na pasta raiz da sua loja virtual, exceto a pasta ```/app/design/``` que deve ser implementada conforme instruções na etapa **Adaptar página Finalizar Compra**. A transferência pode ser realizada através do acesso FTP da sua hospedagem.

## Instruções

- [Guia de emissão de Nota Fiscal para Loja Virtual](https://webmaniabr.com/blog/guia-de-emissao-de-nota-fiscal-para-loja-virtual/)

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

## Suporte

Qualquer dúvida entre em contato na nossa [Central de Ajuda](https://ajuda.webmaniabr.com) ou acesse o [Painel de Controle](https://webmaniabr.com/painel/) para conversar em tempo real no Chat ou Abrir um chamado.
