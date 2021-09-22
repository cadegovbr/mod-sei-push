<?
require_once dirname(__FILE__). '/../../../SEI.php';

class PushNotificacaoRN extends InfraRN {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance();
	}
	
	protected function cadastrarControlado(PushNotificacaoDTO $objPushNotificacaoDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_cadastrar', __METHOD__, $objPushNotificacaoDTO);

			$objInfraException = new InfraException();

			if(count($objPushNotificacaoDTO->getArrObjTarefaDTO()) == 0){
				$objInfraException->adicionarValidacao('Nenhuma tarefa selecionada para esta Notificação.');
				$objInfraException->lancarValidacoes();
			}

			$objPushNotificacaoConsultaDTO = new PushNotificacaoDTO();
			$objPushNotificacaoConsultaDTO->retTodos();
			$objPushNotificacaoConsultaDTO->setNumIdTipoProcedimento($objPushNotificacaoDTO->getNumIdTipoProcedimento());
			$objPushNotificacaoConsultaDTO = $this->consultar($objPushNotificacaoConsultaDTO);
			if(!empty($objPushNotificacaoConsultaDTO)) {
				$objInfraException->adicionarValidacao('Uma notificação de PUSH com os dados informados já existe no registro.');
				$objInfraException->lancarValidacoes();
			}
			
			$objPushNotificacaoBD = new PushNotificacaoBD($this->getObjInfraIBanco());
			$objPushNotificacaoBD->cadastrar($objPushNotificacaoDTO);

			$objPushNotificacaoConsultaDTO = new PushNotificacaoDTO();
			$objPushNotificacaoConsultaDTO->retTodos();
			$objPushNotificacaoConsultaDTO->setNumIdTipoProcedimento($objPushNotificacaoDTO->getNumIdTipoProcedimento());
			$objPushNotificacaoConsultaDTO = $this->consultar($objPushNotificacaoConsultaDTO);

			$objPushNotificacaoDTO->setNumIdNotificacao($objPushNotificacaoConsultaDTO->getNumIdNotificacao());

			foreach ($objPushNotificacaoDTO->getArrObjTarefaDTO() as $objTarefaDTO) {
				$objPushRelNotificacaoTarefaRN = new PushRelNotificacaoTarefaRN();
				$objPushRelNotificacaoTarefaDTO = new PushRelNotificacaoTarefaDTO();
				$objPushRelNotificacaoTarefaDTO->setNumIdRelNotificacaoTarefa(null);
				$objPushRelNotificacaoTarefaDTO->setNumIdNotificacao($objPushNotificacaoDTO->getNumIdNotificacao());
				$objPushRelNotificacaoTarefaDTO->setNumIdTarefa($objTarefaDTO->getNumIdTarefa());
				$objPushRelNotificacaoTarefaRN->cadastrar($objPushRelNotificacaoTarefaDTO);
			}

			return $objPushNotificacaoDTO;

		} catch(Exception $e){
			throw new InfraException('Erro cadastrando registro de Notificação do PUSH.', $e);
		}
	}
	
	private function modificarNotificacaoProcessoRestrito($objPushNotificacaoDTO, $sinNotificaRestrito) {
	    $objPushBD = new PushBD($this->getObjInfraIBanco());
	    
	    $objPushNotificacaoConsultaDTO = new PushNotificacaoDTO();
	    $objPushNotificacaoConsultaDTO->retTodos();
	    $objPushNotificacaoConsultaDTO->setNumIdTipoProcedimento($objPushNotificacaoDTO->getNumIdTipoProcedimento());
	    $arrObjPushNotificacaoConsultaDTO = $this->listar($objPushNotificacaoConsultaDTO);
	    
	    foreach ($arrObjPushNotificacaoConsultaDTO as $objPushNotificacaoConsultaDTO) {
	        $objPushAlteracaoDTO = new PushNotificacaoDTO();
	        $objPushAlteracaoDTO->setNumIdNotificacao($objPushNotificacaoConsultaDTO->getNumIdNotificacao());
	        $objPushAlteracaoDTO->setStrNotificaRestrito($sinNotificaRestrito);
	        $objPushBD->alterar($objPushAlteracaoDTO);
	    }
	}

    protected function alterarControlado(PushNotificacaoDTO $objPushNotificacaoDTO) {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_alterar', __METHOD__, $objPushNotificacaoDTO);

            $objInfraException = new InfraException();

            if(count($objPushNotificacaoDTO->getArrObjTarefaDTO()) == 0){
				$objInfraException->adicionarValidacao('Nenhuma tarefa selecionada para esta Notificação.');
				$objInfraException->lancarValidacoes();
			}

			$objPushNotificacaoConsultaDTO = new PushNotificacaoDTO();
			$objPushNotificacaoConsultaDTO->retTodos();
			$objPushNotificacaoConsultaDTO->setNumIdTipoProcedimento($objPushNotificacaoDTO->getNumIdTipoProcedimento());
			$objPushNotificacaoConsultaDTO = $this->consultar($objPushNotificacaoConsultaDTO);
			
			if(!empty($objPushNotificacaoConsultaDTO) && $objPushNotificacaoConsultaDTO->getNumIdNotificacao() != $objPushNotificacaoDTO->getNumIdNotificacao()) {
				$objInfraException->adicionarValidacao('Uma notificação de PUSH com os dados informados já existe no registro.');
				$objInfraException->lancarValidacoes();
			}
			
			$objPushNotificacaoBD = new PushNotificacaoBD($this->getObjInfraIBanco());
			$objPushNotificacaoBD->alterar($objPushNotificacaoDTO);

			$objPushRelNotificacaoTarefaDTO = new PushRelNotificacaoTarefaDTO();
			$objPushRelNotificacaoTarefaDTO->setNumIdNotificacao($objPushNotificacaoDTO->getNumIdNotificacao());
			$objPushRelNotificacaoTarefaDTO->retTodos();
			$objPushRelNotificacaoTarefaRN = new PushRelNotificacaoTarefaRN();

			foreach ($objPushRelNotificacaoTarefaRN->listar($objPushRelNotificacaoTarefaDTO) as $objPushRelNotificacaoTarefaDTO) {
				$objPushRelNotificacaoTarefaRN->excluir($objPushRelNotificacaoTarefaDTO);
			}

			foreach ($objPushNotificacaoDTO->getArrObjTarefaDTO() as $objTarefaDTO) {
				$objPushRelNotificacaoTarefaDTO = new PushRelNotificacaoTarefaDTO();
				$objPushRelNotificacaoTarefaDTO->setNumIdRelNotificacaoTarefa(null);
				$objPushRelNotificacaoTarefaDTO->setNumIdNotificacao($objPushNotificacaoDTO->getNumIdNotificacao());
				$objPushRelNotificacaoTarefaDTO->setNumIdTarefa($objTarefaDTO->getNumIdTarefa());
				$objPushRelNotificacaoTarefaRN->cadastrar($objPushRelNotificacaoTarefaDTO);
			}

			if($objPushNotificacaoDTO->getStrSinNotificaRestrito() == 'N'){
				$objPushDTO = new PushDTO();
				$objPushDTO->setNumIdTipoProcedimento($objPushNotificacaoDTO->getNumIdTipoProcedimento());
				$objPushDTO->setStrStaNivelAcessoGlobal(ProtocoloRN::$NA_PUBLICO,InfraDTO::$OPER_DIFERENTE);
				$objPushDTO->retStrNumeroProtocoloFormatado();
				$objPushDTO->retTodos();
				$objPushRN = new PushRN();
				$arrObjPushDTO = $objPushRN->listar($objPushDTO);
				foreach($arrObjPushDTO as $objPushDTO){
					$objProtocoloDTO = new ProtocoloDTO();
					$objProtocoloDTO->setDblIdProtocolo($objPushDTO->getDblIdProcedimento());
					$objProtocoloDTO->retTodos();
					$objProtocoloRN = new ProtocoloRN();
					$objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);
					if($objProtocoloDTO != null){

						$objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
						$objPushBloqueioNotificacaoRN->notificarAssinante($objProtocoloDTO,$objPushDTO);

						$objPushDTO->setDthUltimoEnvioEmail(InfraData::getStrDataHoraAtual());
                    	$objPushRN->alterar($objPushDTO);	
					}
				}
			}

			return $objPushNotificacaoDTO;
        }
        catch (Exception $e) {
            throw new InfraException ('Erro alterando registro de Notificação do PUSH.', $e);
        }
    }
	
	protected function excluirControlado($arrObjPushNotificacaoDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_excluir', __METHOD__, $arrObjPushNotificacaoDTO);
			
			if(count($arrObjPushNotificacaoDTO) > 0) {
				$objPushBD = new PushBD($this->getObjInfraIBanco());
				$objPushRelNotificacaoTarefaRN = new PushRelNotificacaoTarefaRN();
				foreach($arrObjPushNotificacaoDTO as $objPushNotificacaoDTO){
					$objPushRelNotificacaoTarefaDTO = new PushRelNotificacaoTarefaDTO();
					$objPushRelNotificacaoTarefaDTO->setNumIdNotificacao($objPushNotificacaoDTO->getNumIdNotificacao());
					$objPushRelNotificacaoTarefaDTO->retTodos();
					$arrObjPushRelNotificacaoTarefaDTO = $objPushRelNotificacaoTarefaRN->listar($objPushRelNotificacaoTarefaDTO);

					foreach($arrObjPushRelNotificacaoTarefaDTO as $objPushRelNotificacaoTarefaDTO){
						$objPushRelNotificacaoTarefaRN->excluir($objPushRelNotificacaoTarefaDTO);
					}

					$objPushBD->excluir($objPushNotificacaoDTO);
				}
			}
		} catch(Exception $e){
			throw new InfraException('Erro excluindo registro de Notificação do PUSH.', $e);
		}
	}
	
	protected function listarConectado(PushNotificacaoDTO $objPushNotificacaoDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_listar', __METHOD__, $objPushNotificacaoDTO);
			
			$objPushBD = new PushBD($this->getObjInfraIBanco());
			$objPushNotificacaoDTO->retNumIdNotificacao();

			$arrObjPushNotificacaoDTO = $objPushBD->listar($objPushNotificacaoDTO);

			foreach($arrObjPushNotificacaoDTO as $i => $objPushNotificacao1DTO){

				$objPushRelNotificacaoTarefaDTO = new PushRelNotificacaoTarefaDTO();
				$objPushRelNotificacaoTarefaDTO->setNumIdNotificacao($objPushNotificacao1DTO->getNumIdNotificacao());
				$objPushRelNotificacaoTarefaDTO->retTodos();

				$objPushRelNotificacaoTarefaRN = new PushRelNotificacaoTarefaRN();
				$arrObjPushRelNotificacaoTarefaDTO = $objPushRelNotificacaoTarefaRN->listar($objPushRelNotificacaoTarefaDTO);

				$arrIdTarefa = array();
				foreach ($arrObjPushRelNotificacaoTarefaDTO as $objPushRelNotificacaoTarefaDTO) {
					$arrIdTarefa[] = $objPushRelNotificacaoTarefaDTO->getNumIdTarefa();
				}

				$objTarefaDTO = new TarefaDTO();
				$objTarefaDTO->setNumIdTarefa($arrIdTarefa,InfraDTO::$OPER_IN);
				$objTarefaDTO->retTodos();
				$objTarefaRN = new TarefaRN();
				$arrObjPushNotificacaoDTO[$i]->setArrObjTarefaDTO($objTarefaRN->listar($objTarefaDTO));
			}
			
			return $arrObjPushNotificacaoDTO;
		} catch(Exception $e){
			throw new InfraException('Erro listando registro de Notificação do PUSH.', $e);
		}
	}
	
	protected function consultarConectado(PushNotificacaoDTO $objPushNotificacaoDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_consultar', __METHOD__, $objPushNotificacaoDTO);

			$objPushBD = new PushBD($this->getObjInfraIBanco());
			$objPushNotificacaoDTO->retNumIdNotificacao();
			$objPushNotificacaoDTO = $objPushBD->consultar($objPushNotificacaoDTO);
			if($objPushNotificacaoDTO != null){

				$objPushRelNotificacaoTarefaDTO = new PushRelNotificacaoTarefaDTO();
				$objPushRelNotificacaoTarefaDTO->setNumIdNotificacao($objPushNotificacaoDTO->getNumIdNotificacao());
				$objPushRelNotificacaoTarefaDTO->retTodos();

				$objPushRelNotificacaoTarefaRN = new PushRelNotificacaoTarefaRN();
				$arrObjPushRelNotificacaoTarefaDTO = $objPushRelNotificacaoTarefaRN->listar($objPushRelNotificacaoTarefaDTO);

				if(count($arrObjPushRelNotificacaoTarefaDTO) > 0){
					$arrIdTarefa = array();
					foreach ($arrObjPushRelNotificacaoTarefaDTO as $objPushRelNotificacaoTarefaDTO) {
						$arrIdTarefa[] = $objPushRelNotificacaoTarefaDTO->getNumIdTarefa();
					}

					$objTarefaDTO = new TarefaDTO();
					$objTarefaDTO->setNumIdTarefa($arrIdTarefa,InfraDTO::$OPER_IN);
					$objTarefaDTO->retTodos();
					$objTarefaRN = new TarefaRN();
					$objPushNotificacaoDTO->setArrObjTarefaDTO($objTarefaRN->listar($objTarefaDTO));
				}
			}
			
			return $objPushNotificacaoDTO;
		} catch(Exception $e){
			throw new InfraException('Erro consultando registro do PUSH.', $e);
		}
	}
	
	protected function pesquisarConectado(PushNotificacaoDTO $objPushNotificacaoDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_listar', __METHOD__, $objPushNotificacaoDTO);
			
			$objPushBD = new PushBD($this->getObjInfraIBanco());
			
			return $objPushBD->listar($objPushNotificacaoDTO);
		} catch(Exception $e){
			throw new InfraException('Erro pesquisando registro de Notificação do PUSH.', $e);
		}
	}
	
	protected function pesquisarTarefasAjaxConectado($arrParametros) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_listar', __METHOD__, $objPushNotificacaoDTO);
			$objTarefaDTO = new TarefaDTO();
			$objTarefaDTO->retNumIdTarefa();
			$objTarefaDTO->retStrNome();

			switch ($arrParametros[1]) {
				case 'R':
					$objTarefaDTO->setStrSinHistoricoResumido('S');
					break;
				case 'C':
					$objTarefaDTO->setStrSinHistoricoCompleto('S');
					break;
				default:
					break;
			}

			$objTarefaDTO = $this->montarCampoPesquisaPalavrasPesquisa($arrParametros[0], $objTarefaDTO);
			
			$objTarefaRN = new TarefaRN();
			$arrObjTarefaDTO = $objTarefaRN->listar($objTarefaDTO);
			
			return $arrObjTarefaDTO;
		} catch(Exception $e) {
			throw new InfraException('Erro pesquisando Tarefas da Notificação.', $e);
		}
	}

	protected function pesquisarTiposProcedimentoAjaxConectado($arrParametros) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_notificacao_listar', __METHOD__, $objPushNotificacaoDTO);
			$objTipoProcedimentoDTO = new TipoProcedimentoDTO();
			$objTipoProcedimentoDTO->retNumIdTipoProcedimento();
			$objTipoProcedimentoDTO->retStrNome();
			$objTipoProcedimentoDTO = $this->montarCampoPesquisaPalavrasPesquisa($arrParametros[0], $objTipoProcedimentoDTO);
			
			$objTipoProcedimentoRN = new TipoProcedimentoRN();
			$arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);
			return $arrObjTipoProcedimentoDTO;
		} catch(Exception $e) {
			throw new InfraException('Erro pesquisando Tipos de de Procedimento da Notificação.', $e);
		}
	}
	
	private function montarCampoPesquisaPalavrasPesquisa($objPushNotificacaoDTO, $objTarefaDTO) {
		if($objPushNotificacaoDTO->isSetStrPalavrasPesquisa()) {
			if(trim($objPushNotificacaoDTO->getStrPalavrasPesquisa()) != '') {
				$strPalavrasPesquisa = InfraString::prepararIndexacao($objPushNotificacaoDTO->getStrPalavrasPesquisa(), false);
				$arrStrPalavrasPesquisa = explode(' ', $strPalavrasPesquisa);
				if(!empty($arrStrPalavrasPesquisa)) {
					$numPalavrasPesquisa = count($arrStrPalavrasPesquisa);
					for($i = 0; $i < $numPalavrasPesquisa; $i ++) {
						$arrStrPalavrasPesquisa[$i] = '%' . $arrStrPalavrasPesquisa[$i] . '%';
					}
					if($numPalavrasPesquisa == 1)
						$objTarefaDTO->setStrNome($arrStrPalavrasPesquisa[0], InfraDTO::$OPER_LIKE);
					else {
						$strCampoBusca = array_fill(0, $numPalavrasPesquisa, 'Nome');
						$strOperadorComparativo = array_fill(0, $numPalavrasPesquisa, InfraDTO::$OPER_LIKE);
						$strOperadorLogico = array_fill(0, $numPalavrasPesquisa - 1, InfraDTO::$OPER_LOGICO_AND);
						$objTarefaDTO->adicionarCriterio($strCampoBusca, $strOperadorComparativo, $arrStrPalavrasPesquisa, $strOperadorLogico);
					}
				}
			}
		}
		return $objTarefaDTO;
	}
}
?>