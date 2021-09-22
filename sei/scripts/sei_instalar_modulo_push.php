<?
require_once dirname(__FILE__) . '/../web/SEI.php';

class InstaladorModuloSEIPushRN extends InfraRN
{

    private $numSeg = 0;

    private $versaoAtualDesteModulo = '0.0.1';

    private $nomeParametroModulo = 'VERSAO_MODULO_PUSH';

    public function __construct()
    {
        parent::__construct();
        $this->inicializar(' SEI - INICIALIZAR ');
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    private function inicializar($strTitulo)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        
        try {
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
        } catch (Exception $e) {}
        
        ob_implicit_flush();
        
        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();
        
        $this->numSeg = InfraUtil::verificarTempoProcessamento();
        
        $this->logar($strTitulo);
    }

    private function logar($strMsg)
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    private function finalizar($strMsg = null, $bolErro)
    {
        if (! $bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
            $this->logar('TEMPO TOTAL DE EXECU��O: ' . $this->numSeg . ' s');
        } else {
            $strMsg = 'ERRO: ' . $strMsg;
        }
        
        if ($strMsg != null) {
            $this->logar($strMsg);
        }
        
        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die();
    }

    protected function instalarv001($bancoSEI)
    {
        $queries = $this->getQueries();
        $createTablesQueries = $queries['Create'];
        $insertQueries = $queries['Insert'];
        
        $this->logar('CRIANDO TABELAS:');
        foreach($createTablesQueries as $createTableQuery)
            $bancoSEI->executarSql($createTableQuery);
        
        $this->logar('EXECUTANDO INSERTS:');
        foreach($insertQueries as $insertQuery)
            $bancoSEI->executarSql($insertQuery);
    }
    
    protected function atualizarVersaoConectado()
    {
        try {
            $bancoSEI = BancoSEI::getInstance();
            
            $this->verificaBDsSuportados($bancoSEI);
            
            $this->checaPermissoesBaseDados($bancoSEI);
            
            if ($this->versaoModuloNaoExiste($bancoSEI)) {
                $this->logar('iniciando a cria��o das tabelas...');
                
                $this->instalarv001($bancoSEI);
                
                $this->adicionarParametroControladorVersao($bancoSEI);
                
                $this->logar('ATUALIZA��ES DO M�DULO PUSH NA BASE DO SEI REALIZADAS COM SUCESSO');
            } else {
                $this->logar('SEI - M�DULO PUSH v0.0.1 J� INSTALADO');
                $this->finalizar('FIM', true);
            }
            
            $this->finalizar('FIM', false);
        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            throw new InfraException('Erro atualizando vers�o.', $e);
        }
    }

    protected function verificaBDsSuportados($bancoSEI)
    {
        if (! $this->bdValido($bancoSEI))
            $this->finalizar('BANCO DE DADOS NAO SUPORTADO: ' . get_parent_class($bancoSEI), true);
    }

    protected function bdValido($bancoSEI)
    {
        return $this->ehMySQL($bancoSEI) || $this->ehSQLServer($bancoSEI) || $this->ehOracle($bancoSEI);
    }

    protected function ehMySQL($bancoSEI)
    {
        return $bancoSEI instanceof InfraMySql;
    }

    protected function ehSQLServer($bancoSEI)
    {
        return $bancoSEI instanceof InfraSqlServer;
    }

    protected function ehOracle($bancoSEI)
    {
        return $bancoSEI instanceof InfraOracle;
    }

    protected function checaPermissoesBaseDados($bancoSEI)
    {
        $objInfraMetaBD = new InfraMetaBD($bancoSEI);
        
        if ($this->tabelaTestesNaoExiste($objInfraMetaBD))
            $this->criarTabelaTestes($bancoSEI, $objInfraMetaBD);
        
        $this->deletarTabelaTestes($bancoSEI);
    }

    protected function tabelaTestesNaoExiste($objInfraMetaBD)
    {
        return count($objInfraMetaBD->obterTabelas('sei_teste')) == 0;
    }

