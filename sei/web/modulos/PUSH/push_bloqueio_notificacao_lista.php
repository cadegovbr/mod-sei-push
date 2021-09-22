<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	session_start();
	
	SessaoSEI::getInstance()->validarLink();
	
	PaginaSEI::getInstance()->prepararSelecao('push_bloqueio_notificacao_selecionar');
	
	SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
	
	PaginaSEI::getInstance()->salvarCamposPost(array('txtNumeroProtocoloFormatado'));
	
	switch($_GET['acao']) {
		case 'push_bloqueio_notificacao_excluir':
			try{
				$arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
				$arrObjPushBloqueioNotificacaoDTO = array();
				for($i=0;$i<count($arrStrIds);$i++){
					$objPushBloqueioNotificacaoDTO = new PushBloqueioNotificacaoDTO();
					$objPushBloqueioNotificacaoDTO->setNumIdPushBloqueioNotificacao($arrStrIds[$i]);
					$arrObjPushBloqueioNotificacaoDTO[] = $objPushBloqueioNotificacaoDTO;
				}
				$objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
				$objPushBloqueioNotificacaoRN->excluir($arrObjPushBloqueioNotificacaoDTO);
				PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
			}catch(Exception $e){
				PaginaSEI::getInstance()->processarExcecao($e);
			}
			header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
			die;
		case 'push_bloqueio_notificacao_selecionar':
	      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Bloqueio de notificação','Selecionar Bloqueios de notificação');
	
	      if($_GET['acao_origem']=='push_bloqueio_notificacao_cadastrar' and isset($_GET['id_push_bloqueio_notificacao']))
	          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_push_bloqueio_notificacao']);
	        
	      break;
		case 'push_bloqueio_notificacao_listar':
	      $strTitulo = 'Bloqueios de notificação';
	      break;
		default :
			throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
	}
	
  $arrComandos = array();
	
	$arrComandos [] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';
	
	if($_GET ['acao'] == 'push_bloqueio_notificacao_listar' || $_GET ['acao'] == 'push_bloqueio_notificacao_selecionar') {
		$bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('push_bloqueio_notificacao_cadastrar');

		if($bolAcaoCadastrar)
			$arrComandos [] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_bloqueio_notificacao_cadastrar&acao_origem=' . $_GET ['acao'] . '&acao_retorno=' . $_GET ['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
	}
	
	$objPushBloqueioNotificacaoDTO = new PushBloqueioNotificacaoDTO();
	$objPushBloqueioNotificacaoDTO->retTodos(true);

    $txtNumeroProtocoloFormatado = PaginaSEI::getInstance()->recuperarCampo('txtNumeroProtocoloFormatado');
    if ($txtNumeroProtocoloFormatado!=='')
        $objPushBloqueioNotificacaoDTO->setStrNumeroProtocoloFormatado($txtNumeroProtocoloFormatado);

	PaginaSEI::getInstance()->prepararPaginacao($objPushBloqueioNotificacaoDTO);
	PaginaSEI::getInstance()->prepararOrdenacao($objPushBloqueioNotificacaoDTO, 'NumeroProtocoloFormatado', InfraDTO::$TIPO_ORDENACAO_ASC);
	
	$objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
	$arrObjPushBloqueioNotificacaoDTO = $objPushBloqueioNotificacaoRN->pesquisar($objPushBloqueioNotificacaoDTO);
	
	PaginaSEI::getInstance()->processarPaginacao($objPushBloqueioNotificacaoDTO);
	$numRegistros = count($arrObjPushBloqueioNotificacaoDTO);
	
	if($numRegistros > 0) {
		
		$bolCheck = false;
		$bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('push_bloqueio_notificacao_excluir');
		
		if($bolAcaoExcluir) {
			$bolCheck = true;
			$arrComandos [] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
			$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_bloqueio_notificacao_excluir&acao_origem=' . $_GET ['acao']);
		}
		
		$strResultado = '';
		
		$strSumarioTabela = 'Tabela de Bloqueios de notificação.';
		$strCaptionTabela = 'Bloqueios de notificação';
		
		$strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n"; // 80
		$strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
		$strResultado .= '<tr>';
		if($bolCheck)
			$strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
		
		$strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objPushBloqueioNotificacaoDTO, 'Número Processo (XXXX.XXXXX/AAA-DV)', 'NumeroProtocoloFormatado', $objPushBloqueioNotificacaoDTO) . '</th>' . "\n";
		$strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
		$strResultado .= '</tr>' . "\n";
		$strCssTr = '';
		for($i = 0; $i < $numRegistros; $i ++) {

			$strCssTr =($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strResultado .= $strCssTr;

			if($bolCheck)
				$strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjPushBloqueioNotificacaoDTO [$i]->getNumIdPushBloqueioNotificacao(), $arrObjPushBloqueioNotificacaoDTO [$i]->getStrNumeroProtocoloFormatado()) . '</td>';

			$strResultado .= '<td>' . $arrObjPushBloqueioNotificacaoDTO [$i]->getStrNumeroProtocoloFormatado() . '</td>';
			$strResultado .= '<td align="center">';

            if($bolAcaoExcluir) {
                $strId = $arrObjPushBloqueioNotificacaoDTO [$i]->getNumIdPushBloqueioNotificacao();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjPushBloqueioNotificacaoDTO [$i]->getStrNumeroProtocoloFormatado());
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioImagensGlobal() . '/excluir.gif" title="Excluir Bloqueio de notificação" alt="Excluir Bloqueio de notificação" class="infraImg" /></a>&nbsp;';
            }

			$strResultado .= '</td></tr>' . "\n";
		}
		$strResultado .= '</table>';
	}
	
	if($_GET ['acao'] == 'push_bloqueio_notificacao_selecionar')
		$arrComandos [] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
	else
		$arrComandos [] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET ['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
	
} catch(Exception $e){
	PaginaSEI::getInstance()->processarExcecao($e);
}
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' .PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
label {display:block}
#selTarefa, #selTipoProcedimento, #selTemplateEmailSistema {width: 30%}
input.infraText, textarea.infraTextarea  {width: 30%}
<?
PaginaSEI::getInstance()->fecharStyle();

PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
	  if ('<?=$_GET['acao']?>'=='push_bloqueio_notificacao_selecionar'){
	    infraReceberSelecao();
	    document.getElementById('btnFecharSelecao').focus();
	  }
	  else
    	document.getElementById('btnFechar').focus();
	infraEfeitoTabelas();
}

<? if ($bolAcaoExcluir){ ?>
  function acaoExcluir(id,desc){
    if (confirm("Confirma exclusão da Bloqueio de notificação \""+desc+"\"?")){
      document.getElementById('hdnInfraItemId').value=id;
      document.getElementById('frmBloqueioNotificacaoLista').action='<?=$strLinkExcluir?>';
      document.getElementById('frmBloqueioNotificacaoLista').submit();
    }
  }

  function acaoExclusaoMultipla(){
    if (document.getElementById('hdnInfraItensSelecionados').value==''){
      alert('Nenhuma Bloqueio de notificação selecionada.');
      return;
    }

    if (confirm("Confirma exclusão das Bloqueios de notificação selecionadas?")) {
      document.getElementById('hdnInfraItemId').value='';
      document.getElementById('frmBloqueioNotificacaoLista').action='<?=$strLinkExcluir?>';
      document.getElementById('frmBloqueioNotificacaoLista').submit();
    }
  }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmBloqueioNotificacaoLista" name="frmBloqueioNotificacaoLista" method="post">
<?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados();
?>
    <label id="lblNumeroProtocoloFormatado" for="txtNumeroProtocoloFormatado" class="infraLabelOpcional">Número Processo (XXXX.XXXXX/AAA-DV):</label>
    <input type="text" id="txtNumeroProtocoloFormatado" name="txtNumeroProtocoloFormatado" class="infraText" value="<?=$txtNumeroProtocoloFormatado?>" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
<?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros,true);
  //PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?  
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>