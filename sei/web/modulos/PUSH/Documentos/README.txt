# M�dulo PUSH

## Requisitos:
- SEI 3.0.0 instalado ou atualizado (verificar valor da constante de vers�o do SEI no arquivo /sei/web/SEI.php).
- Antes de executar os scripts de instala��o (itens 4 e 5 abaixo), o usu�rio de acesso aos bancos de dados do SEI e do SIP, constante nos arquivos ConfiguracaoSEI.php e ConfiguracaoSip.php, dever� ter permiss�o de acesso total ao banco de dados, permitindo, por exemplo, cria��o e exclus�o de tabelas.

## Procedimentos para Instala��o:

1. Fazer backup dos bancos de dados do SEI e do SIP.

2. Carregar no servidor os arquivos do m�dulo localizados na pasta "/sei/web/modulos/PUSH" e os scripts de instala��o/atualiza��o "/sei/web/modulos/PUSH/sip_instalar_modulo_pesquisa.php".

3. Editar o arquivo "sei/config/Configura��oSEI.php", tomando o cuidado de usar editor que n�o altere o charset do arquivo, para adicionar a refer�ncia � classe de integra��o do m�dulo e seu caminho relativo dentro da pasta "sei/web/modulos" no array 'Modulos' da chave 'SEI':
	
	'SEI' => array(
			
		'URL' => 'http://[Servidor_PHP]/sei',

		'Producao' => false,

		'RepositorioArquivos' => '/var/sei/arquivos',

		'Modulos' => array('PUSH' => 'PUSH',)
	
		),	
	
4. Executar o script localizado na pasta scripts dentro do m�dulo do PUSH;

5. Rodar o script de banco "/sei/web/modulos/PUSH/scripts/sip_instalar_modulo_push.php" em linha de comando no servidor do SIP, verificando se n�o houve erro em sua execu��o, em que ao final do log dever� ser informado "FIM". Exemplo de comando de execu��o:

	/usr/bin/php -c /etc/php.ini /var/www/html//sei/web/modulos/PUSH/scripts/sip_instalar_modulo_push.php > atualizacao_modulo_push_sip.log