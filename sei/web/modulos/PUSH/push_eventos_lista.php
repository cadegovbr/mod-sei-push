<?

try {
  require_once dirname(__FILE__). '/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();
  
  PaginaSEI::getInstance()->verificarSelecao('push_notificacao_selecionar');
  
  PaginaSEI::getInstance()->prepararSelecao('push_notificacao_selecionar');
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  PaginaSEI::getInstance()->salvarCamposPost(array('txtPalavrasPesquisa'));
  
  $objTarefaDTO = new TarefaDTO();

  switch($_GET['acao']){
    case 'push_notificacao_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tarefa','Selecionar Tarefas');
      break;
    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();
  
  $arrComandos[] = '<button type="submit" accesskey="P" id="btnPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  
  if ($_GET['acao'] == 'push_notificacao_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  $objPushNotificacaoDTO = new PushNotificacaoDTO();
  $objPushNotificacaoDTO->setStrPalavrasPesquisa(PaginaSEI::getInstance()->recuperarCampo('txtPalavrasPesquisa'));

  $objPushNotificacaoRN = new PushNotificacaoRN();

  $arrObjTarefaDTO = $objPushNotificacaoRN->pesquisarTarefasAjax(array($objPushNotificacaoDTO,$_POST['rdoTipoHistorico']));

  PaginaSEI::getInstance()->prepararOrdenacao($objTarefaDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
  
  PaginaSEI::getInstance()->prepararPaginacao($objTarefaDTO);
  PaginaSEI::getInstance()->processarPaginacao($objTarefaDTO);
  
  $numRegistros = count($arrObjTarefaDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='push_notificacao_selecionar'){
      $bolCheck = true;
    }
    
    $strResultado = '';

    $strSumarioTabela = 'Tabela de Tarefas.';
    $strCaptionTabela = 'Tarefas';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th width="90%" class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objTarefaDTO,'Nome','Nome',$arrObjTarefaDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    
    $n = 0;
    for($i = 0;$i < $numRegistros; $i++){
      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      $bolCheckItem = $bolCheck;
      
      $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($n,$arrObjTarefaDTO[$i]->getNumIdTarefa(),$arrObjTarefaDTO[$i]->getStrNome()).'</td>';
      
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjTarefaDTO[$i]->getStrNome()).'</td>';
      $strResultado .= '<td align="center">';

      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($n,$arrObjTarefaDTO[$i]->getNumIdTarefa());
      
      $strResultado .= '</td></tr>'."\n";
      
      $n++;
    }
    $strResultado .= '</table>';
  }
  
  if ($_GET['acao'] == 'push_notificacao_selecionar'){
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

#lblTabelaTarefas {position:absolute;left:0%;top:0%;width:40%;}
#txtTabelaTarefas {position:absolute;left:0%;top:20%;width:40%;}

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
<form id="frmTarefaLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('10em');
  ?>

    <label id="lblTabelaTarefas" class="infraLabelObrigatorio">Tabela:</label>
    <input type="text" id="txtTabelaTarefas" style='width:265px;' name="txtTabelaTarefas" readonly="readonly" class="infraText, infraReadOnly" value=" <?=PaginaSEI::tratarHTML('Tabela de Tarefas')?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

	<label id="lblPalavrasPesquisa" for="txtPalavrasPesquisa" class="infraLabelOpcional">Palavras para Pesquisa:</label>
	<input type="text" id="txtPalavrasPesquisa" name="txtPalavrasPesquisa" value="<?=PaginaSEI::tratarHTML($objPushNotificacaoDTO->getStrPalavrasPesquisa())?>" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <input name="rdoTipoHistorico" id="optTotal" value="T" class="infraRadio" type="radio" style='position:relative;left:270px;top:18px' onclick="document.getElementById('btnPesquisar').click();" <?=($_POST['rdoTipoHistorico'] == 'T' || $_POST['rdoTipoHistorico'] == null?'checked':'')?>>
    <label id="lblTotal" for="optTotal" class="infraLabelOpcional" style='position:relative;left:268px;top:16px'>Total</label>
    <input name="rdoTipoHistorico" id="optCompleto" value="C" class="infraRadio" type="radio" style='position:relative;left:268px;top:18px' onclick="document.getElementById('btnPesquisar').click();" <?=($_POST['rdoTipoHistorico'] == 'C'?'checked':'')?>>
    <label id="lblCompleto" for="optCompleto" class="infraLabelOpcional" style='position:relative;left:268px;top:16px'>Completo</label>
    <input name="rdoTipoHistorico" id="optResumido" value="R" class="infraRadio" type="radio"style= 'position:relative;left:268px;top:18px' onclick="document.getElementById('btnPesquisar').click();" <?=($_POST['rdoTipoHistorico'] == 'R'?'checked':'')?>>
    <label id="lblResumido" for="optResumido" class="infraLabelOpcional" style='position:relative;left:268px;top:16px'>Resumido</label>


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