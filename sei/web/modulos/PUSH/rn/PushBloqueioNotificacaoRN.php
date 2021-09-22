<?
require_once dirname(__FILE__). '/../../../SEI.php';

class PushBloqueioNotificacaoRN extends InfraRN {

    public function __construct() {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco() {
        return BancoSEI::getInstance();
    }

    protected function cadastrarControlado(PushBloqueioNotificacaoDTO $objPushBloqueioNotificacaoDTO) {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('push_bloqueio_notificacao_cadastrar', __METHOD__, $objPushBloqueioNotificacaoDTO);

            $numeroProtocoloFormatado = $objPushBloqueioNotificacaoDTO->getStrNumeroProtocoloFormatado();

            $objProtocoloRN = new ProtocoloRN();
            $objProtocoloDTOPesquisa = new ProtocoloDTO();
            $objProtocoloDTOPesquisa->setStrProtocoloFormatado($numeroProtocoloFormatado);
            $objProtocoloDTOPesquisa->retDblIdProtocolo();
            $objProtocoloDTOPesquisa->retStrProtocoloFormatado();
            $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTOPesquisa);
            
            if (is_null($objProtocoloDTO))
                throw new InvalidArgumentException('Não foi possível encontrar o Protocolo com Número Formatado ' . $numeroProtocoloFormatado);

            $objPushBloqueioNotificacaoDTO->setStrNumeroProtocoloFormatado(null);

            $objPushBloqueioNotificacaoDTO->setDblIdProcedimento($objProtocoloDTO->getDblIdProtocolo());

            $objPushBloqueioNotificacaoBD = new PushBloqueioNotificacaoBD($this->getObjInfraIBanco());

            $objPushDTO = new PushDTO();
            $objPushDTO->setDblIdProcedimento($objProtocoloDTO->getDblIdProtocolo());
            $objPushDTO->retTodos();
            $objPushDTO->retStrNumeroProtocoloFormatado();
            $objPushRN = new PushRN();
            $arrObjPushDTO = $objPushRN->listar($objPushDTO);

            foreach($arrObjPushDTO as $objPushDTO){
                $this->notificarAssinante($objProtocoloDTO,$objPushDTO);
            }

            return $objPushBloqueioNotificacaoBD->cadastrar($objPushBloqueioNotificacaoDTO);
        }
        catch (InvalidArgumentException $e) {
            throw new InfraException ($e->getMessage(), $e);
        }
        catch (Exception $e) {
            throw new InfraException ('Erro cadastrando Bloqueio de notificação do PUSH.', $e);
        }
    }

    protected function excluirControlado($arrObjPushBloqueioNotificacaoDTO) {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('push_bloqueio_notificacao_excluir', __METHOD__, $arrObjPushBloqueioNotificacaoDTO);

            if (count($arrObjPushBloqueioNotificacaoDTO) > 0) {
                $objPushBloqueioNotificacaoBD = new PushBloqueioNotificacaoBD($this->getObjInfraIBanco());

                for ($i = 0; $i < count($arrObjPushBloqueioNotificacaoDTO); $i++)
                    $objPushBloqueioNotificacaoBD->excluir($arrObjPushBloqueioNotificacaoDTO[$i]);
            }
        }
        catch (Exception $e) {
            throw new InfraException ('Erro excluindo Bloqueios de notificação do PUSH.', $e);
        }
    }

    protected function listarConectado(PushBloqueioNotificacaoDTO $objPushBloqueioNotificacaoDTO) {

        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('push_bloqueio_notificacao_listar', __METHOD__, $objPushBloqueioNotificacaoDTO);

            $objPushBloqueioNotificacaoBD = new PushBloqueioNotificacaoBD($this->getObjInfraIBanco());

            return $objPushBloqueioNotificacaoBD->listar($objPushBloqueioNotificacaoDTO);
        }
        catch (Exception $e) {
            throw new InfraException('Erro listando Bloqueios de notificação do PUSH.', $e);
        }
    }

    protected function consultarConectado(PushBloqueioNotificacaoDTO $objPushBloqueioNotificacaoDTO) {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('push_bloqueio_notificacao_consultar', __METHOD__, $objPushBloqueioNotificacaoDTO);

            $objPushBloqueioNotificacaoBD = new PushBloqueioNotificacaoBD($this->getObjInfraIBanco());

            return $objPushBloqueioNotificacaoBD->consultar($objPushBloqueioNotificacaoDTO);
        }
        catch (Exception $e) {
            throw new InfraException('Erro consultando Bloqueio de notificação do PUSH.', $e);
        }
    }

    protected function pesquisarConectado(PushBloqueioNotificacaoDTO $objPushBloqueioNotificacaoDTO) {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('push_bloqueio_notificacao_listar',__METHOD__,$objPushBloqueioNotificacaoDTO);

            if($objPushBloqueioNotificacaoDTO->isSetStrNumeroProtocoloFormatado())
                $objPushBloqueioNotificacaoDTO->setStrNumeroProtocoloFormatado('%' . $objPushBloqueioNotificacaoDTO->getStrNumeroProtocoloFormatado() . '%', InfraDTO::$OPER_LIKE);

            $objPushBloqueioNotificacaoBD = new PushBloqueioNotificacaoBD($this->getObjInfraIBanco());

            return $objPushBloqueioNotificacaoBD->listar($objPushBloqueioNotificacaoDTO);

        }catch(Exception $e){
            throw new InfraException('Erro pesquisando Bloqueio de notificações do PUSH.',$e);
        }
    }

    public function notificarAssinante($objProtocoloDTO,$objPushDTO) {
        $objPushConfiguracaoDTO = new PushConfiguracaoDTO();
        $objPushConfiguracaoDTO->setStrNome('ID_TEMPLATE_BLOQUEIO_PROCESSO_PUSH');
        $objPushConfiguracaoDTO->retTodos();
        $objPushConfiguracaoRN = new PushConfiguracaoRN();
        $objPushConfiguracaoDTO = $objPushConfiguracaoRN->consultar($objPushConfiguracaoDTO);
        if($objPushConfiguracaoDTO != null){
            $objEmailSistemaDTO = new EmailSistemaDTO();
            $objEmailSistemaDTO->retTodos();
            $objEmailSistemaDTO->setNumIdEmailSistema($objPushConfiguracaoDTO->getStrValor());
            $objEmailSistemaRN = new EmailSistemaRN();
            $objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);
            $objEmailDTO = new EmailDTO();
            $objEmailDTO->setStrDe(PUSHUtil::montarRementente($objEmailSistemaDTO->getStrDe(), SessaoSEI::getStrSiglaOrgaoSistema()));
            $objEmailDTO->setStrPara($objPushDTO->getStrEmail());
            $objPushRN = new PushRN();
            $objEmailDTO->setStrAssunto($objPushRN->substituirVariaveisEmail($objEmailSistemaDTO->getStrAssunto(),$objPushDTO));
            $objEmailDTO->setStrMensagem($objPushRN->substituirVariaveisEmail($objEmailSistemaDTO->getStrConteudo(),$objPushDTO));
            EmailRN::processar(array($objEmailDTO));
        }
    }
}
?>