<?
try {
	require_once dirname(__FILE__).'/../../SEI.php';

    session_start();

    SessaoSEIExterna::getInstance()->validarLink();
	
	$objPushDTO = new PushDTO();
	
	$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
	$strTextoAceiteTermosInclusao = $objInfraParametro->getValor('SEI_PUSH_MENSAGEM_ACEITE_TERMOS_INCLUSAO');
	
	switch($_GET['acao']){
	
		case 'push_cadastrar':
			$strTitulo = 'Incluir no PUSH';

			$objPushDTO->setDblIdProcedimento($_GET['id_procedimento']);
			$objPushDTO->setStrNome($_POST['txtNome']);
			$objPushDTO->setStrEmail($_POST['txtEmail']);
			
			if(isset($_POST['sbmCadastrarPush'])){
				try {
					$objPushRN = new PushRN();

                    $strLocation = '/sei' . SessaoSEIExterna::getInstance()->assinarLink('/controlador_externo.php?acao=usuario_externo_controle_acessos&acao_origem_externa=push_cadastrar&id_orgao_acesso_externo=0');

					if (!$objPushRN->sinPermitidaAssinatura($_GET['id_procedimento']))
                        alert("Não é permitido o envio de andamentos deste processo.", $strLocation);
                    else {
                        $objPushDTO = $objPushRN->cadastrar($objPushDTO);

                        alert("Registro incluído no PUSH. Um e-mail será enviado para o endereço informado, com o andamento do processo selecionado.", $strLocation);
                    }

					die();
				} catch(Exception $e){
                    PaginaSEIExterna::getInstance()->processarExcecao($e);
				}
			}
			break;
		default:
			throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
	}
	
	$objParametroPesquisaDTO = new MdPesqParametroPesquisaDTO();
	$objParametroPesquisaDTO->retStrNome();
	$objParametroPesquisaDTO->retStrValor();
	
	$objParametroPesquisaRN = new MdPesqParametroPesquisaRN();
	$arrObjParametroPesquisaDTO = $objParametroPesquisaRN->listar($objParametroPesquisaDTO);
	
	$arrParametroPesquisaDTO = InfraArray::converterArrInfraDTO($arrObjParametroPesquisaDTO, 'Valor', 'Nome');
	
	$bolPesquisaProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaDocumentoProcessoPublico = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_PUBLICO] == 'S' ? true : false;
	$bolListaAndamentoProcessoPublico = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_PUBLICO] == 'S' ? true : false;
	$bolLinkMetadadosProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_METADADOS_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaAndamentoProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_ANDAMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
	$bolListaDocumentoProcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_LISTA_DOCUMENTO_PROCESSO_RESTRITO] == 'S' ? true : false;
	$txtDescricaoProcessoAcessoRestrito = $arrParametroPesquisaDTO[MdPesqParametroPesquisaRN::$TA_DESCRICAO_PROCEDIMENTO_ACESSO_RESTRITO];
	
	PaginaSEIExterna::getInstance()->setTipoPagina(PaginaSEIExterna::$TIPO_PAGINA_SEM_MENU);
	
	$dblIdProcedimento = $_GET['id_procedimento'];
	
	$objProcedimentoDTO = new ProcedimentoDTO();
	$objProcedimentoDTO->retStrNomeTipoProcedimento();
	$objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
	$objProcedimentoDTO->retDtaGeracaoProtocolo();
	$objProcedimentoDTO->retStrStaNivelAcessoGlobalProtocolo();
	$objProcedimentoDTO->retStrStaNivelAcessoLocalProtocolo();
	$objProcedimentoDTO->retNumIdHipoteseLegalProtocolo();
	
	$objProcedimentoDTO->setDblIdProcedimento($dblIdProcedimento);
	$objProcedimentoDTO->setStrSinDocTodos('S');
	$objProcedimentoDTO->setStrSinProcAnexados('S');
	
	$objProcedimentoRN = new ProcedimentoRN();
	$arr = $objProcedimentoRN->listarCompleto($objProcedimentoDTO);
	
	if(count($arr) == 0)
		die('Processo não encontrado.');
	
	$objProcedimentoDTO = $arr[0];
	
	if($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_SIGILOSO)
		die('Processo não encontrado.');
	
	if(! $bolLinkMetadadosProcessoRestrito || ! $bolPesquisaProcessoRestrito)
		if($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() != ProtocoloRN::$NA_PUBLICO)
			die('Processo não encontrado.');
		
	$objInteressadosParticipanteDTO = new ParticipanteDTO();
	$objInteressadosParticipanteDTO->retStrNomeContato();
	$objInteressadosParticipanteDTO->setDblIdProtocolo($dblIdProcedimento);
	$objInteressadosParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
	
	$objInteressadosParticipanteRN = new ParticipanteRN();
	
	$objInteressadosParticipanteDTO = $objInteressadosParticipanteRN->listarRN0189($objInteressadosParticipanteDTO);
	
	if(count($objInteressadosParticipanteDTO) == 0)
		$strInteressados = '&nbsp;';
	else {
		$strInteressados = '';
		foreach($objInteressadosParticipanteDTO as $objInteressadoParticipanteDTO)
			$strInteressados .= $objInteressadoParticipanteDTO->getStrNomeContato() . "<br /> ";
	}
		
	$strMensagemProcessoRestrito = '';
	$strHipoteseLegal = '';
	if($objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO && $bolLinkMetadadosProcessoRestrito) {
		
		$objHipoteseLegalDTO = new HipoteseLegalDTO();
		$objHipoteseLegalDTO->setNumIdHipoteseLegal($objProcedimentoDTO->getNumIdHipoteseLegalProtocolo());
		$objHipoteseLegalDTO->retStrBaseLegal();
		$objHipoteseLegalDTO->retStrNome();
		
		$objHipoteseLegalRN = new HipoteseLegalRN();
		$objHipoteseLegalDTO = $objHipoteseLegalRN->consultar($objHipoteseLegalDTO);
		
		if($objHipoteseLegalDTO != null) {
			$strHipoteseLegal .= '<img src="/infra_css/imagens/espaco.gif">';
			$strHipoteseLegal .= '<img src="imagens/sei_chave_restrito.gif" align="absbottom"  title="Acesso Restrito.&#13' . PaginaSEIExterna::getInstance()->formatarXHTML($objHipoteseLegalDTO->getStrNome() . '(' . $objHipoteseLegalDTO->getStrBaseLegal() . ')') . '">';
		}
		$strMensagemProcessoRestrito = '<p style="font-size: 1.2em;"> ' . $txtDescricaoProcessoAcessoRestrito . '</p>';
	}
	
	$strResultadoCabecalho = '';
	$strResultadoCabecalho .= '<table id="tblCabecalho" width="99.3%" class="infraTable" summary="Cabeçalho de Processo" >'."\n";
	$strResultadoCabecalho .= '<tr><th class="infraTh" colspan="2">Autuação</th></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Processo:</td><td>'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().$strHipoteseLegal.'</td></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Tipo:</td><td>'.PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTO->getStrNomeTipoProcedimento()).'</td></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Data de Registro:</td><td>'.$objProcedimentoDTO->getDtaGeracaoProtocolo().'</td></tr>'."\n";
	$strResultadoCabecalho .= '<tr class="infraTrClara"><td width="20%">Interessados:</td><td> '.$strInteressados.'</td></tr>'."\n";
	$strResultadoCabecalho .= '</table>'."\n";
		
	$arrObjRelProtocoloProtocoloDTO = array();
	
	if($bolListaDocumentoProcessoPublico && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO)
		$arrObjRelProtocoloProtocoloDTO = $objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO();
	else if($bolListaDocumentoProcessoRestrito && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO)
		$arrObjRelProtocoloProtocoloDTO = $objProcedimentoDTO->getArrObjRelProtocoloProtocoloDTO();
	
	$objProtocoloPesquisaPublicaPaginacaoDTO = new MdPesqProtocoloPesquisaPublicaDTO();
	$objProtocoloPesquisaPublicaPaginacaoDTO->retTodos(true);
	PaginaSEIExterna::getInstance()->prepararOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Registro', InfraDTO::$TIPO_ORDENACAO_ASC);
	$arrObjProtocoloPesquisaPublicaDTO = array();
	
	$objDocumentoRN = new DocumentoRN();
	
	$numProtocolos = 0;
	$numDocumentosPdf = 0;
	$strCssMostrarAcoes = '.colunaAcoes {display:none;}' . "\n";
	
	$strThCheck = PaginaSEIExterna::getInstance()->getThCheck();
	
	foreach($arrObjRelProtocoloProtocoloDTO as $objRelProtocoloProtocoloDTO) {
		if($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO) {
			$objDocumentoDTO = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();
			
			if($objDocumentoRN->verificarSelecaoAcessoExterno($objDocumentoDTO)) {
				
				$objProtocoloPesquisaPublicaDTO = new MdPesqProtocoloPesquisaPublicaDTO();
				$objProtocoloPesquisaPublicaDTO->setStrNumeroSEI($objDocumentoDTO->getStrProtocoloDocumentoFormatado());
				$objProtocoloPesquisaPublicaDTO->setStrTipoDocumento(PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie() . ' ' . $objDocumentoDTO->getStrNumero()));
				
				if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_RECEBIDO) {
					
					$objAtributoAndamentoDTO = new AtributoAndamentoDTO();
					$objAtributoAndamentoDTO->setDblIdProtocoloAtividade($objProcedimentoDTO->getDblIdProcedimento());
					$objAtributoAndamentoDTO->setNumIdTarefaAtividade(TarefaRN::$TI_RECEBIMENTO_DOCUMENTO);
					$objAtributoAndamentoDTO->setStrNome("DOCUMENTO");
					$objAtributoAndamentoDTO->setStrIdOrigem($objDocumentoDTO->getDblIdDocumento());
					
					$objAtributoAndamentoDTO->retDthAberturaAtividade();
					
					$objAtributoAndamentoRN = new AtributoAndamentoRN();
					
					$objAtributoAndamentoDTO = $objAtributoAndamentoRN->consultarRN1366($objAtributoAndamentoDTO);
					
					if($objAtributoAndamentoDTO != null && $objAtributoAndamentoDTO->isSetDthAberturaAtividade()) {
						
						$dtaRecebimento = substr($objAtributoAndamentoDTO->getDthAberturaAtividade(), 0, 10);
						
						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($dtaRecebimento);
					} 
					else
						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objDocumentoDTO->getDtaGeracaoProtocolo());
					
					$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objDocumentoDTO->getDtaGeracaoProtocolo());
				} 
				else if($objDocumentoDTO->getStrStaProtocoloProtocolo() == ProtocoloRN::$TP_DOCUMENTO_GERADO) {
					
					$objAssinaturaDTO = new AssinaturaDTO();
					$objAssinaturaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
					$objAssinaturaDTO->setOrdNumIdAssinatura(InfraDTO::$TIPO_ORDENACAO_ASC);
					$objAssinaturaDTO->retDthAberturaAtividade();
					
					$objAssinaturaRN = new AssinaturaRN();
					$arrObjAssinaturaDTO = $objAssinaturaRN->listarRN1323($objAssinaturaDTO);
					
					if(is_array($arrObjAssinaturaDTO) && count($arrObjAssinaturaDTO) > 0) {
						$objAssinaturaDTO = $arrObjAssinaturaDTO[0];
						
						if($objAssinaturaDTO != null && $objAssinaturaDTO->isSetDthAberturaAtividade()) {
							$dtaAssinatura = substr($objAssinaturaDTO->getDthAberturaAtividade(), 0, 10);
							
							$objProtocoloPesquisaPublicaDTO->setDtaRegistro($dtaAssinatura);
							$objProtocoloPesquisaPublicaDTO->setDtaDocumento($dtaAssinatura);
						} 
						else {
							$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objDocumentoDTO->getDtaGeracaoProtocolo());
							$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objDocumentoDTO->getDtaGeracaoProtocolo());
						}
					} 
					else {
						$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objDocumentoDTO->getDtaGeracaoProtocolo());
						$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objDocumentoDTO->getDtaGeracaoProtocolo());
					}
				}
				
				$objProtocoloPesquisaPublicaDTO->setStrUnidade($objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo());
				$objProtocoloPesquisaPublicaDTO->setStrStaAssociacao($objRelProtocoloProtocoloDTO->getStrStaAssociacao());
				$objProtocoloPesquisaPublicaDTO->setObjDocumentoDTO($objDocumentoDTO);
				
				$arrObjProtocoloPesquisaPublicaDTO[] = $objProtocoloPesquisaPublicaDTO;
				$numProtocolos ++;
			}
		} 
		else if($objRelProtocoloProtocoloDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO) {
			$objProcedimentoDTOAnexado = $objRelProtocoloProtocoloDTO->getObjProtocoloDTO2();
			
			$objProtocoloPesquisaPublicaDTO = new MdPesqProtocoloPesquisaPublicaDTO();
			$objProtocoloPesquisaPublicaDTO->setStrNumeroSEI($objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado());
			$objProtocoloPesquisaPublicaDTO->setStrTipoDocumento(PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTOAnexado->getStrNomeTipoProcedimento()));
			$objProtocoloPesquisaPublicaDTO->setDtaDocumento($objProcedimentoDTOAnexado->getDtaGeracaoProtocolo());
			$objProtocoloPesquisaPublicaDTO->setDtaRegistro($objProcedimentoDTOAnexado->getDtaGeracaoProtocolo());
			$objProtocoloPesquisaPublicaDTO->setStrUnidade($objProcedimentoDTOAnexado->getStrSiglaUnidadeGeradoraProtocolo());
			$objProtocoloPesquisaPublicaDTO->setStrStaAssociacao($objRelProtocoloProtocoloDTO->getStrStaAssociacao());
			$objProtocoloPesquisaPublicaDTO->setObjProcedimentoDTO($objProcedimentoDTOAnexado);
			
			$arrObjProtocoloPesquisaPublicaDTO[] = $objProtocoloPesquisaPublicaDTO;
			
			$numProtocolos ++;
		}
	}
	
	if($numProtocolos > 0) {
		
		$strResultado = '<table id="tblDocumentos" width="99.3%" class="infraTable" summary="Lista de Documentos" >
  					  									<caption class="infraCaption" >' . PaginaSEIExterna::getInstance()->gerarCaptionTabela("Protocolos", $numProtocolos) . '</caption>
  					 										<tr>
                                  <th class="infraTh" width="1%">' . $strThCheck . '</th>
  					  										<th class="infraTh" width="15%">' . PaginaSEIExterna::getInstance()->getThOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Documento / Processo', 'NumeroSEI', $arrObjProtocoloPesquisaPublicaDTO, true) . '</th>
  					  										<th class="infraTh" width="15%">' . PaginaSEIExterna::getInstance()->getThOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Tipo de Documento', 'TipoDocumento', $arrObjProtocoloPesquisaPublicaDTO, true) . '</th>
  					  										<th class="infraTh" width="15%">' . PaginaSEIExterna::getInstance()->getThOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Data do Documento', 'Documento', $arrObjProtocoloPesquisaPublicaDTO, true) . '</th>
                                  							<th class="infraTh" width="15%">' . PaginaSEIExterna::getInstance()->getThOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Data de Registro', 'Registro', $arrObjProtocoloPesquisaPublicaDTO, true) . '</th>
  					  										<th class="infraTh" width="15%">' . PaginaSEIExterna::getInstance()->getThOrdenacao($objProtocoloPesquisaPublicaPaginacaoDTO, 'Unidade', 'Unidade', $arrObjProtocoloPesquisaPublicaDTO, true) . '</th>
  					  	
  					  									</tr>';
		
		foreach($arrObjProtocoloPesquisaPublicaDTO as $objProtocoloPesquisaPublicaDTO) {
			
			if($objProtocoloPesquisaPublicaDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_DOCUMENTO_ASSOCIADO) {
				
				$objDocumentoDTO = $objProtocoloPesquisaPublicaDTO->getObjDocumentoDTO();
				$urlCripografadaDocumeto = MdPesqCriptografia::criptografa('acao_externa=documento_exibir&id_documento=' . $objDocumentoDTO->getDblIdDocumento() . '&id_orgao_acesso_externo=0');
				$strLinkDocumento = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('md_pesq_documento_consulta_externa.php?' . $urlCripografadaDocumeto));
				
				$strResultado .= '<tr class="infraTrClara">';
				
				if($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO && $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO) {
					if($objDocumentoRN->verificarSelecaoGeracaoPdf($objDocumentoDTO))
						$strResultado .= '<td align="center">' . PaginaSEIExterna::getInstance()->getTrCheck($numDocumentosPdf ++, $objDocumentoDTO->getDblIdDocumento(), $objDocumentoDTO->getStrNomeSerie()) . '</td>';
					else
						$strResultado .= '<td>&nbsp;</td>';
				} 
				else
					$strResultado .= '<td>&nbsp;</td>';
				
				if($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO && $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO)
					$strResultado .= '<td align="center" style="padding-right:22px"><a href="javascript:void(0);" onclick="window.open(\'' . $strLinkDocumento . '\');" alt="' . PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie()) . '" title="' . PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie()) . '" class="ancoraPadraoAzul">' . $objDocumentoDTO->getStrProtocoloDocumentoFormatado() . '</a></td>';
				else {
					if($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO) {
						
						$strHipoteseLegalDocumento = '';
						$objProtocoloDocumentoDTO = new ProtocoloDTO();
						$objProtocoloDocumentoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
						$objProtocoloDocumentoDTO->retNumIdHipoteseLegal();
						
						$objProtocoloRN = new ProtocoloRN();
						$objProtocoloDocumentoDTO = $objProtocoloRN->consultarRN0186($objProtocoloDocumentoDTO);
						
						if($objProtocoloDocumentoDTO != null) {
							
							$objHipoteseLegaDocumentoDTO = new HipoteseLegalDTO();
							$objHipoteseLegaDocumentoDTO->setNumIdHipoteseLegal($objProtocoloDocumentoDTO->getNumIdHipoteseLegal());
							$objHipoteseLegaDocumentoDTO->retStrNome();
							$objHipoteseLegaDocumentoDTO->retStrBaseLegal();
							
							$objHipoteseLegalRN = new HipoteseLegalRN();
							$objHipoteseLegaDocumentoDTO = $objHipoteseLegalRN->consultar($objHipoteseLegaDocumentoDTO);
							
							if($objHipoteseLegaDocumentoDTO != null)
								$strHipoteseLegalDocumento .= $objHipoteseLegaDocumentoDTO->getStrNome() . '(' . $objHipoteseLegaDocumentoDTO->getStrBaseLegal() . ')';
						}
						
						$strResultado .= '<td align="center" ><span class="retiraAncoraPadraoAzul">' . $objDocumentoDTO->getStrProtocoloDocumentoFormatado() . '</span>';
						$strResultado .= '<img src="/infra_css/imagens/espaco.gif">';
						$strResultado .= '<img src="imagens/sei_chave_restrito.gif" align="absbottom"  title="Acesso Restrito.&#13' . PaginaSEIExterna::getInstance()->formatarXHTML($strHipoteseLegalDocumento) . '">';
						$strResultado .= '</td>';
					} 
					else
						$strResultado .= '<td align="center" style="padding-right:22px" ><span class="retiraAncoraPadraoAzul">' . $objDocumentoDTO->getStrProtocoloDocumentoFormatado() . '</span>';
				}
				
				$strResultado .= '<td align="center">' . PaginaSEIExterna::getInstance()->formatarXHTML($objDocumentoDTO->getStrNomeSerie() . ' ' . $objDocumentoDTO->getStrNumero()) . '</td>
  																	<td align="center">' . $objProtocoloPesquisaPublicaDTO->getDtaDocumento() . '</td>
  																	<td align="center">' . $objProtocoloPesquisaPublicaDTO->getDtaRegistro() . '</td>
  																	<td align="center"><a alt="' . $objDocumentoDTO->getStrDescricaoUnidadeGeradoraProtocolo() . '" title="' . $objDocumentoDTO->getStrDescricaoUnidadeGeradoraProtocolo() . '" class="ancoraSigla">' . $objDocumentoDTO->getStrSiglaUnidadeGeradoraProtocolo() . '</a></td>
  																	<td align="center" class="colunaAcoes">';
				
				$strResultado .= '</td></tr>';
			} else if($objProtocoloPesquisaPublicaDTO->getStrStaAssociacao() == RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO) {
				
				$strResultado .= '<tr class="infraTrClara">';
				$strResultado .= '<td>&nbsp;</td>';
				$strHipoteseLegalAnexo = '';
				$strProtocoloRestrito = '';
				
				$objProcedimentoDTOAnexado = $objProtocoloPesquisaPublicaDTO->getObjProcedimentoDTO();
				
				if($objProcedimentoDTOAnexado->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_RESTRITO) {
					
					$strHipoteseLegalAnexo = '';
					$objProtocoloAnexoDTO = new ProtocoloDTO();
					$objProtocoloAnexoDTO->setDblIdProtocolo($objProcedimentoDTOAnexado->getDblIdProcedimento());
					$objProtocoloAnexoDTO->retNumIdHipoteseLegal();
					
					$objProtocoloRN = new ProtocoloRN();
					$objProtocoloAnexoDTO = $objProtocoloRN->consultarRN0186($objProtocoloAnexoDTO);
					
					if($objProtocoloAnexoDTO != null) {
						
						$objHipoteseLegaAnexoDTO = new HipoteseLegalDTO();
						$objHipoteseLegaAnexoDTO->setNumIdHipoteseLegal($objProtocoloAnexoDTO->getNumIdHipoteseLegal());
						$objHipoteseLegaAnexoDTO->retStrNome();
						$objHipoteseLegaAnexoDTO->retStrBaseLegal();
						
						$objHipoteseLegalRN = new HipoteseLegalRN();
						$objHipoteseLegaDocumentoDTO = $objHipoteseLegalRN->consultar($objHipoteseLegaAnexoDTO);
						
						if($objHipoteseLegaDocumentoDTO != null)
							$strHipoteseLegalAnexo .= $objHipoteseLegaDocumentoDTO->getStrNome() . '(' . $objHipoteseLegaDocumentoDTO->getStrBaseLegal() . ')';
					}
					
					$strProtocoloRestrito .= '<img src="imagens/sei_chave_restrito.gif" align="absbottom"  title="Acesso Restrito.&#13' . $strHipoteseLegalAnexo . '">';
				}
				
				if($objProcedimentoDTOAnexado->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO && $objProcedimentoDTO->getStrStaNivelAcessoLocalProtocolo() == ProtocoloRN::$NA_PUBLICO) {
					$parametrosCriptografadosProcesso = MdPesqCriptografia::criptografa('id_orgao_acesso_externo=0&id_procedimento=' . $objProcedimentoDTOAnexado->getDblIdProcedimento());
					$urlPesquisaProcesso = 'md_pesq_processo_exibir.php?' . $parametrosCriptografadosProcesso;
					
					$strLinkProcessoAnexado = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink(MdPesqSolrUtilExterno::prepararUrl($urlPesquisaProcesso)));
					
					$strResultado .= '<td align="center"><a href="javascript:void(0);" onclick="window.open(\'' . $strLinkProcessoAnexado . '\');" alt="' . $objProcedimentoDTOAnexado->getStrNomeTipoProcedimento() . '" title="' . $objProcedimentoDTOAnexado->getStrNomeTipoProcedimento() . '" class="ancoraPadraoAzul">' . $objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado() . '</a>' . $strProtocoloRestrito . '</td>';
				} 
				else
					$strResultado .= '<td align="center" style="padding-right:22px" ><span class="retiraAncoraPadraoAzul">' . PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTOAnexado->getStrProtocoloProcedimentoFormatado()) . ' </span>' . $strProtocoloRestrito . '</td>';
				
				$strResultado .= '<td align="center">' . PaginaSEIExterna::getInstance()->formatarXHTML($objProcedimentoDTOAnexado->getStrNomeTipoProcedimento()) . '</td>
  																	<td align="center">' . $objProtocoloPesquisaPublicaDTO->getDtaDocumento() . '</td>
  																	<td align="center">' . $objProtocoloPesquisaPublicaDTO->getDtaRegistro() . '</td>
  																	<td align="center"><a alt="' . $objProcedimentoDTOAnexado->getStrDescricaoUnidadeGeradoraProtocolo() . '" title="' . $objProcedimentoDTOAnexado->getStrDescricaoUnidadeGeradoraProtocolo() . '" class="ancoraSigla">' . $objProcedimentoDTOAnexado->getStrSiglaUnidadeGeradoraProtocolo() . '</a></td>
  																	<td align="center" class="colunaAcoes">&nbsp;</td>';
				$strResultado .= '</tr>';
			}
		}
		
		$strResultado .= '</table>';
	}
	
	$numRegistrosAtividades = 0;
	
	if(($bolListaAndamentoProcessoPublico && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_PUBLICO) 
			||($bolListaAndamentoProcessoRestrito && $objProcedimentoDTO->getStrStaNivelAcessoGlobalProtocolo() == ProtocoloRN::$NA_RESTRITO)) {
		
		$objProcedimentoHistoricoDTO = new ProcedimentoHistoricoDTO();
		$objProcedimentoHistoricoDTO->setDblIdProcedimento($dblIdProcedimento);
		$objProcedimentoHistoricoDTO->setStrStaHistorico(ProcedimentoRN::$TH_EXTERNO);
		$objProcedimentoHistoricoDTO->setStrSinGerarLinksHistorico('N');
		
		$objProcedimentoRN = new ProcedimentoRN();
		$objProcedimentoDTORet = $objProcedimentoRN->consultarHistoricoRN1025($objProcedimentoHistoricoDTO);
		$arrObjAtividadeDTO = $objProcedimentoDTORet->getArrObjAtividadeDTO();
		
		$numRegistrosAtividades = count($arrObjAtividadeDTO);
	}
	
	if($numRegistrosAtividades > 0) {
		
		$bolCheck = false;
		
		$strResultadoAndamentos = '';
		
		$strResultadoAndamentos .= '<table id="tblHistorico" width="99.3%" class="infraTable" summary="Histórico de Andamentos">' . "\n";
		$strResultadoAndamentos .= '<caption class="infraCaption">' . PaginaSEIExterna::getInstance()->gerarCaptionTabela('Andamentos', $numRegistrosAtividades). '</caption>';
		$strResultadoAndamentos .= '<tr>';
		$strResultadoAndamentos .= '<th class="infraTh" width="20%">Data/Hora</th>';
		$strResultadoAndamentos .= '<th class="infraTh" width="10%">Unidade</th>';
		$strResultadoAndamentos .= '<th class="infraTh">Descrição</th>';
		$strResultadoAndamentos .= '</tr>' . "\n";
		
		$strQuebraLinha = '<span style="line-height:.5em"><br /></span>';
		
		foreach($arrObjAtividadeDTO as $objAtividadeDTO){
			
			$strResultadoAndamentos .= "\n\n" . '<!-- ' . $objAtividadeDTO->getNumIdAtividade() . ' -->' . "\n";
			
			if($objAtividadeDTO->getStrSinUltimaUnidadeHistorico() == 'S')
				$strAbertas = 'class="andamentoAberto"';
			else
				$strAbertas = 'class="andamentoConcluido"';
			
			$strResultadoAndamentos .= '<tr ' . $strAbertas . '>';
			$strResultadoAndamentos .= "\n" . '<td align="center">';
			$strResultadoAndamentos .= substr($objAtividadeDTO->getDthAbertura(), 0, 16);
			$strResultadoAndamentos .= '</td>';
			
			$strResultadoAndamentos .= "\n" . '<td align="center">';
			$strResultadoAndamentos .= '<a alt="' . $objAtividadeDTO->getStrDescricaoUnidade() . '" title="' . $objAtividadeDTO->getStrDescricaoUnidade() . '" class="ancoraSigla">' . $objAtividadeDTO->getStrSiglaUnidade() . '</a>';
			$strResultadoAndamentos .= '</td>';
			
			$strResultadoAndamentos .= "\n";
			$strResultadoAndamentos .= "\n" . '<td>';
			
			if(!InfraString::isBolVazia($objAtividadeDTO->getStrNomeTarefa()))
				$strResultadoAndamentos .= nl2br($objAtividadeDTO->getStrNomeTarefa()). $strQuebraLinha;
			
			$strResultadoAndamentos .= '</td>';
			
			$strResultadoAndamentos .= '</tr>';
		}
		$strResultadoAndamentos .= '</table><br />';
	}
}
catch(Exception $e){
	PaginaSEIExterna::getInstance()->processarExcecao($e);
}

