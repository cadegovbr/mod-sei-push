<?
require_once dirname(__FILE__). '/../../../SEI.php';

class PushRN extends InfraRN {
	
	public function __construct() {
		parent::__construct();
	}
	
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance();
	}

    protected function cadastrarPublicoControlado(PushDTO $objPushDTO) {
        try {
            return $this->cadastrarInterno($objPushDTO);
        } catch(Exception $e){
            throw new InfraException('Erro cadastrando registro do PUSH.', $e);
        }
    }
	
	protected function cadastrarControlado(PushDTO $objPushDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_cadastrar', __METHOD__, $objPushDTO);

            return $this->cadastrarInterno($objPushDTO);
		} catch(Exception $e){
			throw new InfraException('Erro cadastrando registro do PUSH.', $e);
		}
	}
	
	protected function alterarControlado(PushDTO $objPushDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_alterar', __METHOD__, $objPushDTO);

			$objPushBD = new PushBD($this->getObjInfraIBanco());
			
			return $objPushBD->alterar($objPushDTO);
		} catch(Exception $e){
			throw new InfraException('Erro alterando registro do PUSH.', $e);
		}
	}
	
	protected function excluirControlado($arrObjPushDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_excluir', __METHOD__, $arrObjPushDTO);
			
			$objInfraException = new InfraException();
			if(count($arrObjPushDTO)> 0) {
				$objPushBD = new PushBD($this->getObjInfraIBanco());
				for($i = 0; $i < count($arrObjPushDTO); $i ++) {
					$objPushDTO = new PushDTO();
					$objPushDTO->setStrChave($arrObjPushDTO[$i]->getStrChave());
					$objPushDTO->retTodos();
					$objPushDTO = $this->consultar($objPushDTO);
					if ($objPushDTO == null) {
					    $objInfraException->adicionarValidacao('Processo não encontrado.');
					    $objInfraException->lancarValidacoes();
					}
					$objPushBD->excluir($objPushDTO);
				}
			}
		} catch(Exception $e){
			throw new InfraException('Erro excluindo registro do PUSH.', $e);
		}
	}
	
	protected function listarConectado(PushDTO $objPushDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_listar', __METHOD__, $objPushDTO);

			$objPushBD = new PushBD($this->getObjInfraIBanco());
			
			return $objPushBD->listar($objPushDTO);
		} catch(Exception $e){
			throw new InfraException('Erro listando registro do PUSH.', $e);
		}
	}
	
	protected function consultarConectado(PushDTO $objPushDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_consultar', __METHOD__, $objPushDTO);
			
			$objPushBD = new PushBD($this->getObjInfraIBanco());
			
			return $objPushBD->consultar($objPushDTO);
		} catch(Exception $e){
			throw new InfraException('Erro consultando registro do PUSH.', $e);
		}
	}
	
	protected function pesquisarConectado(PushDTO $objPushDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('push_listar', __METHOD__, $objPushDTO);

            if($objPushDTO->isSetStrNumeroProtocoloFormatado())
                $objPushDTO->setStrNumeroProtocoloFormatado('%' . $objPushDTO->getStrNumeroProtocoloFormatado() . '%', InfraDTO::$OPER_LIKE);

			if($objPushDTO->isSetStrNome())
				$objPushDTO->setStrNome('%' . $objPushDTO->getStrNome() . '%', InfraDTO::$OPER_LIKE);
			
			if($objPushDTO->isSetStrEmail())
				$objPushDTO->setStrEmail('%' . $objPushDTO->getStrEmail() . '%', InfraDTO::$OPER_LIKE);
			
			$objPushBD = new PushBD($this->getObjInfraIBanco());
			
			return $objPushBD->listar($objPushDTO);
		} catch(Exception $e){
			throw new InfraException('Erro pesquisando registro do PUSH.', $e);
		}
	}
	
	private function recuperarUnidade($numIdUnidade) {
		$objUnidadeDTO = new UnidadeDTO();
		$objUnidadeDTO->retStrSigla();
		$objUnidadeDTO->setNumIdUnidade($numIdUnidade);
			
		$objUnidadeRN = new UnidadeRN();
		return $objUnidadeRN->consultarRN0125($objUnidadeDTO);
	}

	private function montarListaInteressados($objPushDTO){
		$strListaInteressados = '';
		$objParticipanteDTO = new ParticipanteDTO();
		$objParticipanteDTO->setDblIdProtocolo($objPushDTO->getDblIdProcedimento());
		$objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
		$objParticipanteDTO->retStrNomeContato();
		$objParticipanteRN = new ParticipanteRN();
		$arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);
		foreach($arrObjParticipanteDTO as $objParticipanteDTO){
			$strListaInteressados .= $objParticipanteDTO->getStrNomeContato() . "\n";
		}
		return $strListaInteressados;
	}
	
	private function montarListaAndamentos($arrObjAtividadeDTO,$objPushDTO) {
		$strAtividades = '';
		$arrIdAtividade = array();
		foreach($arrObjAtividadeDTO as $objAtividadeDTO){
			$arrIdAtividade[] = $objAtividadeDTO->getNumIdAtividade();
		}
	    $objProcedimentoRN = new ProcedimentoRN();
    	$objProcedimentoHistoricoDTO = new ProcedimentoHistoricoDTO();
    	$objProcedimentoHistoricoDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
    	$objProcedimentoHistoricoDTO->setStrStaHistorico(ProcedimentoRN::$TH_TOTAL);
    	$objProcedimentoDTORet = $objProcedimentoRN->consultarHistoricoRN1025($objProcedimentoHistoricoDTO);

    	foreach($objProcedimentoDTORet->getArrObjAtividadeDTO() as $objAtividadeDTORet){
    		if(in_array($objAtividadeDTORet->getNumIdAtividade(), $arrIdAtividade))
    			$strAtividades .= $objAtividadeDTORet->getDthAbertura() . ' - ' . $objAtividadeDTORet->getStrSiglaUnidadeOrigem() . ' - ' . strip_tags($objAtividadeDTORet->getStrNomeTarefa() . ($objAtividadeDTORet->getNumIdTarefa() == TarefaRN::$TI_PROCESSO_REMETIDO_UNIDADE?' para a unidade ' . $objAtividadeDTORet->getStrSiglaUnidade():'')) . "\n ";
    	}

		return $strAtividades;
	}
	
	public function recuperarHistoricoAndamentosProtocolo($objPushDTO) {
		if($objPushDTO->isSetArrObjAtividadesDTO())
			$arrObjAtividades = $objPushDTO->getArrObjAtividadesDTO();
		else {
			$objProcedimentoHistoricoDTO = new ProcedimentoHistoricoDTO();
			$objProcedimentoHistoricoDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
			$objProcedimentoHistoricoDTO->setStrStaHistorico(ProcedimentoRN::$TH_TOTAL);
			$objProcedimentoHistoricoDTO->setStrSinGerarLinksHistorico('N');
			$objProcedimentoRN = new ProcedimentoRN();
			$objProcedimentoDTO = $objProcedimentoRN->consultarHistoricoRN1025($objProcedimentoHistoricoDTO);
			$arrObjAtividades = $objProcedimentoDTO->getArrObjAtividadeDTO();
		}

		$arrObjEventosPush = $objPushDTO->getArrObjEventosHabilitadosDTO();

		$arrObjAtividadesPush = array();
		foreach($arrObjAtividades as $objAtividadeDTO) {
		    foreach ($arrObjEventosPush as $objEventoPushDTO) {
				if($objAtividadeDTO->getNumIdTarefa() == $objEventoPushDTO->getNumIdTarefa()) {
					$arrObjAtividadesPush[] = $objAtividadeDTO;
					break;
				}
		    }
		}

		return $arrObjAtividadesPush;
	}
	
	private function montarLinkMaisDetalhes($objPushDTO) {
		$strParametrosCriptografadosProcesso = MdPesqCriptografia::criptografa('acao_externa=md_pesq_processo_exibir&id_orgao_acesso_externo=0&id_procedimento='.$objPushDTO->getDblIdProcedimento());
		$strUrlPesquisaProcesso = '/md_pesq_processo_exibir.php?'.$strParametrosCriptografadosProcesso;
		$strHref = ConfiguracaoSEI::getInstance()->getValor('SEI','URL') . '/modulos/pesquisa' . PUSHUtil::prepararUrl($strUrlPesquisaProcesso);
		return $strHref;
	}
	
	private function montarLinkDescadastramento($objPushDTO) {
		$strParametrosCriptografadosProcesso = PUSHCriptografia::criptografa('acao_externa=push_descadastrar&id_orgao_acesso_externo=0&push_chave='.$objPushDTO->getStrChave());
		$strUrlPesquisaProcesso = '/push_descadastrar_email.php?'.$strParametrosCriptografadosProcesso;
		$strHref = ConfiguracaoSEI::getInstance()->getValor('SEI','URL') . '/modulos/PUSH' . SessaoSEI::getInstance()->assinarLink(PUSHUtil::prepararUrl($strUrlPesquisaProcesso));
		return $strHref;
	}

	private function montarLinkAutenticacao($objPushDTO) {
		$strParametrosCriptografadosProcesso = PUSHCriptografia::criptografa('acao_externa=push_autenticar&id_orgao_acesso_externo=0&push_chave='.$objPushDTO->getStrChave());
		$strUrlPesquisaProcesso = '/push_autenticar_email.php?'.$strParametrosCriptografadosProcesso;
		$strHref = ConfiguracaoSEI::getInstance()->getValor('SEI','URL') . '/modulos/PUSH' . SessaoSEI::getInstance()->assinarLink(PUSHUtil::prepararUrl($strUrlPesquisaProcesso));
		return $strHref;
	}
	
	protected function enviarEmailsPeriodicoConectado() {
		$objPushDTO = new PushDTO();
		$objPushDTO->setDthUltimoEnvioEmail(InfraData::getStrDataHoraAtual(),InfraDTO::$OPER_MENOR_IGUAL);
		$objPushDTO->retTodos(true);
		$objPushDTO->retStrNumeroProtocoloFormatado();
		$objPushRN = new PushRN();
		$arrObjPushDTO = $objPushRN->listar($objPushDTO);
		
		foreach($arrObjPushDTO as $objPushDTO){
			$objPushNotificacaoRN = new PushNotificacaoRN();
			$objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
			$objAtividadeRN = new AtividadeRN();
			$objEmailSistemaRN = new EmailSistemaRN();

			$objProcedimentoDTO = new ProcedimentoDTO();
			$objProcedimentoDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
			$objProcedimentoDTO->retTodos();
			$objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();
			$objProcedimentoRN = new ProcedimentoRN();
			$objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

			if($objProcedimentoDTO != null){
				if($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() != ProtocoloRN::$NA_SIGILOSO){
					$objPushBloqueioNotificacaoDTO = new PushBloqueioNotificacaoDTO();
					$objPushBloqueioNotificacaoDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
					$objPushBloqueioNotificacaoDTO->retTodos();
					$objPushBloqueioNotificacaoDTO = $objPushBloqueioNotificacaoRN->consultar($objPushBloqueioNotificacaoDTO);
					if($objPushBloqueioNotificacaoDTO == null){
						$objPushNotificacaoDTO = new PushNotificacaoDTO();
						$objPushNotificacaoDTO->setNumIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
						$objPushNotificacaoDTO->retTodos();
						$objPushNotificacaoDTO = $objPushNotificacaoRN->consultar($objPushNotificacaoDTO);
						if($objPushNotificacaoDTO != null){
							if(($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO && $objPushNotificacaoDTO->getStrSinNotificaRestrito() == 'S') || $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO){
								$arrIdTarefas = array();
								foreach($objPushNotificacaoDTO->getArrObjTarefaDTO() as $objTarefaDTO){
									$arrIdTarefas[] = $objTarefaDTO->getNumIdTarefa();
								}
								$objAtividadeDTO = new AtividadeDTO();
								$objAtividadeDTO->setDblIdProtocolo($objPushDTO->getDblIdProcedimento());
								$objAtividadeDTO->setNumIdTarefa($arrIdTarefas,InfraDTO::$OPER_IN);
								$objAtividadeDTO->setDthAbertura($objPushDTO->getDthUltimoEnvioEmail(),InfraDTO::$OPER_MAIOR_IGUAL);
								$objAtividadeDTO->retTodos();
								$arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);
								if(count($arrObjAtividadeDTO) > 0){
									$objEmailSistemaDTO = new EmailSistemaDTO();
									$objEmailSistemaDTO->setNumIdEmailSistema($objPushNotificacaoDTO->getNumIdEmailSistema());
									$objEmailSistemaDTO->retTodos();
									$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

									$strAssunto = $this->substituirVariaveisEmail($objEmailSistemaDTO->getStrAssunto(), $objPushDTO);
			                        $strDe = PUSHUtil::montarRementente($objEmailSistemaDTO->getStrDe(), SessaoSEI::getStrSiglaOrgaoSistema());
			                        $strPara = $this->substituirVariaveisEmail($objEmailSistemaDTO->getStrPara(), $objPushDTO);
			                        $strCorpo = $this->substituirVariaveisEmail($objEmailSistemaDTO->getStrConteudo(), $objPushDTO, $arrObjAtividadeDTO);

			                        $objEmailDTO = new EmailDTO();
			                        $objEmailDTO->setStrDe($strDe);
			                        $objEmailDTO->setStrPara($strPara);
			                        $objEmailDTO->setStrAssunto($strAssunto);
			                        $objEmailDTO->setStrMensagem($strCorpo);
			                        EmailRN::processar(array($objEmailDTO));

			                        $objPushDTO->setDthUltimoEnvioEmail(InfraData::getStrDataHoraAtual());
			                        $objPushRN->alterar($objPushDTO);
			                    }else{
			                    	//Caso não existam novos andamentos para serem enviados.
			                    }
							}else{
								//Caso o Processo assinado seja restrito e a configuração da Notificação não permita envido de andamentos de processos restritos.
								$objAtividadeDTO = new AtividadeDTO();
								$objAtividadeDTO->setDblIdProtocolo($objPushDTO->getDblIdProcedimento());
								$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_GLOBAL);
								$objAtividadeDTO->setDthAbertura($objPushDTO->getDthUltimoEnvioEmail(),InfraDTO::$OPER_MAIOR_IGUAL);
								$objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
								$objAtividadeDTO->retTodos();
								$arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);
								if(count($arrObjAtividadeDTO) > 0){
									$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
									$objAtributoAndamentoDTO->setNumIdAtividade($arrObjAtividadeDTO[0]->getNumIdAtividade());
									$objAtributoAndamentoDTO->setStrNome('NIVEL_ACESSO');
									$objAtributoAndamentoDTO->retTodos();

									$objAtributoAndamentoRN = new AtributoAndamentoRN();
									$objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
									if($objAtributoAndamentoDTO != null && $objAtributoAndamentoDTO->getStrIdOrigem() == ProtocoloRN::$NA_RESTRITO){

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
							}
						}else{
							//Caso o Processo assinado seja de um tipo não configurado em notificações.
							$objAtividadeDTO = new AtividadeDTO();
							$objAtividadeDTO->setDblIdProtocolo($objPushDTO->getDblIdProcedimento());
							$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_ALTERACAO_TIPO_PROCESSO);
							$objAtividadeDTO->setDthAbertura($objPushDTO->getDthUltimoEnvioEmail(),InfraDTO::$OPER_MAIOR_IGUAL);
							$objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
							$objAtividadeDTO->retTodos();
							$arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);
							if(count($arrObjAtividadeDTO) > 0){
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
					}else{
						//Caso o Processo assinado esteja configurado como bloquado nas configurações do PUSH.
					}
				}else{
					//Caso o Processo assinado seja sigiloso.
					$objAtividadeDTO = new AtividadeDTO();
					$objAtividadeDTO->setDblIdProtocolo($objPushDTO->getDblIdProcedimento());
					$objAtividadeDTO->setNumIdTarefa(TarefaRN::$TI_ALTERACAO_NIVEL_ACESSO_GLOBAL);
					$objAtividadeDTO->setDthAbertura($objPushDTO->getDthUltimoEnvioEmail(),InfraDTO::$OPER_MAIOR_IGUAL);
					$objAtividadeDTO->setOrdDthAbertura(InfraDTO::$TIPO_ORDENACAO_DESC);
					$objAtividadeDTO->retTodos();
					$arrObjAtividadeDTO = $objAtividadeRN->listarRN0036($objAtividadeDTO);
					if(count($arrObjAtividadeDTO) > 0){
						$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
						$objAtributoAndamentoDTO->setNumIdAtividade($arrObjAtividadeDTO[0]->getNumIdAtividade());
						$objAtributoAndamentoDTO->setStrNome('NIVEL_ACESSO');
						$objAtributoAndamentoDTO->retTodos();

						$objAtributoAndamentoRN = new AtributoAndamentoRN();
						$objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
						if($objAtributoAndamentoDTO != null && $objAtributoAndamentoDTO->getStrIdOrigem() == ProtocoloRN::$NA_SIGILOSO){

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
				}
			}else{
				//Caso o processo assinado não exista na base de dados.
			}
		}
	}
	
	protected function recuperarAtividadesIdsEmailControleConectado($arrObjPushEmailControleDTO) {
	    $arrObjAtividadesDTO = array();
	    foreach ($arrObjPushEmailControleDTO as $objPushEmailControleDTO)
	        array_push($arrObjAtividadesDTO, $objPushEmailControleDTO->getNumIdAtividade());
	    return $arrObjAtividadesDTO;
	}
	
	protected function gravarEmailControleConectado() {
		$objPushDTO = new PushDTO();
		$objPushDTO->retTodos(true);
		$objPushRN = new PushRN();
		$arrObjPushDTO = $objPushRN->listar($objPushDTO);
		
		foreach ($arrObjPushDTO as $objPushDTO) {
			if(!empty($objPushDTO->getDblIdProcedimento())) {
				$arrObjPushNotificacao = $this->recuperarNotificacoesPush($objPushDTO);

				$arrObjEventos = $this->recuperarEventosHabilitados($arrObjPushNotificacao);
				
				$objPushDTO->setArrObjEventosHabilitadosDTO($arrObjEventos);
				
				$arrObjAtividadesDTO = $objPushRN->recuperarHistoricoAndamentosProtocolo($objPushDTO);
				$objAtividadeRecenteDTO = $arrObjAtividadesDTO[0];
				
				if(!empty($arrObjAtividadesDTO)) {
				    foreach ($arrObjAtividadesDTO as $objAtividadeDTO) {
				        $objPushEmailControleDTO = new PushEmailControleDTO();
				        $objPushEmailControleDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
				        $objPushEmailControleDTO->setNumIdAtividade($objAtividadeDTO->getNumIdAtividade());

				        $objPushEmailControleDTO->setStrBloqueado($this->deveBloquearNotificacao($objAtividadeDTO,$arrObjPushNotificacao) ? 'S' : 'N');
				        $objPushEmailControleDTO->setStrEnviado('N');
				        $objPushEmailControleRN = new PushEmailControleRN();
				        $objPushEmailControleRN->cadastrar($objPushEmailControleDTO);
				    }
				}
			}
		}
	}
	
	private function recuperarProcedimento($objPushDTO) {
		$objProcedimentoDTO = new ProcedimentoDTO();
		$objProcedimentoDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
		$objProcedimentoDTO->retTodos();
		$objProcedimentoRN = new ProcedimentoRN();
		return $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
	}
	
	public function recuperarNotificacoesPush($objPushDTO) {
		$objProcedimentoDTO = $this->recuperarProcedimento($objPushDTO);
		
		$objPushNotificacaoDTO = new PushNotificacaoDTO();
		$objPushNotificacaoDTO->setNumIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
		$objPushNotificacaoDTO->retTodos(true);
		$objPushNotificacaoRN = new PushNotificacaoRN();
		return $objPushNotificacaoRN->listar($objPushNotificacaoDTO);
	}
	
	private function recuperarTemplatesEmail($arrObjPushNotificacao) {
		$arrObjTemplatesEmail = array();
		foreach ($arrObjPushNotificacao as $objPushNotificacaoDTO) {
			$numIdTemplateEmail = $objPushNotificacaoDTO->getNumIdTemplateEmail();
			if($arrObjTemplatesEmail[$numIdTemplateEmail])
				continue;
				$objEmailSistemaDTO = new EmailSistemaDTO();
				$objEmailSistemaDTO->retTodos();
				$objEmailSistemaDTO->setNumIdEmailSistema($numIdTemplateEmail);
				$objEmailSistemaRN = new EmailSistemaRN();
				$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);
				$arrObjTemplatesEmail[$numIdTemplateEmail] = $objEmailSistemaDTO;
		}
		return $arrObjTemplatesEmail;
	}
	
	public function recuperarEventosHabilitados($arrObjPushNotificacao) {
		$arrObjEventos = array();
		foreach ($arrObjPushNotificacao as $objPushNotificacaoDTO) {
			$numIdTarefa = $objPushNotificacaoDTO->getNumIdTarefa();
			if($arrObjEventos[$numIdTarefa])
				continue;
				$objTarefaDTO = new TarefaDTO();
				$objTarefaDTO->retStrNome();
				$objTarefaDTO->retNumIdTarefa();
				$objTarefaDTO->setNumIdTarefa($numIdTarefa);
				$objTarefaRN = new TarefaRN();
				$objTarefaDTO = $objTarefaRN->consultar($objTarefaDTO);
				$arrObjEventos[$numIdTarefa] = $objTarefaDTO;
		}
		return $arrObjEventos;
	}

    protected function deveBloquearNotificacao($objAtividadeDTO, $arrObjPushNotificacao) {

		$objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
		$objRelProtocoloProtocoloDTO->setStrStaAssociacao(array(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_RELACIONADO,RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_DESANEXADO,RelProtocoloProtocoloRN::$TA_DOCUMENTO_MOVIDO),InfraDTO::$OPER_NOT_IN);
		$objRelProtocoloProtocoloDTO->setDblIdProtocolo2($objAtividadeDTO->getDblIdProtocolo());
		$objRelProtocoloProtocoloDTO->retDblIdProtocolo1();

		$objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
		$objRelProtocoloProtocoloDTO = $objRelProtocoloProtocoloRN->consultarRN0841($objRelProtocoloProtocoloDTO);

		if($objRelProtocoloProtocoloDTO != null)
			$idProcedimento = $objRelProtocoloProtocoloDTO->getDblIdProtocolo1();
		else
			$idProcedimento = $objAtividadeDTO->getDblIdProtocolo();

		$objProtocoloDTO = $this->getProtocolo($idProcedimento);

		$bolBloquearRestritoSigiloso = false;

		foreach($arrObjPushNotificacao as $objPushNotificacao){
			if($objPushNotificacao->getNumIdTarefa() == $objAtividadeDTO->getNumIdTarefa() && $objPushNotificacao->getNumIdTipoProcedimento() == $objProtocoloDTO->getNumIdTipoProcedimentoProcedimento()){

				$bolBloquearRestritoSigiloso = ($objPushNotificacao->getStrNotificaRestrito() == 'N' && $objProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_RESTRITO) || $objProtocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO;
			}
		}
		return $this->notificacaoDoProcedimentoEstaoBloqueadas($idProcedimento) || $bolBloquearRestritoSigiloso;
    }

    protected function notificacaoDoProcedimentoEstaoBloqueadas($idProcedimento) {
        $objPushBloqueioNotificacaoDTO = new PushBloqueioNotificacaoDTO();
        $objPushBloqueioNotificacaoDTO->retTodos();
        $objPushBloqueioNotificacaoDTO->setDblIdProcedimento($idProcedimento);
        $objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
        $arrObjPushBloqueioNotificacaoDTO = $objPushBloqueioNotificacaoRN->listar($objPushBloqueioNotificacaoDTO);

        return count($arrObjPushBloqueioNotificacaoDTO) > 0;
    }

    public function getProtocolo($idProcedimento) {
        $objProtocoloDTO = new ProtocoloDTO();
        $objProtocoloDTO->retTodos();
        $objProtocoloDTO->setDblIdProtocolo($idProcedimento);
        $objProtocoloRN = new ProtocoloRN();
        $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->retTodos();
        $objProcedimentoDTO->setDblIdProcedimento($idProcedimento);
        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
        
        $objProtocoloDTO->setNumIdTipoProcedimentoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
        
        return $objProtocoloDTO;
    }

    protected function procedimentoEhSigiloso($objProtocoloDTO) {
        $nivelAcessoSigiloso = '2';

        return $objProtocoloDTO->getStrStaNivelAcessoGlobal() == $nivelAcessoSigiloso;
    }

    public function procedimentoEhRestrito($objProtocoloDTO) {
        $nivelAcessoRestrito = '1';

        return $objProtocoloDTO->getStrStaNivelAcessoGlobal() == $nivelAcessoRestrito;
    }
    
    private function cadastrarInterno($objPushDTO) {

        $objInfraException = new InfraException();
        $objPushBD = new PushBD($this->getObjInfraIBanco());

        $objPushExistenteDTO = new PushDTO();
        $objPushExistenteDTO->setDblIdProcedimento($objPushDTO->getDblIdProcedimento());
        $objPushExistenteDTO->setStrEmail($objPushDTO->getStrEmail());
        $objPushExistenteDTO->retTodos();
        $objPushExistenteRN = new PushRN();
        $objPushExistenteDTO = $objPushExistenteRN->consultar($objPushExistenteDTO);

        if(!empty($objPushExistenteDTO)) {
            $objInfraException->adicionarValidacao('Já existe um registro no PUSH com o e-mail informado para o processo escolhido');
            $objInfraException->lancarValidacoes();
        }
        $objPushDTO->setStrChave(md5(uniqid(rand(), true)));
        $objPushDTO->setDthUltimoEnvioEmail('31/12/9999 23:59:59');
        $objPushDTO = $objPushBD->cadastrar($objPushDTO);
        
        $objPushCompletoDTO = new PushDTO();
        $objPushCompletoDTO->setNumIdPush($objPushDTO->getNumIdPush());
        $objPushCompletoDTO->retTodos(true);
        $objPushCompletoDTO = $this->consultar($objPushCompletoDTO);

        $this->enviarEmailConfirmacaoCadastro($objPushCompletoDTO);

        return $objPushDTO;
    }
    
    protected function enviarEmailConfirmacaoCadastro(PushDTO $objPushDTO) {
        $objPushConfiguracaoDTO = new PushConfiguracaoDTO();
        $objPushConfiguracaoDTO->setStrNome('ID_TEMPLATE_CONFIRMACAO_CADASTRO_PROCESSO_PUSH');
        $objPushConfiguracaoDTO->retTodos();
        $objPushConfiguracaoRN = new PushConfiguracaoRN();
        $objPushConfiguracaoDTO = $objPushConfiguracaoRN->consultar($objPushConfiguracaoDTO);
        
        $objEmailSistemaDTO = new EmailSistemaDTO();
        $objEmailSistemaDTO->setNumIdEmailSistema($objPushConfiguracaoDTO->getStrValor());
        $objEmailSistemaDTO->retTodos();
        $objEmailSistemaRN = new EmailSistemaRN();
        $objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);
        
        $strAssunto = $this->substituirVariaveisEmail($objEmailSistemaDTO->getStrAssunto(), $objPushDTO);
        $strDe = PUSHUtil::montarRementente($objEmailSistemaDTO->getStrDe(), SessaoSEI::getStrSiglaOrgaoSistema());
        $strPara = $this->substituirVariaveisEmail($objEmailSistemaDTO->getStrPara(), $objPushDTO);
        $strCorpo = $this->substituirVariaveisEmail($objEmailSistemaDTO->getStrConteudo(), $objPushDTO);
        
        $objEmailDTO = new EmailDTO();
        $objEmailDTO->setStrDe($strDe);
        $objEmailDTO->setStrPara($strPara);
        $objEmailDTO->setStrAssunto($strAssunto);
        $objEmailDTO->setStrMensagem($strCorpo);
        
        EmailRN::processar(array($objEmailDTO));
    }

    public function substituirVariaveisEmail($strTemplate, $objPushDTO, $arrObjAtividadeDTO=null){
    	$strTemplate = str_replace('@nome_contato@', $objPushDTO->getStrNome(), $strTemplate);
        $strTemplate = str_replace('@email_usuario_externo@', $objPushDTO->getStrEmail(), $strTemplate);
        $strTemplate = str_replace('@link_descadastramento@', $this->montarLinkDescadastramento($objPushDTO), $strTemplate);
        $strTemplate = str_replace('@link_autenticacao@', $this->montarLinkAutenticacao($objPushDTO), $strTemplate);
        $strTemplate = str_replace('@processo@', $objPushDTO->getStrNumeroProtocoloFormatado(), $strTemplate);
        $strTemplate = str_replace('@email_usuario_externo@', $objPushDTO->getStrEmail(), $strTemplate);

        $strTemplate = str_replace('@lista_interessados@', $this->montarListaInteressados($objPushDTO), $strTemplate);
        $strTemplate = str_replace('@link_mais_detalhes@', $this->montarLinkMaisDetalhes($objPushDTO), $strTemplate);
		if($arrObjAtividadeDTO != null)
        	$strTemplate = str_replace('@lista_andamentos@', $this->montarListaAndamentos($arrObjAtividadeDTO,$objPushDTO), $strTemplate);
        
        return $strTemplate;	
    }

    public function processarControladorAjaxPesquisaPublica($idProcedimento,$nome,$email){

        $xml = null;
        try{

          $arrResposta['RespostaCadastro'] = '';

          $objPushDTO = new PushDTO();
          $objPushDTO->setDblIdProcedimento($idProcedimento);
          $objPushDTO->setStrNome($nome);
          $objPushDTO->setStrEmail($email);

          if($objPushDTO->getDblIdProcedimento() != '' && InfraUtil::validarEmail($objPushDTO->getStrEmail()) && $objPushDTO->getStrNome() != ''){
            $objPushRN = new PushRN();
            $objPushRN->cadastrar($objPushDTO);
            $arrResposta['RespostaCadastro'] = 'Registro incluído no PUSH. Um e-mail será enviado para o endereço informado, com o andamento do processo selecionado';
          }
          else
            $arrResposta['RespostaCadastro'] = 'Dados de cadastro inválidos';

        }catch(Exception $e){
          if( $e->__toString() == 'Já existe um registro no PUSH com o e-mail informado para o processo escolhido')
            $arrResposta['RespostaCadastro'] = $e->__toString();
          else
            $arrResposta['RespostaCadastro'] = "Ocorreu um erro durante o cadastro no PUSH";
        }
        $xml = InfraAjax::gerarXMLComplementosArray($arrResposta);
        
        return $xml;
    }

    public function sinPermitidaAssinatura($idProcedimento){
    	$objProcedimentoDTO = new ProcedimentoDTO;
    	$objProcedimentoDTO->setDblIdProcedimento($idProcedimento);
    	$objProcedimentoDTO->retTodos();
    	$objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();
    	$objProcedimentoRN = new ProcedimentoRN;
    	$objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

    	$objPushBloqueioNotificacaoDTO = new PushBloqueioNotificacaoDTO();
        $objPushBloqueioNotificacaoDTO->setDblIdProcedimento($idProcedimento);
        $objPushBloqueioNotificacaoDTO->retTodos();
        $objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
        $objPushBloqueioNotificacaoDTO = $objPushBloqueioNotificacaoRN->consultar($objPushBloqueioNotificacaoDTO);

        $objPushNotificacaoDTO = new PushNotificacaoDTO();
		$objPushNotificacaoDTO->setNumIdTipoProcedimento($objProcedimentoDTO->getNumIdTipoProcedimento());
		$objPushNotificacaoDTO->retTodos();
		$objPushNotificacaoRN = new PushNotificacaoRN();
		$objPushNotificacaoDTO = $objPushNotificacaoRN->consultar($objPushNotificacaoDTO);

		$bolReturn = ($objProcedimentoDTO != null &&
					$objPushNotificacaoDTO != null) && 
					$objPushBloqueioNotificacaoDTO == null &&
					($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO ||
					($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO &&
					$objPushNotificacaoDTO->getStrSinNotificaRestrito() == 'S'));

		return $bolReturn;
    }
}
?>