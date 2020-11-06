<p align="center">
  <img src="https://wmbr.s3.amazonaws.com/img/logo_webmaniabr_github.png">
</p>

# Nota Fiscal Eletrônica para Magento

Através do emissor de Nota Fiscal da WebmaniaBR®, você conta com a emissão e arquivamento das suas notas fiscais, cálculo automático de impostos, geração do Danfe para impressão e envio automático de e-mails para os clientes.

- **Módulo compatível com as versões 1.7, 1.8, 1.9, 2.0, 2.1, 2.2 e 2.3**
- **Faça download da última versão do módulo: [Clique aqui](https://github.com/webmaniabr/NFeMagento/releases)**
- Emissor de Nota Fiscal WebmaniaBR®: [Saiba mais](https://webmaniabr.com/nota-fiscal-eletronica/)
- Documentação REST API: [Visualizar](https://webmaniabr.com/docs/rest-api-nfe/)

## Requisitos

- Contrate um dos planos de Nota Fiscal Eletrônica da WebmaniaBR® a partir de R$29,90/mês: [Assine Agora](https://webmaniabr.com/nota-fiscal-eletronica/)
- Instale o módulo grátis do Magento da WebmaniaBR® e configure conforme instruções.

## Instalação

Após realizar o download da última versão ([Clique aqui](https://github.com/webmaniabr/NFeMagento/releases)), descompacte o arquivo zip e envie todos os arquivos na pasta raiz da sua loja virtual. A transferência pode ser realizada através do acesso FTP da sua hospedagem.

## Configuração

Consulte o nosso guia passo a passo para começar a emitir as notas fiscais com apenas um clique na sua Loja Virtual:

- [Configurar credenciais](https://ajuda.webmaniabr.com/hc/pt-br/articles/360013113632-Configurar-credenciais-no-Magento)
- [Configurar Impostos](https://ajuda.webmaniabr.com/hc/pt-br/articles/360013346391-Configurar-impostos-no-Magento)
- [Emitir Nota Fiscal](https://ajuda.webmaniabr.com/hc/pt-br/articles/360013126992-Emiss%C3%A3o-de-NF-e-no-Magento)

## Adaptar página Finalizar Compra

Para a emissão correta da Nota Fiscal Eletrônica é importante ter os seguintes campos obrigatórios na página Finalizar Compra:

- CPF/CNPJ - Registrado no campo vat_id (VAT) do Magento 2.X
- Nome completo / Razão Social
- Endereço - Mapeamento [Saiba mais](https://ajuda.webmaniabr.com/hc/pt-br/articles/360051814852)
- Número - Mapeamento [Saiba mais](https://ajuda.webmaniabr.com/hc/pt-br/articles/360051814852)
- Complemento - Mapeamento [Saiba mais](https://ajuda.webmaniabr.com/hc/pt-br/articles/360051814852)
- Bairro
- Cidade
- Estado
- CEP

Na pasta ```/app/design/``` possui exemplos de como deve ser a página Finalizar Compra e a amostragem dos campos no painel de controle do Magento. Mais informações de configuração você encontra no site Comunidade Magento: http://www.comunidademagento.com.br/portal/adicionando-campos-de-endereco/

**Exemplo de campos na página Finalizar Compra**
<p align="center"><img src="https://webmaniabr.com/wp-content/uploads/2015/12/img_56662bb04a8a0.png"></p>

**Exemplo de amostragem no Back-end**
<p align="center"><img src="https://webmaniabr.com/wp-content/uploads/2015/12/img_56663c7472e3f.png"></p>

## Ativar Emissão Automática (opcional versões 1.x)

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
<img src="https://wmbr.s3.amazonaws.com/img/dashboard_webmaniabr_01.jpg">
</p>

## Suporte

Qualquer dúvida entre em contato na nossa [Central de Ajuda](https://ajuda.webmaniabr.com) ou no e-mail suporte@webmaniabr.com.
