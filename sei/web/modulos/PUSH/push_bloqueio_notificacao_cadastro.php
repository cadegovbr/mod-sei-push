<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	session_start();
	
	SessaoSEI::getInstance()->validarLink();
	
	PaginaSEI::getInstance()->verificarSelecao('push_bloqueio_notificacao_selecionar');
	
	SessaoSEI::getInstance()->validarPermissao($_GET ['acao']);
	
	$objPushBloqueioNotificacaoDTO = new PushBloqueioNotificacaoDTO();
	
	$arrComandos = array();

	switch ($_GET ['acao']){
		case 'push_bloqueio_notificacao_cadastrar' :
			
			$strTitulo = 'Novo Bloqueio de notificação';
			
			$arrComandos [] = '<button type="submit" accesskey="S" name="sbmCadastrarBloqueioNotificacao" id="sbmCadastrarBloqueioNotificacao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
			$arrComandos [] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno(). '&id_push_bloqueio_notificacao=' . $_GET ['id_push_bloqueio_notificacao'] . '&acao_origem=' . $_GET ['acao'])). '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
			
			$objPushBloqueioNotificacaoDTO->setNumIdPushBloqueioNotificacao(null);
            $objPushBloqueioNotificacaoDTO->setStrNumeroProtocoloFormatado($_POST ['txtNumeroProtocoloFormatado']);

			if (isset($_POST ['sbmCadastrarBloqueioNotificacao'])){
				try {
					$objPushBloqueioNotificacaoRN = new PushBloqueioNotificacaoRN();
					$objPushBloqueioNotificacaoDTO = $objPushBloqueioNotificacaoRN->cadastrar($objPushBloqueioNotificacaoDTO);
					PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
					header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?&acao=' . PaginaSEI::getInstance()->getAcaoRetorno(). '&acao_origem=' . $_GET ['acao'] . '&id_push_bloqueio_notificacao=' . $objPushBloqueioNotificacaoDTO->getNumIdPushBloqueioNotificacao(). PaginaSEI::getInstance()->montarAncora($objPushBloqueioNotificacaoDTO->getNumIdPushBloqueioNotificacao())));
					die();
				} catch(Exception $e){
					PaginaSEI::getInstance()->processarExcecao($e);
				}
			}
			break;

		default :
			throw new InfraException("Ação '" . $_GET ['acao'] . "' não reconhecida.");
	}

} catch(Exception $e){
	PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema(). ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
label {display:block}
input.infraText, select.infraSelect, textarea.infraTextarea  {width: 30%}

#lblTarefa {position:absolute;left:0%;top:0%;width:90%;}
#txtTarefa {position:absolute;left:0%;top:18%;width:50%;}
#selTarefa {position:absolute;left:0%;top:38%;width:90%;height:56%;}
#divOpcoesTarefa {position:absolute;left:91%;top:38%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPushBloqueioNotificacaoCadastro" method="post"
	onsubmit="return OnSubmitForm();"
	action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?&acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados();
?>
    <?=PushFormINT::insereCampoTextoSimples('NumeroProtocoloFormatado', 'Número Processo (XXXX.XXXXX/AAA-DV)', $objPushBloqueioNotificacaoDTO->getStrNumeroProtocoloFormatado(), 50, true); ?>
    <?=PushFormINT::insereEspacador(); ?>

  <input type="hidden" id="hdnIdPushBloqueioNotificacao" name="hdnIdPushBloqueioNotificacao" value="<?=$objPushBloqueioNotificacaoDTO->getNumIdPushBloqueioNotificacao();?>" />
<?
PaginaSEI::getInstance()->fecharAreaDados();
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>

<script type="text/javascript">
  function inicializar(){
	  
    if ('<?=$_GET['acao']?>'=='push_bloqueio_notificacao_cadastrar')
      document.getElementById('txtNumeroProtocoloFormatado').focus();
    else if ('<?=$_GET['acao']?>'=='push_bloqueio_notificacao_consultar')
      infraDesabilitarCamposAreaDados();
    else {
      if(document.getElementById('btnCancelar'))
      	document.getElementById('btnCancelar').focus();
      else
    	document.getElementById('btnFechar').focus();
    }
  }

  function validarCadastro(){
	if (document.getElementById('txtNumeroProtocoloFormatado').value == '') {
		alert("Informe o processo.");
		document.getElementById('txtNumeroProtocoloFormatado').focus();
		return false;
	}

    return true;
  }

  function OnSubmitForm(){
    return validarCadastro();
  }
</script>