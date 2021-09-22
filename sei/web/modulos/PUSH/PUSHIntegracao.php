<?

class PUSHIntegracao extends SeiIntegracao {
	
	public function getNome() {
		return 'Módulo PUSH';
	}
	
	public function getVersao() {
		return '1.0.0';
	}
	
	public function getInstituicao() {
		return 'Ministério da Fazenda';
	}
	
	public function inicializar($strVersaoSEI) {
		if (substr($strVersaoSEI, 0, 2) != '3.')
			die('Módulo "' . $this->getNome() . '" (' . $this->getVersao() . ') não é compatível com esta versão do SEI (' . $strVersaoSEI . ').');
	}

    public function montarAcaoControleAcessoExterno($arrObjAcessoExternoAPI){
    	SessaoSEIExterna::getInstance()->configurarAcessoExterno(null);
        foreach ($arrObjAcessoExternoAPI as $ObjAcessoExternoAPI) {
            $objPushRN = new PushRN();
            if($objPushRN->sinPermitidaAssinatura($ObjAcessoExternoAPI->getProcedimento()->getIdProcedimento())){
                $arrAcoes[$ObjAcessoExternoAPI->getIdAcessoExterno()][] = '<a href="' . PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao=push_cadastrar&acao_origem=' . $_GET ['acao'] . '&acao_retorno=' . $_GET ['acao'] . '&idProcedimento=' . $ObjAcessoExternoAPI->getProcedimento()->getIdProcedimento())) . '"><img src="' . PaginaSEIExterna::getInstance()->getDiretorioImagensGlobal() . '/assinar.gif" title="Cadastrar Processo no PUSH" alt="Cadastrar Processo no PUSH" class="imagemStatus" /></a>';
            }
        }

        return $arrAcoes;
    }

    public function processarControladorExterno($strAcao){
        switch($strAcao) {
            case 'push_cadastrar':
                require_once dirname(__FILE__) . '/push_assinar_processo.php';
                return true;
        }

        return false;
    }
	
	public function processarControladorAjax($strAcao){
	
		$xml = null;
	
		switch($strAcao) {
			case 'push_notificacao_selecionar_tarefa':
				$arrTarefaDTO = PushNotificacaoINT::autoCompletarTarefas(trim($_POST['palavras_pesquisa']),$_POST['TipoHistorico']);
				$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrTarefaDTO,'IdTarefa', 'Nome');
				break;
			case 'push_notificacao_tipo_procedimento_selecionar':
				$arrTipoProcedimentoDTO = PushNotificacaoINT::autoCompletarTipoProcedimento(trim($_POST['palavras_pesquisa']));
				$xml = InfraAjax::gerarXMLItensArrInfraDTO($arrTipoProcedimentoDTO,'IdTipoProcedimento', 'Nome');
				break;
		}

		return $xml;
	}    
	
	public function processarControlador($strAcao) {
		switch ($strAcao) {
            case 'push_listar':
                require_once dirname(__FILE__) . '/push_lista.php';
                return true;
			case 'push_descadastrar':
				require_once dirname(__FILE__) . '/push_descadastrar_email.php';
				return true;
			case 'push_notificacao_cadastrar':
            case 'push_notificacao_consultar':
            case 'push_notificacao_alterar':
				require_once dirname(__FILE__) . '/push_notificacao_cadastro.php';
				return true;
			case 'push_notificacao_selecionar':
				require_once dirname(__FILE__) . '/push_eventos_lista.php';
				return true;
			case 'push_notificacao_tipo_procedimento_selecionar':
				require_once dirname(__FILE__) . '/push_tipo_processo_lista.php';
				return true;
			case 'push_notificacao_listar':
			case 'push_notificacao_excluir':
				require_once dirname(__FILE__) . '/push_notificacao_lista.php';
				return true;
            case 'push_bloqueio_notificacao_cadastrar':
            case 'push_bloqueio_notificacao_alterar':
            case 'push_bloqueio_notificacao_consultar':
                require_once dirname(__FILE__) . '/push_bloqueio_notificacao_cadastro.php';
                return true;
            case 'push_bloqueio_notificacao_listar':
            case 'push_bloqueio_notificacao_excluir':
                require_once dirname(__FILE__) . '/push_bloqueio_notificacao_lista.php';
                return true;
            case 'push_config_excluir' :
            case 'push_config_listar' :
            case 'push_config_selecionar' :
                require_once dirname(__FILE__) . '/push_configuracao_lista.php';
                return true;
            case 'push_config_alterar' :
            case 'push_config_consultar' :
            case 'push_config_cadastrar' :
                require_once dirname(__FILE__) . '/push_configuracao_cadastro.php';
                return true;
		}
	
		return false;
	}
}
?>