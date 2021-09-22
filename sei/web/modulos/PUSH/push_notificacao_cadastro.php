<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	session_start();
	
	SessaoSEI::getInstance()->validarLink();
	
	PaginaSEI::getInstance()->verificarSelecao('push_notificacao_tipo_procedimento_selecionar');
	PaginaSEI::getInstance()->verificarSelecao('push_notificacao_selecionar');
	
	SessaoSEI::getInstance()->validarPermissao($_GET ['acao']);
	
	$arrComandos = array();
	
	$objNotificacaoDTO = new PushNotificacaoDTO();

	$strItensSelTemplateEmailSistema = PushNotificacaoINT::montarSelectNome('', '', '');

	switch ($_GET ['acao']){
		case 'push_notificacao_cadastrar' :
			$Alterar = false;
			$strTitulo = 'Nova Notificação';
			
			$arrComandos [] = '<button type="submit" accesskey="S" name="sbmCadastrarNotificacao" id="sbmCadastrarNotificacao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
			$arrComandos [] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno(). '&id_notificacao=' . $_GET ['id_notificacao'] . '&acao_origem=' . $_GET ['acao'])). '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
			
			if (isset($_POST ['sbmCadastrarNotificacao'])){
				try {

					$arrObjTarefasDTO = array();
					$arrOpcoesTarefa = PaginaSEI::getInstance()->getArrOptionsSelect($_POST['hdnTarefas']);
					foreach($arrOpcoesTarefa as $opcoesTarefa){
						$objTarefaDTO  = new TarefaDTO();
						$objTarefaDTO->setNumIdTarefa($opcoesTarefa[0]);
						$objTarefaDTO->setStrNome($opcoesTarefa[1]);
						$arrObjTarefasDTO[] = $objTarefaDTO;
					}

					$arrOpcoesTipoProcedimento = PaginaSEI::getInstance()->getArrOptionsSelect($_POST['hdnTiposProcedimento']);

					foreach($arrOpcoesTipoProcedimento as $opcaoTipoProcedimento){
						$objNotificacaoDTO = new PushNotificacaoDTO();
						$objNotificacaoDTO->setNumIdNotificacao(null);
						$objNotificacaoDTO->setNumIdTipoProcedimento($opcaoTipoProcedimento[0]);
						$objNotificacaoDTO->setNumIdEmailSistema($_POST ['selDescricaoTemplateEmail']);
			            $objNotificacaoDTO->setStrSinNotificaRestrito(PaginaSEI::getInstance()->getCheckbox($_POST ['chkNotificaRestrito']));
			            $objNotificacaoDTO->setArrObjTarefaDTO($arrObjTarefasDTO);
			            $objNotificacaoRN = new PushNotificacaoRN();
						$objNotificacaoDTO = $objNotificacaoRN->cadastrar($objNotificacaoDTO);
					}

					PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
					header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?&acao=push_notificacao_listar&acao_origem=' . $_GET ['acao']));
					die();
				} catch(Exception $e){
					PaginaSEI::getInstance()->processarExcecao($e);
				}
			}
			break;
		case 'push_notificacao_alterar' :
			$Alterar = true;
			$strTitulo = 'Atualizar Notificação';
			
			$arrComandos [] = '<button type="submit" accesskey="S" name="sbmAlterarNotificacao" id="sbmAlterarNotificacao" value="Alterar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
			$arrComandos [] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno(). '&id_notificacao=' . $_GET ['id_notificacao'] . '&acao_origem=' . $_GET ['acao'])). '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

			if(!isset($_POST ['sbmAlterarNotificacao'])){
				$objNotificacaoDTO->setNumIdNotificacao($_GET['id_push_notificacao']);
				$objNotificacaoDTO->retTodos();
				$objNotificacaoRN = new PushNotificacaoRN();
				$objNotificacaoDTO = $objNotificacaoRN->consultar($objNotificacaoDTO);

				$objTipoProcedimentoDTO = new TipoProcedimentoDTO();
				$objTipoProcedimentoDTO->setNumIdTipoProcedimento($objNotificacaoDTO->getNumIdTipoProcedimento());
				$objTipoProcedimentoDTO->retTodos();
				$objTipoProcedimentoRN = new TipoProcedimentoRN();
				$arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

				$strItensSelTarefas = InfraINT::montarSelectArrInfraDTO(null,null,null,$objNotificacaoDTO->getArrObjTarefaDTO(),'IdTarefa','Nome');

				$strItensSelTemplateEmailSistema = PushNotificacaoINT::montarSelectNome('', '', $objNotificacaoDTO->getNumIdEmailSistema());
				
				$strTipoProcedimentoAlterar = $arrObjTipoProcedimentoDTO[0]->getStrNome();
				$numIdTipoProcedimentoAlterar = $arrObjTipoProcedimentoDTO[0]->getNumIdTipoProcedimento();
			}
			else{
				$objNotificacaoDTO->setNumIdNotificacao($_POST['hdnIdNotificacao']);
				$objNotificacaoDTO->setNumIdTipoProcedimento($_POST ['hdnIdTipoProcedimentoAlterar']);
				$objNotificacaoDTO->setNumIdEmailSistema($_POST ['selDescricaoTemplateEmail']);
				$objNotificacaoDTO->setStrSinNotificaRestrito(PaginaSEI::getInstance()->getCheckbox($_POST ['chkNotificaRestrito']));
				$arrObjTarefasDTO = array();
				$arrOpcoesTarefa = PaginaSEI::getInstance()->getArrOptionsSelect($_POST['hdnTarefas']);

				foreach($arrOpcoesTarefa as $opcoesTarefa){
					$objTarefaDTO  = new TarefaDTO();
					$objTarefaDTO->setNumIdTarefa($opcoesTarefa[0]);
					$objTarefaDTO->setStrNome($opcoesTarefa[1]);
					$arrObjTarefasDTO[] = $objTarefaDTO;
				}

				$objNotificacaoDTO->setArrObjTarefaDTO($arrObjTarefasDTO);

				try {
					$objNotificacaoRN = new PushNotificacaoRN();
					$objNotificacaoDTO = $objNotificacaoRN->alterar($objNotificacaoDTO);
					PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
					header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?&acao=push_notificacao_listar&acao_origem=' . $_GET ['acao']));
					die();
				} catch(Exception $e){
					PaginaSEI::getInstance()->processarExcecao($e);
				}
			}
			break;
		default :
			throw new InfraException("Ação '" . $_GET ['acao'] . "' não reconhecida.");
	}

	$strLinkAjaxNomeTarefa = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=push_notificacao_selecionar_tarefa');
	$strLinkTarefa = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_notificacao_selecionar&tipo_selecao=2&id_object=objLupaTarefa');

	$strLinkAjaxNomeTipoProcedimento = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=push_notificacao_tipo_procedimento_selecionar');
	$strLinkTipoProcedimento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_notificacao_tipo_procedimento_selecionar&tipo_selecao=2&id_object=objLupaTipoProcedimento');
	
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

