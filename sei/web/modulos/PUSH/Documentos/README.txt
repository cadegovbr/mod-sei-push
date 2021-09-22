# Módulo PUSH

## Requisitos:
- SEI 3.0.0 instalado ou atualizado (verificar valor da constante de versão do SEI no arquivo /sei/web/SEI.php).
- Antes de executar os scripts de instalação (itens 4 e 5 abaixo), o usuário de acesso aos bancos de dados do SEI e do SIP, constante nos arquivos ConfiguracaoSEI.php e ConfiguracaoSip.php, deverá ter permissão de acesso total ao banco de dados, permitindo, por exemplo, criação e exclusão de tabelas.

## Procedimentos para Instalação:

1. Fazer backup dos bancos de dados do SEI e do SIP.

2. Carregar no servidor os arquivos do módulo localizados na pasta "/sei/web/modulos/PUSH" e os scripts de instalação/atualização "/sei/web/modulos/PUSH/sip_instalar_modulo_pesquisa.php".

3. Editar o arquivo "sei/config/ConfiguraçãoSEI.php", tomando o cuidado de usar editor que não altere o charset do arquivo, para adicionar a referência à classe de integração do módulo e seu caminho relativo dentro da pasta "sei/web/modulos" no array 'Modulos' da chave 'SEI':
	
	'SEI' => array(
			
		'URL' => 'http://[Servidor_PHP]/sei',

		'Producao' => false,

		'RepositorioArquivos' => '/var/sei/arquivos',

		'Modulos' => array('PUSH' => 'PUSH',)
	
		),	
	
4. Executar o script localizado na pasta scripts dentro do módulo do PUSH;

5. Rodar o script de banco "/sei/web/modulos/PUSH/scripts/sip_instalar_modulo_push.php" em linha de comando no servidor do SIP, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

	/usr/bin/php -c /etc/php.ini /var/www/html//sei/web/modulos/PUSH/scripts/sip_instalar_modulo_push.php > atualizacao_modulo_push_sip.log