function alert($msg, $strLocation) {
	echo '<script>window.alert("'. $msg .'"); window.location.href = location.origin + "'.$strLocation.'";</script>';
}
PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: ' . PaginaSEIExterna::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEIExterna::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
?>
label.infraLabelOpcional,
label.infraLabelObrigatorio,
label.infraLabelCheckbox,
label.infraLabelRadio{
color:black;
}
input.infraButton, button.infraButton{
border-color: #666 #666 #666 #666;
color:black;
}
div.infraBarraSistemaE {width:90%}
div.infraBarraSistemaD {width:5%}
div.infraBarraComandos {width:99%}

table caption {
  text-align:left !important;
  font-size: 1.2em;
  font-weight:bold;
}

.andamentoAberto {
  background-color:white;
}

.andamentoConcluido {
  background-color:white;
}

#tblCabecalho{margin-top:1;}
#tblDocumentos {margin-top:1.5em;}
#tblHistorico {margin-top:1.5em;}

label {display:block}
input.infraText, select.infraSelect, input.infraCheckbox, textarea.infraTextarea {display:block;}
input.infraText, select.infraSelect, textarea.infraTextarea {width:95%;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;}
input.infraText, select.infraSelect, input.infraCheckbox {height: 20px;}
textarea.infraTextarea {width:100%}
table {width:100%!important}
input.infraText, textarea.infraTextarea {padding: 0 3px;}
#divGeral {height: 3.2em; width: 100%; overflow:visible; margin-top:10px;}
.campos-form {width: 40%; float: left;}
.campos-container {float:left; width:100%;}
.texto-explicativo-container {font-size:13px;}
.termo-aceitacao {width: 50%;float:left;font-size:13px;border: solid 2px; padding:6px; margin-left:100px;}
.termo-aceitacao div:first-child {margin-bottom:20px;}
.termo-aceitacao div {margin-bottom:15px;}
.termo-aceitacao a {font-size:13px;}
.termo-aceitacao .botoes {margin-bottom: 5px}
.termo-aceite {display:inline-block;margin-bottom:5px !important;}
.botao-container {float:right;margin-right:10px;margin-bottom:5px !important;}
.botao-container input:hover {cursor:pointer;}

