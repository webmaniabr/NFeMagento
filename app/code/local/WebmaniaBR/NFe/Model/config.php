<?

/* Credenciais da Aplicação */
$consumerKey = ''; // Defina o Consumer Key da sua aplicação
$consumerSecret = ''; // Defina o Consumer Secret da sua aplicação
$accessToken = ''; // Defina o Access Token do seu usuário
$accessTokenSecret = ''; // Defina o Access Token Secret do seu usuário

/* Configurar Emissão */
$executar_quando = 'processing'; // Emitir NF-e somente quando alterar para status
$natureza_operacao = 'Venda de produção do estabelecimento'; // Defina a natureza da operação
$ambiente = 1; // Defina o ambiente da emissão no Sefaz. Padrão: Produção.
$ncm = ''; // Defina o Código NCM dos produtos
$cest = ''; // Defina o Código CEST dos produtos
$classe_imposto = ''; // Defina a classe de imposto dos produtos
$origem = 0; // Defina a origem dos produtos