    protected function criarTabelaTestes($bancoSEI, $objInfraMetaBD)
    {
        $bancoSEI->executarSql('CREATE TABLE sei_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
    }

    protected function deletarTabelaTestes($bancoSEI)
    {
        $bancoSEI->executarSql('DROP TABLE sei_teste');
    }

    protected function versaoModuloNaoExiste($bancoSEI)
    {
        $objInfraParametro = new InfraParametro($bancoSEI);
        $strVersaoModuloLitigioso = $objInfraParametro->getValor($this->nomeParametroModulo, false);
        return InfraString::isBolVazia($strVersaoModuloLitigioso);
    }

    protected function adicionarParametroControladorVersao($bancoSEI)
    {
        $bancoSEI->executarSql('insert into infra_parametro (valor, nome ) VALUES( \'' . $this->versaoAtualDesteModulo . '\',  \'' . $this->nomeParametroModulo . '\' )');
    }
    
    protected function getQueries()
    {
        return array(
            'Create' => array(
                'create table md_push (id_md_push int not null,
                id_procedimento bigint not null,
                nome varchar(100) not null,
                email varchar(100) not null,
                chave varchar(100) not null,
                dth_ultimo_envio_email datetime null,
                constraint pk_md_push primary key (id_md_push),
                constraint fk_md_push_procedimento foreign key (id_procedimento) references procedimento(id_procedimento));',
                
                'create table seq_md_push (id int not null auto_increment,
                campo char(1) default null,
                constraint pk_seq_md_push primary key (id));',
                
                'CREATE TABLE md_push_notificacao (
                      id_notificacao int(11) NOT NULL,
                      id_tipo_procedimento int(11) NOT NULL UNIQUE,
                      id_email_sistema int(11) NOT NULL,
                      sin_notifica_restrito char(1) NOT NULL,
                      PRIMARY KEY (id_notificacao),
                      KEY fk_md_push_not_id_tipo_procedimento (id_tipo_procedimento),
                      KEY fk_md_push_not_id_email_sistema (id_email_sistema),
                      CONSTRAINT fk_md_push_not_id_tipo_procedimento FOREIGN KEY (id_tipo_procedimento) REFERENCES tipo_procedimento (id_tipo_procedimento),
                      CONSTRAINT fk_md_push_not_id_email_sistema FOREIGN KEY (id_email_sistema) REFERENCES email_sistema (id_email_sistema)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;',
                'CREATE TABLE md_push_rel_notificacao_tarefa (
                      id_rel_notificacao_tarefa int(11) NOT NULL,
                      id_notificacao int(11) NOT NULL,
                      id_tarefa int(11) NOT NULL,
                      PRIMARY KEY (id_rel_notificacao_tarefa),
                      KEY fk_md_push_rel_not_tarefa_id_notificacao (id_notificacao),
                      KEY fk_md_push_rel_not_tarefa_id_tarefa (id_tarefa),
                      CONSTRAINT fk_md_push_rel_not_tarefa_id_notificacao FOREIGN KEY (id_notificacao) REFERENCES md_push_notificacao (id_notificacao),
                      CONSTRAINT fk_md_push_rel_not_tarefa_id_tarefa FOREIGN KEY (id_tarefa) REFERENCES tarefa (id_tarefa)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;',
                'CREATE TABLE seq_md_push_rel_notificacao_tarefa (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      campo char(1) DEFAULT NULL,
                      PRIMARY KEY (id)
                    ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;',
                'create table seq_md_push_notificacao (id int not null auto_increment,
                  campo char(1) default null,
                  constraint pk_seq_md_push_notificacao primary key (id));',
                
                'create table md_push_bloqueio_notificacao (id_md_push_bloqueio_notificacao int not null,
                id_procedimento bigint not null,
                constraint pk_md_push_bloqueio_notificacao primary key (id_md_push_bloqueio_notificacao),
                constraint fk_md_push_bloqueio_procedimento foreign key (id_procedimento) references procedimento(id_procedimento));',
                