<?
PaginaSEIExterna::getInstance()->fecharStyle();

PaginaSEIExterna::getInstance()->montarJavaScript();
PaginaSEIExterna::getInstance()->abrirJavaScript();
?>

function inicializar(){
	infraOcultarMenuSistemaEsquema();
	
	infraProcessarResize();
}

function onSubmitForm(){
	var nome = infraTrim(document.getElementById('txtNome').value);
	if(nome==''){
		alert('Informe o nome');
		return false;
	}
	var email = infraTrim(document.getElementById('txtEmail').value);
	if(email==''){
		alert('Informe o e-mail');
		return false;
	}
	if(!infraValidarEmail(email)){
		alert('E-mail inválido');
		return false;
	}
	return true;
}

<?
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
PaginaSEIExterna::getInstance()->montarBarraComandosSuperior(array());
echo $strResultadoCabecalho;
echo $strMensagemProcessoRestrito;
PaginaSEIExterna::getInstance()->montarAreaTabela($strResultado,$numProtocolos);
echo $strResultadoAndamentos;
?>
<form id="sbmCadastrarPush" name="sbmCadastrarPush" method="post" onsubmit="return onSubmitForm();" action="<?=PaginaSEIExterna::getInstance()->formatarXHTML(SessaoSEIExterna::getInstance()->assinarLink('controlador_externo.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&id_procedimento='.$_GET['id_procedimento'].'&id_orgao_acesso_externo=0'))?>">
	<div id="divGeral" class="infraAreaDados">
        <div class="campos-form">
            <div class="campos-container">
                <?=PushFormINT::insereCampoTextoSimples('Nome', 'Nome', '', 50, true)?>
                <?=FormINT::insereEspacador(); ?>
            </div>
            
            <br style="clear: both;" />
            
            <div class="campos-container">
                <?=PushFormINT::insereCampoTextoSimples('Email', 'E-mail', '', 100, true)?>
                <?=FormINT::insereEspacador(); ?>
            </div>
       	</div>
        <div class="termo-aceite"><input type="checkbox" id="chkAceitarTermos" name="chkAceitarTermos"/><label id="lblAceitarTermos" for="chkAceitarTermos" class="termo-aceite">Li e aceito os termos acima.</label></div>
                      <div class="botao-container"><input type="submit" id="sbmAssinar" name="sbmCadastrarPush" value="Incluir" class="infraButton" /></div>
	</div>
</form>
<?  
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>