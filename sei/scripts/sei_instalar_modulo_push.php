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
            $this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . $this->numSeg . ' s');
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
                $this->logar('iniciando a criação das tabelas...');
                
                $this->instalarv001($bancoSEI);
                
                $this->adicionarParametroControladorVersao($bancoSEI);
                
                $this->logar('ATUALIZAÇÕES DO MÓDULO PUSH NA BASE DO SEI REALIZADAS COM SUCESSO');
            } else {
                $this->logar('SEI - MÓDULO PUSH v0.0.1 JÁ INSTALADO');
                $this->finalizar('FIM', true);
            }
            
            $this->finalizar('FIM', false);
        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(false);
            InfraDebug::getInstance()->setBolDebugInfra(false);
            InfraDebug::getInstance()->setBolEcho(false);
            throw new InfraException('Erro atualizando versão.', $e);
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
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c) ,'PUSH - Andamento do processo registrado','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Movimentação processual Processo nº @processo@','      :: Este é um e-mail automático ::\r\n \r\n Prezado(a) @nome_contato@,\r\n \r\n Este e-mail informa que o Processo nº @processo@ recebeu o(s) seguinte(s) andamento(s):\r\n \r\n @lista_andamentos@\r\n \r\n Lista de Interessados do processo\r\n \r\n @lista_interessados@\r\n \r\n  :: Para mais detalhes, acesse o link abaixo:\r\n \r\n @link_mais_detalhes@\r\n\r\n :: Para descadastramento do acompanhamento do processo, acesse o link abaixo:\r\n\r\n @link_descadastramento@','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c),'PUSH - Notificação de Bloqueio','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Bloqueio de notificações do Processo nº @processo@','Aviso de bloqueio de notificação.\r\n \r\nInformamos que o processo nº @processo@ foi habilitado para o bloqueio de notificações. Dessa forma, você não receberá mais notificações sobre os andamentos.','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c),'PUSH - Cadastro no PUSH','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Cadastro no Processo nº @processo@',':: Este é um e-mail automático ::\r\n \r\nPrezado(a) Sr(a). @nome_contato@,\r\n \r\nVocê se cadastrou no serviço PUSH para acompanhamento do processo @processo@ com a conta de e-mail @email_usuario_externo@.\r\n \r\nPara confirmar o seu cadastro, acesse o link a seguir:\r\n \r\n @link_autenticacao@ \r\n \r\n Após a confirmação do cadastro, você receberá mensagens (*uma vez ao dia*) sempre que o processo receber um ou mais andamentos.\r\n \r\nO serviço PUSH possui caráter meramente informativo e auxiliar, e não possui cunho oficial. Portanto, não substitui os meios oficiais de notificação, e de modo algum se presta para contagem de quaisquer prazos processuais.\r\n \r\nCCertifique-se de que sua caixa de correio eletrônico esteja apta a receber mensagens do PUSH. Caixa de correio cheia, bloqueios AntiSpam, et. Podem impedir o recebimento das mensagens.\r\n \r\nEste orgão não se responsabiliza por eventuais falhas de comunicação, sejam por indisponibilidade do serviço, inconsistência de dados cadastrais ou de qualquer outra natureza, que impeçam o recebimento da correspondência eletrônica.\r\n \r\nPara descadastramento do acompanhamento do processo, acesse o link:\r\n \r\n @link_descadastramento@','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into email_sistema values ((select CASE WHEN max(id_email_sistema) + 1 is null THEN 1 ELSE max(id_email_sistema) + 1 END from email_sistema as c),'PUSH - Notificação Descadastramento','@sigla_sistema@ <@email_sistema@>','@email_usuario_externo@','SEI - PUSH - Notificação de Descadastramento do Processo nº @processo@',':: Este é um e-mail automático ::\r\n \r\nPrezado(a) Sr(a). @nome_contato@,\r\n \r\nSeu descadastramento do serviço PUSH para acompanhamento do processo @processo@ foi realizado com sucesso.\r\n','S',NULL);",
                "insert into seq_email_sistema (campo) values (null);",
                "insert into md_push_config values ((select CASE WHEN max(id_md_push_config) + 1 is null THEN 1 ELSE max(id_md_push_config) + 1 END from md_push_config as c), 'ID_TEMPLATE_DESCADASTRAMENTO_PROCESSO_PUSH', (select id_email_sistema from email_sistema es where descricao = 'PUSH - Notificação Descadastramento'));",
                "insert into infra_agendamento_tarefa values ((select CASE WHEN max(id_infra_agendamento_tarefa) + 1 is null THEN 1 ELSE max(id_infra_agendamento_tarefa) + 1 END from infra_agendamento_tarefa as c), 'Verificação e envio de e-mails periódicos do PUSH.', 'PushAgendamentoRN::enviarEmailPush', 'D', '1', null, null, 'N', null, null, 'S');",
                "update infra_sequencia set num_atual=(select max(id_infra_agendamento_tarefa) from infra_agendamento_tarefa) where nome_tabela='infra_agendamento_tarefa';",
                "insert into seq_md_push_config (campo) values (null);",
                "insert into md_push_config values ((select CASE WHEN max(id_md_push_config) + 1 is null THEN 1 ELSE max(id_md_push_config) + 1 END from md_push_config as c), 'ID_TEMPLATE_CONFIRMACAO_CADASTRO_PROCESSO_PUSH', (select id_email_sistema from email_sistema es where descricao = 'PUSH - Cadastro no PUSH'));",
                "insert into seq_md_push_config (campo) values (null);",
                "insert into md_push_config values ((select CASE WHEN max(id_md_push_config) + 1 is null THEN 1 ELSE max(id_md_push_config) + 1 END from md_push_config as c), 'ID_TEMPLATE_BLOQUEIO_PROCESSO_PUSH', (select id_email_sistema from email_sistema es where descricao = 'PUSH - Notificação de Bloqueio'));",
                "insert into seq_md_push_config (campo) values (null);",
                "insert into infra_parametro values ('SEI_PUSH_MENSAGEM_ACEITE_TERMOS_INCLUSAO', '<div>TERMO DE ACEITAÇÃO</div><div>O serviço PUSH possui caráter meramente informativo e auxiliar, e não possui cunho oficial. Portanto, não substitui os meios oficiais de comunicação para a produção de efeitos legais, não constitui meio oficial de notificação, e de modo algum se presta para contagem de quaisquer prazos processuais, para os quais devem ser consideradas as publicações no Diário Oficial da União e na página do Ministério da Fazenda na internet </div><div>O Ministério da Fazenda não se responsabiliza por eventuais falhas de comunicação, sejam por indisponibilidade do serviço, inconsistência de dados cadastrais ou de qualquer outra natureza, que impeçam o recebimento da correspondência eletrônica.</div>');"
            )
        );
    }
}

// ========================= INICIO SCRIPT EXECUÇÃO =============

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

// ========================== FIM SCRIPT EXECUÇÃO ====================
?>