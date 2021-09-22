<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	session_start();
	
	SessaoSEI::getInstance()->validarLink();
	
	PaginaSEI::getInstance()->prepararSelecao('push_notificacao_selecionar');
	
	SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
	
	PaginaSEI::getInstance()->salvarCamposPost(array('selTipoProcedimento','selTemplateEmailSistema','selNotificaRestrito'));

	switch($_GET['acao']) {
		case 'push_notificacao_excluir':
			try{				
				foreach (PaginaSEI::getInstance()->getArrStrItensSelecionados() as $idNotificacao) {
					$objPushNotificacaoDTO = new PushNotificacaoDTO();
					$objPushNotificacaoDTO->setNumIdNotificacao($idNotificacao);
					$arrObjPushNotificacaoDTO[] = $objPushNotificacaoDTO;
				}

				$objPushNotificacaoRN = new PushNotificacaoRN();
				$objPushNotificacaoRN->excluir($arrObjPushNotificacaoDTO);
				PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
			}catch(Exception $e){
				PaginaSEI::getInstance()->processarExcecao($e);
			}
			header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
			die;
		case 'push_notificacao_selecionar':
	      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Notificação','Selecionar Notificações');
	
	      if($_GET['acao_origem']=='push_notificacao_cadastrar' and isset($_GET['id_notificacao']))
	          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_notificacao']);
	        
	      break;
		case 'push_notificacao_listar':
	      $strTitulo = 'Notificações';
	      break;
		default :
			throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
	}
	
  $arrComandos = array();
	
	$arrComandos [] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';
	
	if($_GET ['acao'] == 'push_notificacao_listar' || $_GET ['acao'] == 'push_notificacao_selecionar') {
		$bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('push_notificacao_cadastrar');
		
		if($bolAcaoCadastrar)
			$arrComandos [] = '<button type="button" accesskey="N" id="btnNovo" value="Nova" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_notificacao_cadastrar&acao_origem=' . $_GET ['acao'] . '&acao_retorno=' . $_GET ['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ova</button>';
	}
	
	$objNotificacaoDTO = new PushNotificacaoDTO();
	$objNotificacaoDTO->retTodos(true);
	
	$strTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimento');
	if($strTipoProcedimento !== '')
		$objNotificacaoDTO->setNumIdTipoProcedimento($strTipoProcedimento);
	
	$strTemplateEmailSistema = PaginaSEI::getInstance()->recuperarCampo('selTemplateEmailSistema');
	if($strTemplateEmailSistema !== '')
	    $objNotificacaoDTO->setNumIdEmailSistema($strTemplateEmailSistema);

    $strNotificaRestrito = PaginaSEI::getInstance()->recuperarCampo('selNotificaRestrito');
    if ($strNotificaRestrito!=='')
        $objNotificacaoDTO->setStrSinNotificaRestrito($strNotificaRestrito);
	
	PaginaSEI::getInstance()->prepararPaginacao($objNotificacaoDTO);
	PaginaSEI::getInstance()->prepararOrdenacao($objNotificacaoDTO, 'IdTipoProcedimento', InfraDTO::$TIPO_ORDENACAO_ASC);
	
	$objNotificacaoRN = new PushNotificacaoRN();
	$arrObjNotificacaoDTO = $objNotificacaoRN->listar($objNotificacaoDTO);
	
	PaginaSEI::getInstance()->processarPaginacao($objNotificacaoDTO);
	$numRegistros = count($arrObjNotificacaoDTO);
	
	if ($numRegistros > 0) {
		
		$bolCheck = false;
        $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('push_notificacao_alterar');
		$bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('push_notificacao_excluir');
		
		if ($bolAcaoExcluir) {
			$bolCheck = true;
			$arrComandos [] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
			$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_notificacao_excluir&acao_origem=' . $_GET ['acao']);
		}
		
		$strResultado = '';
		
		$strSumarioTabela = 'Tabela de Notificações.';
		$strCaptionTabela = 'Notificações';
		
		$strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n"; // 80
		$strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
		$strResultado .= '<tr>';
		if ($bolCheck)
			$strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";

		$strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objNotificacaoDTO, 'Tipo de Processo', 'NomeTipoProcedimento', $objNotificacaoDTO) . '</th>' . "\n";
		$strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objNotificacaoDTO, 'Template de e-mail', 'DescricaoEmailSistema', $objNotificacaoDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objNotificacaoDTO, 'Notifica Processos Restritos','SinNotificaRestrito',$objNotificacaoDTO).'</th>'."\n";

		$strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
		$strResultado .= '</tr>' . "\n";
		$strCssTr = '';
		for ($i = 0; $i < $numRegistros; $i ++) {
			
			$strCssTr =($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strResultado .= $strCssTr;
			if($bolCheck)
				$strResultado .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjNotificacaoDTO [$i]->getNumIdNotificacao(), $arrObjNotificacaoDTO[$i]->getStrNomeTipoProcedimento()) . '</td>';
			$strResultado .= '<td>' . $arrObjNotificacaoDTO[$i]->getStrNomeTipoProcedimento() . '</td>';
			$strResultado .= '<td>' . $arrObjNotificacaoDTO[$i]->getStrDescricaoEmailSistema() . '</td>';
            $strResultado .= '<td>' . ($arrObjNotificacaoDTO[$i]->getStrSinNotificaRestrito() == 'S' ? 'Sim' : 'Não') . '</td>';
			$strResultado .= '<td align="center">';

            if ($bolAcaoAlterar)
                $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_notificacao_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_push_notificacao='.$arrObjNotificacaoDTO[$i]->getNumIdNotificacao())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar registro de notificação" alt="Alterar registro de notificação" class="infraImg" /></a>&nbsp;';
			
			if($bolAcaoExcluir) {
				$strId = $arrObjNotificacaoDTO [$i]->getNumIdNotificacao();
				$strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjNotificacaoDTO[$i]->getStrNomeTipoProcedimento());
			}
			
			if($bolAcaoExcluir)
				$strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioImagensGlobal() . '/excluir.gif" title="Excluir Notificação" alt="Excluir Notificação" class="infraImg" /></a>&nbsp;';
			
			$strResultado .= '</td></tr>' . "\n";
		}
		$strResultado .= '</table>';
	}
	
	if($_GET ['acao'] == 'push_notificacao_selecionar')
		$arrComandos [] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
	else
		$arrComandos [] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET ['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
	
	$strItensselTipoProcedimento = TipoProcedimentoINT::montarSelectNome('', 'Todos', $strTipoProcedimento);
	$strItensselTemplateEmailSistema = PushNotificacaoINT::montarSelectNome('', 'Todos', $strTemplateEmailSistema);
    $strItensSelNotificaRestrito = PushNotificacaoINT::montarSelectBinario('','Todos',$strNotificaRestrito);
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
#selTipoProcedimento, #selTemplateEmailSistema {width: 30%}
input.infraText, textarea.infraTextarea  {width: 30%}
<?
PaginaSEI::getInstance()->fecharStyle();

PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
	  if ('<?=$_GET['acao']?>'=='push_notificacao_selecionar'){
	    infraReceberSelecao();
	    document.getElementById('btnFecharSelecao').focus();
	  }
	  else
    	document.getElementById('btnFechar').focus();
	infraEfeitoTabelas();
}