#lblTipoProcedimento {position:absolute;left:0%;top:0%;width:90%;}
#txtTipoProcedimento {position:absolute;left:0%;top:18%;width:50%;}
#selTipoProcedimento {position:absolute;left:0%;top:38%;width:90%;height:56%;}
#divOpcoesTipoProcedimento {position:absolute;left:91%;top:38%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmNotificacaoCadastro" method="post"
	onsubmit="return OnSubmitForm();"
	action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?&acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados();
?>
<?
if($Alterar){
?>
	<?=PushFormINT::insereCampoTextoSimples('TipoProcedimentoAlterar', 'Tipo de Procedimento', $strTipoProcedimentoAlterar, 500, true, '', '', '', true); ?>
	<input type="hidden" id="hdnIdTipoProcedimentoAlterar" name="hdnIdTipoProcedimentoAlterar" value="<?=$numIdTipoProcedimentoAlterar ?>" />
<?
}
?>

  <div id="divTipoProcedimento" class="infraAreaDados" style="height:11em; <?=($Alterar?'display:none;':'')?>">
    <label id="lblTipoProcedimento" for="txtTipoProcedimento" accesskey="T" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">T</span>ipos de Processo:</label>
    <input type="text" id="txtTipoProcedimento" name="txtTipoProcedimento" class="infraText" style='width:520px;' tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <input type="hidden" id="hdnIdTipoProcedimento" name="hdnIdTipoProcedimento" class="infraText" value="" />
    <select id="selTipoProcedimento" name="selTipoProcedimento" class="infraSelect" multiple="multiple" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
    </select>
    <div id="divOpcoesTipoProcedimento">
      <img id="imgPesquisarTipoProcedimento" onclick="objLupaTipoProcedimento.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" alt="Pesquisa de Tipos de Processo" title="Pesquisa de Tipos de Processo" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgRemoverTipoProcedimento" onclick="objLupaTipoProcedimento.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Tipos de Processo Selecionados" title="Remover Tipos de Processos Selecionados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <br />
      <img id="imgTipoProcedimentoAcima" onclick="objLupaTipoProcedimento.moverAcima();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_acima_select.gif" alt="Mover Acima Tipo de Processo Selecionado" title="Mover Acima Tipo de Processo Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgTipoProcedimentoAbaixo" onclick="objLupaTipoProcedimento.moverAbaixo();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_abaixo_select.gif" alt="Mover Abaixo Tipo de Processo Selecionado" title="Mover Abaixo Tipo de Processo Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>
  </div>
  <?=PushFormINT::insereEspacador(); ?>
  
  <?=PushFormINT::insereCampoSelecaoSimples('DescricaoTemplateEmail', 'Template de e-mail', $strItensSelTemplateEmailSistema, true); ?>
  <?=PushFormINT::insereEspacador(); ?>

  <?=PushFormINT::insereCampoCheckSimples('NotificaRestrito', 'Notifica Processos Restritos', ($objNotificacaoDTO->isSetStrSinNotificaRestrito()?$objNotificacaoDTO->getStrSinNotificaRestrito():'N'))?>
  <?=PushFormINT::insereEspacador(); ?>

  <div id="divTarefas" class="infraAreaDados" style="height:11em;">
    <label id="lblTarefa" for="txtTarefa" accesskey="E" class="infraLabelOpcional"><span class="infraTeclaAtalho">E</span>ventos:</label>
    <input type="text" id="txtTarefa" name="txtTarefa" class="infraText" style='width:520px;' tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <input type="hidden" id="hdnIdTarefa" name="hdnIdTarefa" class="infraText" value="" />
    <select id="selTarefa" name="selTarefa" class="infraSelect" multiple="multiple" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
    	<?=$strItensSelTarefas?>
    </select>
    <div id="divOpcoesTarefa">
      <img id="imgPesquisarTarefa" onclick="objLupaTarefa.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" alt="Pesquisa de Tarefas" title="Pesquisa de Tarefas" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgRemoverTarefa" onclick="objLupaTarefa.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Eventos Selecionados" title="Remover Eventos Selecionados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <br />
      <img id="imgTarefaAcima" onclick="objLupaTarefa.moverAcima();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_acima_select.gif" alt="Mover Acima Evento Selecionado" title="Mover Acima Evento Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgTarefaAbaixo" onclick="objLupaTarefa.moverAbaixo();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_abaixo_select.gif" alt="Mover Abaixo Evento Selecionado" title="Mover Abaixo Evento Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>
  </div>

  	<input name="rdoTipoHistorico" id="optTotal" value="N" class="infraRadio" type="radio" style='position:relative;left:525px;top:-90px'>
  	<label id="lblTotal" for="optTotal" class="infraLabelOpcional" style='position:relative;left:545px;top:-110px'>Total</label>
  	<input name="rdoTipoHistorico" id="optCompleto" value="N" class="infraRadio" type="radio" style='position:relative;left:580px;top:-130px'>
  	<label id="lblCompleto" for="optCompleto" class="infraLabelOpcional" style='position:relative;left:600px;top:-150px'>Completo</label>
  	<input name="rdoTipoHistorico" id="optResumido" value="N" class="infraRadio" type="radio"style= 'position:relative;left:660px;top:-170px' checked>
  	<label id="lblResumido" for="optResumido" class="infraLabelOpcional" style='position:relative;left:680px;top:-190px'>Resumido</label>

  	<input type="hidden" id="hdnIdNotificacao" name="hdnIdNotificacao" value="<?=($objNotificacaoDTO->isSetNumIdNotificacao()?$objNotificacaoDTO->getNumIdNotificacao():'') ?>" />

	<input type="hidden" id="hdnTarefas" name="hdnTarefas" value="<?=PaginaSEI::tratarHTML($_POST['hdnTarefas'])?>" />
	<input type="hidden" id="hdnTiposProcedimento" name="hdnTiposProcedimento" value="<?=PaginaSEI::tratarHTML($_POST['hdnTiposProcedimento'])?>" />
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
  var objAutoCompletarTarefa = null;
  var objLupaTarefa = null;
  var objLupaTipoProcedimento = null;
  var objAutoCompletarTipoProcedimento = null;

  function inicializar(){

    if ('<?=$_GET['acao']?>'=='push_notificacao_cadastrar')    	
      document.getElementById('selTipoProcedimento').focus();
    else if ('<?=$_GET['acao']?>'=='push_notificacao_consultar')
      infraDesabilitarCamposAreaDados();
    else {
      if(document.getElementById('btnCancelar'))
      	document.getElementById('btnCancelar').focus();
      else
    	document.getElementById('btnFechar').focus();
    }

    objAutoCompletarTarefa = new infraAjaxAutoCompletar('hdnIdTarefa','txtTarefa','<?=$strLinkAjaxNomeTarefa ?>');
    objAutoCompletarTarefa.limparCampo = false;

    objAutoCompletarTipoProcedimento = new infraAjaxAutoCompletar('hdnIdTipoProcedimento','txtTipoProcedimento','<?=$strLinkAjaxNomeTipoProcedimento ?>');
    objAutoCompletarTipoProcedimento.limparCampo = false;

    objAutoCompletarTarefa.prepararExecucao = function(){
    	TipoHistorico = '';
    	if(document.getElementById('optTotal').checked)
    		TipoHistorico='T';
    	if(document.getElementById('optCompleto').checked)
    		TipoHistorico='C';
    	if(document.getElementById('optResumido').checked)
    		TipoHistorico='R';
        return 'palavras_pesquisa='+document.getElementById('txtTarefa').value+'&TipoHistorico='+TipoHistorico;
    };

    objAutoCompletarTipoProcedimento.prepararExecucao = function(){
        return 'palavras_pesquisa='+document.getElementById('txtTipoProcedimento').value;
    };

    objAutoCompletarTarefa.processarResultado = function(id,descricao,complemento){
		if (id!=''){
			objLupaTarefa.adicionar(id,descricao,document.getElementById('txtTarefa'));
		}
	};

	objAutoCompletarTipoProcedimento.processarResultado = function(id,descricao,complemento){
		if (id!=''){
			objLupaTipoProcedimento.adicionar(id,descricao,document.getElementById('txtTipoProcedimento'));
		}
	};

	objLupaTarefa = new infraLupaSelect('selTarefa','hdnTarefas','<?=$strLinkTarefa?>');
	objLupaTipoProcedimento = new infraLupaSelect('selTipoProcedimento','hdnTiposProcedimento','<?=$strLinkTipoProcedimento?>');
  }

  function validarCadastro(){
  	Alterar = '<?=($Alterar?'true':'false')?>';

	if (document.getElementById('hdnTiposProcedimento').value == '' && Alterar=='false') {
		alert("Informe os Tipos de Processo.");
		document.getElementById('hdnTiposProcedimento').focus();
		return false;
	}

	if (document.getElementById('selDescricaoTemplateEmail').value == '') {
		alert("Informe o Template de e-mail.");
		document.getElementById('selDescricaoTemplateEmail').focus();
		return false;
	}
	
	if (document.getElementById('hdnTarefas').value == '') {
		alert("Informe os Eventos.");
		document.getElementById('hdnTarefas').focus();
		return false;
	}
    
    return true;
  }

  function OnSubmitForm(){
    return validarCadastro();
  }
</script>