                'create table seq_md_push_bloqueio_notificacao (id int not null auto_increment,
                campo char(1) default null,
                constraint pk_seq_md_push_bloqueio_notificacao primary key (id));',
                                
                'create table md_push_config (id_md_push_config int not null,
                  nome varchar(100) not null,
                  valor varchar(255) not null,
                  constraint pk_md_push_config primary key (id_md_push_config));',
                
                'create table seq_md_push_config (id int not null auto_increment,
                  campo char(1) default null,
                  constraint pk_seq_md_push_config primary key (id));'
            ),
            
            'Insert' => array(
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c) ,'PUSH - Andamento do processo registrado','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Movimenta��o processual Processo n� @processo@','      :: Este � um e-mail autom�tico ::\r\n \r\n Prezado(a) @nome_contato@,\r\n \r\n Este e-mail informa que o Processo n� @processo@ recebeu o(s) seguinte(s) andamento(s):\r\n \r\n @lista_andamentos@\r\n \r\n Lista de Interessados do processo\r\n \r\n @lista_interessados@\r\n \r\n  :: Para mais detalhes, acesse o link abaixo:\r\n \r\n @link_mais_detalhes@\r\n\r\n :: Para descadastramento do acompanhamento do processo, acesse o link abaixo:\r\n\r\n @link_descadastramento@','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c),'PUSH - Notifica��o de Bloqueio','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Bloqueio de notifica��es do Processo n� @processo@','Aviso de bloqueio de notifica��o.\r\n \r\nInformamos que o processo n� @processo@ foi habilitado para o bloqueio de notifica��es. Dessa forma, voc� n�o receber� mais notifica��es sobre os andamentos.','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c),'PUSH - Cadastro no PUSH','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Cadastro no Processo n� @processo@',':: Este � um e-mail autom�tico ::\r\n \r\nPrezado(a) Sr(a). @nome_contato@,\r\n \r\nVoc� se cadastrou no servi�o PUSH para acompanhamento do processo @processo@ com a conta de e-mail @email_usuario_externo@.\r\n \r\nPara confirmar o seu cadastro, acesse o link a seguir:\r\n \r\n @link_autenticacao@ \r\n \r\n Ap�s a confirma��o do cadastro, voc� receber� mensagens (*uma vez ao dia*) sempre que o processo receber um ou mais andamentos.\r\n \r\nO servi�o PUSH possui car�ter meramente informativo e auxiliar, e n�o possui cunho oficial. Portanto, n�o substitui os meios oficiais de notifica��o, e de modo algum se presta para contagem de quaisquer prazos processuais.\r\n \r\nCCertifique-se de que sua caixa de correio eletr�nico esteja apta a receber mensagens do PUSH. Caixa de correio cheia, bloqueios AntiSpam, et. Podem impedir o recebimento das mensagens.\r\n \r\nEste org�o n�o se responsabiliza por eventuais falhas de comunica��o, sejam por indisponibilidade do servi�o, inconsist�ncia de dados cadastrais ou de qualquer outra natureza, que impe�am o recebimento da correspond�ncia eletr�nica.\r\n \r\nPara descadastramento do acompanhamento do processo, acesse o link:\r\n \r\n @link_descadastramento@','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c),'PUSH - Notifica��o Descadastramento','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Notifica��o de Descadastramento do Processo n� @processo@',':: Este � um e-mail autom�tico ::\r\n \r\nPrezado(a) Sr(a). @nome_contato@,\r\n \r\nSeu descadastramento do servi�o PUSH para acompanhamento do processo @processo@ foi realizado com sucesso.\r\n','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into md_push_config values ((select CASE WHEN max(id_md_push_config) + 1 is null THEN 1 ELSE max(id_md_push_config) + 1 END from md_push_config as c), 'ID_TEMPLATE_DESCADASTRAMENTO_PROCESSO_PUSH', (select id_email_sistema from email_sistema es where descricao = 'PUSH - Notifica��o Descadastramento'));",
                "insert into infra_agendamento_tarefa values ((select CASE WHEN max(id_infra_agendamento_tarefa) + 1 is null THEN 1 ELSE max(id_infra_agendamento_tarefa) + 1 END from infra_agendamento_tarefa as c), 'Verifica��o e envio de e-mails peri�dicos do PUSH.', 'PushAgendamentoRN::enviarEmailPush', 'D', '1', null, null, 'N', null, null, 'S');",
                "update infra_sequencia set num_atual=(select max(id_infra_agendamento_tarefa) from infra_agendamento_tarefa) where nome_tabela='infra_agendamento_tarefa';",
                "insert into seq_md_push_config (campo) values (null);",
                "insert into md_push_config values ((select CASE WHEN max(id_md_push_config) + 1 is null THEN 1 ELSE max(id_md_push_config) + 1 END from md_push_config as c), 'ID_TEMPLATE_CONFIRMACAO_CADASTRO_PROCESSO_PUSH', (select id_email_sistema from email_sistema es where descricao = 'PUSH - Cadastro no PUSH'));",
                "insert into seq_md_push_config (campo) values (null);",
                "insert into md_push_config values ((select CASE WHEN max(id_md_push_config) + 1 is null THEN 1 ELSE max(id_md_push_config) + 1 END from md_push_config as c), 'ID_TEMPLATE_BLOQUEIO_PROCESSO_PUSH', (select id_email_sistema from email_sistema es where descricao = 'PUSH - Notifica��o de Bloqueio'));",
                "insert into seq_md_push_config (campo) values (null);",
                "insert into infra_parametro values ('SEI_PUSH_MENSAGEM_ACEITE_TERMOS_INCLUSAO', '<div>TERMO DE ACEITA��O</div><div>O servi�o PUSH possui car�ter meramente informativo e auxiliar, e n�o possui cunho oficial. Portanto, n�o substitui os meios oficiais de comunica��o para a produ��o de efeitos legais, n�o constitui meio oficial de notifica��o, e de modo algum se presta para contagem de quaisquer prazos processuais, para os quais devem ser consideradas as publica��es no Di�rio Oficial da Uni�o e na p�gina do Minist�rio da Fazenda na internet </div><div>O Minist�rio da Fazenda n�o se responsabiliza por eventuais falhas de comunica��o, sejam por indisponibilidade do servi�o, inconsist�ncia de dados cadastrais ou de qualquer outra natureza, que impe�am o recebimento da correspond�ncia eletr�nica.</div>');"
            )
        );
    }
}

// ========================= INICIO SCRIPT EXECU��O =============

try {
    
    session_start();
    
    SessaoSEI::getInstance(false);
    
    $objVersaoRN = new InstaladorModuloSEIPushRN();
    $objVersaoRN->atualizarVersao();
} catch (Exception $e) {
    echo (nl2br(InfraException::inspecionar($e)));
    try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {}
}

// ========================== FIM SCRIPT EXECU��O ====================
?>