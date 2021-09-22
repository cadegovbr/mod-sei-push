<?
require_once dirname(__FILE__) . '/../web/Sip.php';

class InstaladorModuloPushRN extends InfraRN
{

    private $numSeg = 0;

    private $versaoAtualDesteModulo = '0.0.1';

    private $nomeParametroModulo = 'VERSAO_MODULO_PUSH';

    public function __construct()
    {
        parent::__construct();
        $this->inicializar(' SIP - INICIALIZAR ');
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSip::getInstance();
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

    private function recuperarSistemaId()
    {
        $objSistemaDTO = new SistemaDTO();
        $objSistemaDTO->retNumIdSistema();
        $objSistemaDTO->setStrSigla('SEI');
        $objSistemaRN = new SistemaRN();
        $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);
        if ($objSistemaDTO == null)
            throw new InfraException('Sistema SEI não encontrado.');
        
        return $objSistemaDTO->getNumIdSistema();
    }

    /* Contem atualizaçoes da versao 0.0.1 do modulo */
    protected function instalarv001()
    {
        $numIdSistemaSei = $this->recuperarSistemaId();
        
        $objPerfilRN = new PerfilRN();
        $numIdPerfilSeiAdministrador = $this->recuperarPerfilSeiAdministrador($numIdSistemaSei, $objPerfilRN);
        
        $numIdMenuSei = $this->recuperarMenuSei($numIdSistemaSei);
        
        $this->logar('ID MENU PRINCIPAL SEI ' . $numIdMenuSei);
        
        $this->adicionarRecursosPerfilSeiAdministrador($numIdSistemaSei, $numIdPerfilSeiAdministrador);
        $this->adicionarItensMenuPerfilSeiAdministrador($numIdSistemaSei, $numIdPerfilSeiAdministrador, $numIdMenuSei, $numIdItemMenuSeiAdministracao);
    }

    private function recuperarPerfilSeiAdministrador($numIdSistemaSei, $objPerfilRN)
    {
        $objPerfilDTO = new PerfilDTO();
        $objPerfilDTO->retNumIdPerfil();
        $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
        $objPerfilDTO->setStrNome('Administrador');
        $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);
        
        if ($objPerfilDTO == null)
            throw new InfraException('Perfil Administrador do sistema SEI não encontrado.');
        
