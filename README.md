# Módulo PUSH

## Requisitos:
- SEI 3.0.0 instalado ou atualizado (verificar valor da constante de versão do SEI no arquivo /sei/web/SEI.php).
- Antes de executar os scripts de instalação (itens 4 e 5 abaixo), o usuário de acesso aos bancos de dados do SEI e do SIP, constante nos arquivos ConfiguracaoSEI.php e ConfiguracaoSip.php, deverá ter permissão de acesso total ao banco de dados, permitindo, por exemplo, criação e exclusão de tabelas.

## Procedimentos para Instalação:

1. Fazer backup dos bancos de dados do SEI e do SIP.

2.Carregar no servidor os arquivos do módulo nas pastas correspondentes nos servidores do SEI e do SIP.

3. Editar o arquivo "sei/config/ConfiguraçãoSEI.php", tomando o cuidado de usar editor que não altere o charset do arquivo, para adicionar a referência à classe de integração do módulo e seu caminho relativo dentro da pasta "sei/web/modulos" no array 'Modulos' da chave 'SEI':
	
	'SEI' => array(
 	'URL' => 'http://[Servidor_PHP]/sei',
 	'Producao' => false,
	'Modulos' => array('PUSH' => 'PUSH',)	
		),	
	
4. Antes de seguir para os próximos passos, é importante conferir se o Módulo foi corretamente declarado no arquivo "/sei/config/ConfiguracaoSEI.php". Acesse o menu **Infra > Módulos** e confira se consta a linha correspondente ao Módulo, pois, realizando os passos anteriores da forma correta, independente da execução do script de banco, o Módulo já deve ser reconhecido na tela aberta pelo menu indicado.
   
5. Rodar o script de banco "/sip/scripts/sip_instalar_modulo_push.php" em linha de comando no servidor do SIP, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

   	/usr/bin/php -c /etc/php.ini /opt/sip/scripts/sip_instalar_modulo_push.php 2>&1 > instalacao_modulo_push_sip.log

6. Antes de rodar o script de banco do SEI, para evitar erros de chave duplicada, importante executar o script do core do SEI de atualização dos sequences das tabelas:

   	/usr/bin/php -c /etc/php.ini /opt/sei/scripts/atualizar_sequencias.php 2>&1 > atualizar_sequencias_sei.log

7. Rodar o script de banco "/sei/scripts/sei_instalar_modulo_push.php" em linha de comando no servidor do SEI, verificando se não houve erro em sua execução, em que ao final do log deverá ser informado "FIM". Exemplo de comando de execução:

   	/usr/bin/php -c /etc/php.ini /opt/sei/scripts/sei_instalar_modulo_push.php 2>&1 > atualizacao_modulo_push_sei.log 

8. **IMPORTANTE**: Na execução dos dois scripts de banco acima, ao final deve constar o termo "FIM", o "TEMPO TOTAL DE EXECUÇÃO" e a informação de que a instalação/atualização foi realizada com sucesso na base de dados correspondente (SEM ERROS). Do contrário, o script não foi executado até o final e algum dado não foi inserido/atualizado no respectivo banco de dados, devendo recuperar o backup do banco e repetir o procedimento.
	- Constando ao final da execução do script as informações indicadas, pode logar no SEI e SIP e verificar no menu **Infra > Parâmetros** dos dois sistemas se consta o parâmetro "VERSAO_MODULO_PUSH" com o valor da última versão do módulo.
	
9. Em caso de erro durante a execução do script, verificar (lendo as mensagens de erro e no menu Infra > Log do SEI e do SIP) se a causa é algum problema na infraestrutura local ou ajustes indevidos na estrutura de banco do core do sistema. Neste caso, após a correção, deve recuperar o backup do banco pertinente e repetir o procedimento, especialmente a execução dos scripts de banco indicados acima.
	- Caso não seja possível identificar a causa, entrar em contato com: <a href="mailto:cgti@cade.gov.br">cgti@cade.gov.br</a>

	
10. Após a execução com sucesso, com um usuário com permissão de Administrador no SEI, seguir os passos dispostos no tópico "Orientações Negociais" mais abaixo.

## Orientações Negociais:

1. Acesso ao sistema
O Acesso às funcionalidades do módulo PUSH é feito através do login do Sistema Eletrônico de Informações (SEI).

2. Objetivos
O módulo PUSH tem como objetivo possibilitar a notificação de alterações em um processo para o usuário cadastrado.

3. Cadastros
Criar Notificação do PUSH:
  1.	Acesse o item de menu Módulo PUSH/Notificações
  2.	Clique no botão Novo
  3.	Preencha o campo Tipo de procedimento com o tipo de processo desejado
  4.	Preencha o campo Template de e-mail com o template de e-mail do PUSH
  5.	Selecione os eventos que dispararão essa notificação
  6.	Clique no botão salvar;

Criar Bloqueio de Notificação do PUSH:
  1.	Acesse o item de menu Módulo PUSH/Bloqueio de Notificações
  2.	Clique no botão Novo
  3.	Preencha o campo Número do processo com o número do processo desejado.
  4.	Clique no botão salvar;

4.	Consultas
Consultar notificações do PUSH
  1.	Acesse o item de menu Módulo PUSH/Notificações
  2.	Verifique a lista exibida na tela que abrir

Consultar bloqueios de notificação do PUSH
  1.	Acesse o item de menu Módulo PUSH/Bloqueio de Notificações
  2.	Na lista de bloqueios de notificações, após localizar o bloqueio de notificação que deseja consultar, clique no botão de consultar, simbolizado por um ícone de lupa;

5. Consultar assinaturas do PUSH
1.	Acesse o item de menu Módulo PUSH/Assinaturas

## Pesquisa Pública:
- Para habilitar o botão "inclur no PUSH" na página de exibição do processo na pesquisa pública, é necesário editar o arquivo ../modulos/pesquisa/md_pesq_processo_exibir.php e incluir o código abaixo antes da instrução "PaginaSEIExterna::getInstance()->montarBarraComandosSuperior($arrComandos);"

```php
// Inicio modificação Push por TLA
//    Trecho de código que verifica se o Push está instalado e insere o formulário de assinatura do Push, caso o processo em questão possa ser assinado.
if (ConfiguracaoSEI::getInstance()->getValor('SEI','Modulos')['PUSHIntegracao'] != null){
  if(PushFormINT::sinFormularioPushMostrarBotao($_GET['id_procedimento']))
    $arrComandos[] = '<button type="button" id="btnPush" value="Push" onclick="location.href=\'' .  ConfiguracaoSEI::getInstance()->getValor('SEI','URL') . '/modulos/' . ConfiguracaoSEI::getInstance()->getValor('SEI','Modulos')['PUSHIntegracao'] . '/push_assinar_processo.php?idProcedimento=' . $_GET['id_procedimento'] . '\'" class="infraButton">Incluir no PUSH</button>';
}
// Fim modificação Push por TLA
```
