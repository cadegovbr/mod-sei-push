<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	session_start();
	
	SessaoSEI::getInstance()->validarLink();
	
	PaginaSEI::getInstance()->prepararSelecao('push_selecionar');
	
	SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
	
	PaginaSEI::getInstance()->salvarCamposPost(array('txtNumeroProtocoloFormatado','txtNome','txtEmail'));
	
	switch($_GET['acao']) {
		case 'push_listar':
	      $strTitulo = 'Assinaturas';
	      break;
		default :
			throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
	}

    $arrComandos = array();

    $arrComandos [] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';
	
	$objPushlDTO = new PushDTO();
    $objPushlDTO->retTodos(true);

    $txtNumeroProtocoloFormatado = PaginaSEI::getInstance()->recuperarCampo('txtNumeroProtocoloFormatado');
    if ($txtNumeroProtocoloFormatado!=='')
        $objPushlDTO->setStrNumeroProtocoloFormatado($txtNumeroProtocoloFormatado);

    $txtNome = PaginaSEI::getInstance()->recuperarCampo('txtNome');
    if ($txtNome!=='')
        $objPushlDTO->setStrNome($txtNome);

    $txtEmail = PaginaSEI::getInstance()->recuperarCampo('txtEmail');
    if ($txtEmail!=='')
        $objPushlDTO->setStrEmail($txtEmail);

	PaginaSEI::getInstance()->prepararPaginacao($objPushlDTO);
	PaginaSEI::getInstance()->prepararOrdenacao($objPushlDTO, 'NumeroProtocoloFormatado', InfraDTO::$TIPO_ORDENACAO_ASC);
	
	$objPushRN = new PushRN();
	$arrObjPushDTO = $objPushRN->pesquisar($objPushlDTO);
	
	PaginaSEI::getInstance()->processarPaginacao($objPushlDTO);
	$numRegistros = count($arrObjPushDTO);
	
	if($numRegistros > 0) {

		$strResultado = '';
		
		$strSumarioTabela = 'Tabela de Assinaturas.';
		$strCaptionTabela = 'Assinaturas';
		
		$strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n"; // 80
		$strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
		$strResultado .= '<tr>';
		$strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objPushlDTO, 'Nuúmero Protocolo Formatado', 'NumeroProtocoloFormatado', $objPushlDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objPushlDTO, 'Nome', 'Nome', $objPushlDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objPushlDTO, 'Email', 'Email', $objPushlDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objPushlDTO, 'Data do último envio', 'UltimoEnvioEmail', $objPushlDTO) . '</th>' . "\n";
		$strResultado .= '</tr>' . "\n";
		$strCssTr = '';
		for($i = 0; $i < $numRegistros; $i ++) {

			$strCssTr =($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
			$strResultado .= $strCssTr;

			$strResultado .= '<td>' . $arrObjPushDTO [$i]->getStrNumeroProtocoloFormatado() . '</td>';
            $strResultado .= '<td>' . $arrObjPushDTO [$i]->getStrNome() . '</td>';
            $strResultado .= '<td>' . $arrObjPushDTO [$i]->getStrEmail() . '</td>';
            $strResultado .= '<td>' . $arrObjPushDTO [$i]->getDthUltimoEnvioEmail() . '</td>';
			$strResultado .= '</tr>' . "\n";
		}
		$strResultado .= '</table>';
	}
	
	if($_GET ['acao'] == 'push_selecionar')
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
	  if ('<?=$_GET['acao']?>'=='push_selecionar'){
	    infraReceberSelecao();
	    document.getElementById('btnFecharSelecao').focus();
	  }
	  else
    	document.getElementById('btnFechar').focus();
	infraEfeitoTabelas();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
<form id="frmPushLista" name="frmPushLista" method="post">
<?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados();
?>
    <label id="lblNumeroProtocoloFormatado" for="txtNumeroProtocoloFormatado" class="infraLabelOpcional">Número Processo (XXXX.XXXXX/AAA-DV):</label>
    <input type="text" id="txtNumeroProtocoloFormatado" name="txtNumeroProtocoloFormatado" class="infraText" value="<?=$txtNumeroProtocoloFormatado?>" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    <br /><br />
    <label id="lblNome" for="txtNome" class="infraLabelOpcional">Nome:</label>
    <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=$txtNome?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    <br /><br />
    <label id="lblEmail" for="txtEmail" class="infraLabelOpcional">Email:</label>
    <input type="text" id="txtEmail" name="txtEmail" class="infraText" value="<?=$txtEmail?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
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