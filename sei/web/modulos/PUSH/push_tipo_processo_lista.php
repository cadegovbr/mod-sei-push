<?

try {
  require_once dirname(__FILE__). '/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();
  
  PaginaSEI::getInstance()->verificarSelecao('push_notificacao_tipo_procedimento_selecionar');
  
  PaginaSEI::getInstance()->prepararSelecao('push_notificacao_tipo_procedimento_selecionar');
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  PaginaSEI::getInstance()->salvarCamposPost(array('txtPalavrasPesquisa'));
  
  $objTipoProcedimentoDTO = new TipoProcedimentoDTO();

  switch($_GET['acao']){
    case 'push_notificacao_tipo_procedimento_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Processo','Selecionar Tipo Processo');
      break;
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  
  $arrComandos[] = '<button type="submit" accesskey="P" id="btnPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  
  if ($_GET['acao'] == 'push_notificacao_tipo_procedimento_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  $objPushNotificacaoDTO = new PushNotificacaoDTO();
  $objPushNotificacaoDTO->setStrPalavrasPesquisa(PaginaSEI::getInstance()->recuperarCampo('txtPalavrasPesquisa'));

  $objPushNotificacaoRN = new PushNotificacaoRN();

  $arrObjTipoProcedimentoDTO = $objPushNotificacaoRN->pesquisarTiposProcedimentoAjax(array($objPushNotificacaoDTO));

  PaginaSEI::getInstance()->prepararOrdenacao($objTipoProcedimentoDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
  
  PaginaSEI::getInstance()->prepararPaginacao($objTipoProcedimentoDTO);
  PaginaSEI::getInstance()->processarPaginacao($objTipoProcedimentoDTO);
  
  $numRegistros = count($arrObjTipoProcedimentoDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='push_notificacao_tipo_procedimento_selecionar'){
      $bolCheck = true;
    }
    
    $strResultado = '';

    $strSumarioTabela = 'Tabela de Tipos de Processo.';
    $strCaptionTabela = 'Tipos Procedimento';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th width="90%" class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objTipoProcedimentoDTO,'Nome','Nome',$arrObjTipoProcedimentoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    
    $n = 0;
    for($i = 0;$i < $numRegistros; $i++){
      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      $bolCheckItem = $bolCheck;
      
      $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($n,$arrObjTipoProcedimentoDTO[$i]->getNumIdTipoProcedimento(),$arrObjTipoProcedimentoDTO[$i]->getStrNome()).'</td>';
      
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjTipoProcedimentoDTO[$i]->getStrNome()).'</td>';
      $strResultado .= '<td align="center">';

      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($n,$arrObjTipoProcedimentoDTO[$i]->getNumIdTipoProcedimento());
      
      $strResultado .= '</td></tr>'."\n";
      
      $n++;
    }
    $strResultado .= '</table>';
  }
  
  if ($_GET['acao'] == 'push_notificacao_tipo_procedimento_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }

  if (PaginaSEI::getInstance()->isBolPaginaSelecao()) {
    $strDisplayOpcaoDesativados = 'display:none;';
  }

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
} 

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>

#lblTabelaTipoProcedimento {position:absolute;left:0%;top:0%;width:40%;}
#txtTabelaTipoProcedimento {position:absolute;left:0%;top:20%;width:40%;}

#lblPalavrasPesquisa {position:absolute;left:0%;top:50%;width:70%;}
#txtPalavrasPesquisa {position:absolute;left:0%;top:70%;width:70%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){	
    infraReceberSelecao();
  
  if (infraGetAnchor()==null){
    document.getElementById('txtPalavrasPesquisa').focus();
  }
  
  infraEfeitoTabelas();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmTipoProcedimentoLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('10em');
  ?>

    <label id="lblTabelaTipoProcedimento" class="infraLabelObrigatorio">Tabela:</label>
    <input type="text" id="txtTabelaTipoProcedimento" style='width:265px;' name="txtTabelaTipoProcedimento" readonly="readonly" class="infraText, infraReadOnly" value=" <?=PaginaSEI::tratarHTML('Tabela de Tipos Procedimento')?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

	<label id="lblPalavrasPesquisa" for="txtPalavrasPesquisa" class="infraLabelOpcional">Palavras para Pesquisa:</label>
	<input type="text" id="txtPalavrasPesquisa" name="txtPalavrasPesquisa" value="<?=PaginaSEI::tratarHTML($objPushNotificacaoDTO->getStrPalavrasPesquisa())?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
  
  <input type="hidden" name="hdnFlag" value="1" />  
  
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>