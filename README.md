<p align="center">
  <img src="docs/assets/webmania-logo.png" width="396" alt="Webmania">
</p>

# Nota Fiscal Eletrônica para Magento

Através do emissor de Nota Fiscal da Webmania®, você conta com a emissão e arquivamento das suas notas fiscais, cálculo automático de impostos, geração do Danfe para impressão e envio automático de e-mails para os clientes.

- **Módulo compatível com as versões 1.7, 1.8, 1.9, 2.0, 2.1, 2.2, 2.3 e 2.4.**
- **Faça download da última versão do módulo: [Clique aqui](https://github.com/webmaniabr/NFeMagento/releases)**
- Emissor de Nota Fiscal Webmania® : [Saiba mais](https://webmaniabr.com/nota-fiscal-eletronica/)
- Documentação REST API: [Visualizar](https://webmaniabr.com/docs/rest-api-nfe/)

## Compatibilidade verificada

| Magento | Pasta / pacote | PHP verificado |
| --- | --- | --- |
| 1.7.0.2, 1.8.1.0 e 1.9.4.5 | `1.x` / `Magento-1.x.zip` | 5.6 |
| 2.0.18 | `2.0` / `Magento-2.0.zip` | 5.6 |
| 2.1.18 | `2.1` / `Magento-2.1.zip` | 5.6 |
| 2.2.11 | `2.2` / `Magento-2.2.zip` | 7.1 |
| 2.3.7-p4 | `2.3` / `Magento-2.3.zip` | 7.4 |
| 2.4.8-p5 | `2.4` / `Magento-2.4.zip` | 8.3 |
| 2.4.9 | `2.4` / `Magento-2.4.zip` | 8.5 |

Use somente a pasta ou o pacote correspondente à sua edição do Magento. As distribuições possuem versões internas próprias; a versão do release identifica o conjunto publicado.

A tabela registra os ambientes exercitados. Use o PHP exigido pela versão da sua plataforma e valide as integrações da loja em homologação antes de atualizar.

## Requisitos

- Contrate um dos planos de Nota Fiscal Eletrônica da Webmania®: [Assine Agora](https://webmaniabr.com/nota-fiscal-eletronica/#plans-section)
- Instale o módulo do Magento da Webmania®  e configure conforme instruções.

## Instalação

Após baixar o [release mais recente](https://github.com/webmaniabr/NFeMagento/releases/latest), escolha o ZIP correspondente à versão da sua loja. Descompacte somente esse pacote e envie seu conteúdo para a raiz do Magento, preservando a pasta `app/`.

No Magento 2, execute na raiz da loja:

```sh
php bin/magento module:enable Webmaniabr_Nfe
php bin/magento setup:upgrade
php bin/magento cache:clean
```

Em modo de produção, publique também o conteúdo estático conforme o procedimento de implantação da loja. No Magento 1, limpe o cache e confirme a ativação do módulo no painel administrativo.

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

Você pode gerenciar todas as Notas Fiscais e realizar a impressão do Danfe no painel da Webmania® . Simples e fácil.

<p align="center">
<img src="https://wmbr.s3.amazonaws.com/img/dashboard_webmaniabr_01.jpg">
</p>

## Atualizações desta versão

- Aceita CNPJ numérico e alfanumérico nas edições distribuídas.
- Protege e migra as credenciais para a criptografia nativa do Magento.
- Reforça permissões administrativas, notificações e documentos fiscais.
- Preserva datas e metadados das notas e permite nova tentativa após uma falha comprovadamente anterior ao envio.

## Suporte

Qualquer dúvida acesse o [Painel Webmania® ](https://webmaniabr.com/painel/) para *abrir um chamado* ou conversar em tempo real no *chat*.