        return $objPerfilDTO->getNumIdPerfil();
    }

    private function recuperarMenuSei($numIdSistemaSei)
    {
        $objMenuDTO = new MenuDTO();
        $objMenuDTO->retNumIdMenu();
        $objMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objMenuDTO->setStrNome('Principal');
        $objMenuRN = new MenuRN();
        $objMenuDTO = $objMenuRN->consultar($objMenuDTO);
        
        if ($objMenuDTO == null)
            throw new InfraException('Menu do sistema SEI não encontrado.');
        
        return $objMenuDTO->getNumIdMenu();
        ;
    }

    private function cadastrarItemMenuSeiAdministracao($numIdSistemaSei)
    {
        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objItemMenuDTO->setStrRotulo('Administração');
        $objItemMenuRN = new ItemMenuRN();
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
        
        if ($objItemMenuDTO == null)
            throw new InfraException('Item de menu Administração do sistema SEI não encontrado.');
        
        return $objItemMenuDTO->getNumIdItemMenu();
    }

    private function adicionarRecursosPerfilSeiAdministrador($numIdSistemaSei, $numIdPerfilSeiAdministrador)
    {
        $arrRecursosPerfilSeiAdministrador = array(
            'push_alterar',
            'push_excluir',
            'push_listar',
            'push_consultar',
            'push_enviar_email',
            'push_descadastrar',
            'push_notificacao_cadastrar',
            'push_notificacao_alterar',
            'push_notificacao_excluir',
            'push_notificacao_listar',
            'push_notificacao_consultar',
            'push_notificacao_selecionar',
            'push_notificacao_tipo_procedimento_selecionar',
            'push_bloqueio_notificacao_cadastrar',
            'push_bloqueio_notificacao_listar',
            'push_bloqueio_notificacao_excluir',
            'push_bloqueio_notificacao_consultar',
            'push_email_controle_cadastrar',
            'push_email_controle_alterar',
            'push_email_controle_listar',
            'push_email_controle_consultar',
            'push_config_cadastrar',
            'push_config_alterar',
            'push_config_excluir',
            'push_config_listar',
            'push_config_consultar'
        );
        
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, $arrRecursosPerfilSeiAdministrador);
    }

    private function adicionarItensMenuPerfilSeiAdministrador($numIdSistemaSei, $numIdPerfilSeiAdministrador, $numIdMenuSei, $numIdItemMenuSeiAdministracao)
    {
        $objItemMenuPUSH = $this->adicionarItemMenu(array(
            array(
                'IdSistema' => $numIdSistemaSei,
                'IdPerfil' => $numIdPerfilSeiAdministrador,
                'IdMenu' => $numIdMenuSei,
                'IdMenuPai' => $this->cadastrarItemMenuSeiAdministracao($this->recuperarSistemaId()),
                'IdRecurso' => null,
                'rotulo' => 'PUSH',
                'sequencia' => 82
            )
        ));
        $numIdItemMenuPUSH = $objItemMenuPUSH->getNumIdItemMenu();
        
        $arrItensMenuPerfilSeiAdministrador = array(
            array(
                'IdSistema' => $numIdSistemaSei,
                'IdPerfil' => $numIdPerfilSeiAdministrador,
                'IdMenu' => $numIdMenuSei,
                'IdMenuPai' => $numIdItemMenuPUSH,
                'IdRecurso' => $this->getObjRecursoMenu($numIdSistemaSei, 'push_notificacao_listar'),
                'rotulo' => 'Notificações',
                'sequencia' => 10
            ),
            array(
                'IdSistema' => $numIdSistemaSei,
                'IdPerfil' => $numIdPerfilSeiAdministrador,
                'IdMenu' => $numIdMenuSei,
                'IdMenuPai' => $numIdItemMenuPUSH,
                'IdRecurso' => $this->getObjRecursoMenu($numIdSistemaSei, 'push_listar'),
                'rotulo' => 'Assinaturas',
                'sequencia' => 30
            ),
            array(
                'IdSistema' => $numIdSistemaSei,
                'IdPerfil' => $numIdPerfilSeiAdministrador,
                'IdMenu' => $numIdMenuSei,
                'IdMenuPai' => $numIdItemMenuPUSH,
                'IdRecurso' => $this->getObjRecursoMenu($numIdSistemaSei, 'push_bloqueio_notificacao_listar'),
                'rotulo' => 'Bloqueios de Notificação',
                'sequencia' => 40
            ),
            array(
                'IdSistema' => $numIdSistemaSei,
                'IdPerfil' => $numIdPerfilSeiAdministrador,
                'IdMenu' => $numIdMenuSei,
                'IdMenuPai' => $numIdItemMenuPUSH,
                'IdRecurso' => $this->getObjRecursoMenu($numIdSistemaSei, 'push_config_listar'),
                'rotulo' => 'Configurações',
                'sequencia' => 50
            )
        );
        
        $this->adicionarItemMenu($arrItensMenuPerfilSeiAdministrador);
    }


    protected function atualizarVersaoConectado()
    {
        try {
            
            // checando BDs suportados
            if (! (BancoSip::getInstance() instanceof InfraMySql) && ! (BancoSip::getInstance() instanceof InfraSqlServer) && ! (BancoSip::getInstance() instanceof InfraOracle))
                $this->finalizar('BANCO DE DADOS NAO SUPORTADO: ' . get_parent_class(BancoSip::getInstance()), true);
            
            // checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());
            
            if (count($objInfraMetaBD->obterTabelas('sip_teste')) == 0)
                BancoSip::getInstance()->executarSql('CREATE TABLE sip_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            
            BancoSip::getInstance()->executarSql('DROP TABLE sip_teste');
            
            // checando qual versao instalar
            $objInfraParametro = new InfraParametro(BancoSip::getInstance());
            
            $strVersaoModuloLitigioso = $objInfraParametro->getValor($this->nomeParametroModulo, false);
            
            if (InfraString::isBolVazia($strVersaoModuloLitigioso)) {
                
                // aplica atualizaçoes da versao 0.0.1
                $this->instalarv001();
                
                // adicionando parametro para controlar versao do modulo
                BancoSip::getInstance()->executarSql('insert into infra_parametro (valor, nome ) VALUES( \'' . $this->versaoAtualDesteModulo . '\',  \'' . $this->nomeParametroModulo . '\' )');
                $this->logar('ATUALIZAÇÔES DO MÓDULO PUSH NA BASE DO SIP REALIZADAS COM SUCESSO');
            } else {
                $this->logar('SIP - MÓDULO PUSH v0.0.1 JÁ INSTALADO');
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

    private function adicionarRecursoPerfil($numIdSistema, $numIdPerfil, $strNomes = array(), $strCaminho = null)
    {
        foreach ($strNomes as $strNome) {
            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->retNumIdRecurso();
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);
            
            $objRecursoRN = new RecursoRN();
            $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);
            
            if ($objRecursoDTO == null) {
                
                $objRecursoDTO = new RecursoDTO();
                $objRecursoDTO->setNumIdRecurso(null);
                $objRecursoDTO->setNumIdSistema($numIdSistema);
                $objRecursoDTO->setStrNome($strNome);
                $objRecursoDTO->setStrDescricao(null);
                
                if ($strCaminho == null)
                    $objRecursoDTO->setStrCaminho('controlador.php?acao=' . $strNome);
                else
                    $objRecursoDTO->setStrCaminho($strCaminho);
                
                $objRecursoDTO->setStrSinAtivo('S');
                $objRecursoDTO = $objRecursoRN->cadastrar($objRecursoDTO);
            }
            
            if ($numIdPerfil != null) {
                $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
                $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
                $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
                $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
                
                $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
                
                if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO) == 0)
                    $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
            }
        }
    }

    private function getObjRecursoMenu($numIdSistema, $strNome)
    {
        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNome);
        
        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);
        
        if ($objRecursoDTO == null)
            throw new InfraException('Recurso não encontrado.');
        return $objRecursoDTO->getNumIdRecurso();
    }

    private function adicionarItemMenu($arrParametros)
    {
        foreach ($arrParametros as $parametros) {
            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->retNumIdItemMenu();
            $objItemMenuDTO->setNumIdMenu($parametros['IdMenu']);
            
            if ($parametros['IdMenuPai'] == null) {
                $objItemMenuDTO->setNumIdMenuPai(null);
                $objItemMenuDTO->setNumIdItemMenuPai(null);
            } else {
                $objItemMenuDTO->setNumIdMenuPai($parametros['IdMenu']);
                $objItemMenuDTO->setNumIdItemMenuPai($parametros['IdMenuPai']);
            }
            
            $objItemMenuDTO->setNumIdSistema($parametros['IdSistema']);
            $objItemMenuDTO->setNumIdRecurso($parametros['IdRecurso']);
            $objItemMenuDTO->setStrRotulo($parametros['rotulo']);
            
            $objItemMenuRN = new ItemMenuRN();
            $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);
            
            if ($objItemMenuDTO == null) {
                
                $objItemMenuDTO = new ItemMenuDTO();
                $objItemMenuDTO->setNumIdItemMenu(null);
                $objItemMenuDTO->setNumIdMenu($parametros['IdMenu']);
                
                if ($parametros['IdMenuPai'] == null) {
                    $objItemMenuDTO->setNumIdMenuPai(null);
                    $objItemMenuDTO->setNumIdItemMenuPai(null);
                } else {
                    $objItemMenuDTO->setNumIdMenuPai($parametros['IdMenu']);
                    $objItemMenuDTO->setNumIdItemMenuPai($parametros['IdMenuPai']);
                }
                
                $objItemMenuDTO->setNumIdSistema($parametros['IdSistema']);
                $objItemMenuDTO->setNumIdRecurso($parametros['IdRecurso']);
                $objItemMenuDTO->setStrRotulo($parametros['rotulo']);
                $objItemMenuDTO->setStrDescricao(null);
                $objItemMenuDTO->setNumSequencia($parametros['sequencia']);
                $objItemMenuDTO->setStrSinNovaJanela('N');
                $objItemMenuDTO->setStrSinAtivo('S');
                $objItemMenuDTO = $objItemMenuRN->cadastrar($objItemMenuDTO);
            }
            
            if ($parametros['IdPerfil'] != null && $parametros['IdRecurso'] != null) {
                $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
                $objRelPerfilRecursoDTO->setNumIdSistema($parametros['IdSistema']);
                $objRelPerfilRecursoDTO->setNumIdPerfil($parametros['IdPerfil']);
                $objRelPerfilRecursoDTO->setNumIdRecurso($parametros['IdRecurso']);
                
                $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
                
                if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO) == 0)
                    $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
                
                $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
                $objRelPerfilItemMenuDTO->setNumIdPerfil($parametros['IdPerfil']);
                $objRelPerfilItemMenuDTO->setNumIdSistema($parametros['IdSistema']);
                $objRelPerfilItemMenuDTO->setNumIdRecurso($parametros['IdRecurso']);
                $objRelPerfilItemMenuDTO->setNumIdMenu($parametros['IdMenu']);
                $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());
                
                $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
                
                if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO) == 0)
                    $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
            }
            
            if ($parametros['IdRecurso'] == null)
                return $objItemMenuDTO;
        }
    }
}

// ========================= INICIO SCRIPT EXECUÇAO =============

try {
    
    session_start();
    
    SessaoSip::getInstance(false);
    
    $objVersaoRN = new InstaladorModuloPushRN();
    $objVersaoRN->atualizarVersao();
} catch (Exception $e) {
    echo (nl2br(InfraException::inspecionar($e)));
    try {
        LogSip::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {}
}

// ========================== FIM SCRIPT EXECUÇÂO ====================
?>