<? if ($bolAcaoExcluir){ ?>
  function acaoExcluir(id,desc){
    if (confirm("Confirma exclusão da Notificação \""+desc+"\"?")){
      document.getElementById('hdnInfraItemId').value=id;
      document.getElementById('frmNotificacaoLista').action='<?=$strLinkExcluir?>';
      document.getElementById('frmNotificacaoLista').submit();
    }
  }

  function acaoExclusaoMultipla(){
    if (document.getElementById('hdnInfraItensSelecionados').value==''){
      alert('Nenhuma Notificação selecionada.');
      return;
    }

    if (confirm("Confirma exclusão das Notificações selecionadas?")) {
      document.getElementById('hdnInfraItemId').value='';
      document.getElementById('frmNotificacaoLista').action='<?=$strLinkExcluir?>';
      document.getElementById('frmNotificacaoLista').submit();
    }
  }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmNotificacaoLista" name="frmNotificacaoLista" method="post">
	<?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados();
?>
  <label id="lblTipoProcedimento" for="selTipoProcedimento" accesskey="P" class="infraLabelOpcional">Tipo do <span class="infraTeclaAtalho">P</span>rocesso:</label>
  <select id="selTipoProcedimento" name="selTipoProcedimento" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
    <?=$strItensselTipoProcedimento?>
  </select>
  <br /><br />
  <label id="lblTemplateEmailSistema" for="selTemplateEmailSistema" accesskey="E" class="infraLabelOpcional">Template de <span class="infraTeclaAtalho">E</span>-mail:</label>
  <select id="selTemplateEmailSistema" name="selTemplateEmailSistema" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
    <?=$strItensselTemplateEmailSistema?>
  </select>
  <br /><br />
  <label id="lblNotificaRestrito" for="selNotificaRestrito" accesskey="N" class="infraLabelOpcional"><span class="infraTeclaAtalho">N</span>otifica Processos Restritos:</label>
  <select id="selNotificaRestrito" name="selNotificaRestrito" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
    <?=$strItensSelNotificaRestrito?>
  </select>
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