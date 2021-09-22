<?




try {
  require_once dirname(__FILE__) . '/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('push_config_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('txtNome'));

  switch($_GET['acao']){
    case 'push_config_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjPushConfiguracaoDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objPushConfiguracaoDTO = new PushConfiguracaoDTO();
          $objPushConfiguracaoDTO->setNumIdPushConfiguracao($arrStrIds[$i]);
          $arrObjPushConfiguracaoDTO[] = $objPushConfiguracaoDTO;
        }
        $objPushConfiguracaoRN = new PushConfiguracaoRN();
        $objPushConfiguracaoRN->excluir($arrObjPushConfiguracaoDTO);
        PaginaSEI::getInstance()->setStrMensagem('Operação realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      }
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'push_config_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Configuração','Selecionar Configurações');

      if ($_GET['acao_origem']=='push_config_cadastrar' and isset($_GET['id_push_configuracao']))
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_push_configuracao']);
        
      break;

    case 'push_config_listar':
      $strTitulo = 'Configurações';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $arrComandos = array();

  $arrComandos[] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';

  if ($_GET['acao'] == 'push_config_listar' || $_GET['acao'] == 'push_config_selecionar'){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('push_config_cadastrar');

    if ($bolAcaoCadastrar)
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_config_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
  }

  $objPushConfiguracaoDTO = new PushConfiguracaoDTO();
  $objPushConfiguracaoDTO->retNumIdPushConfiguracao();
  $objPushConfiguracaoDTO->retStrNome();
  $objPushConfiguracaoDTO->retStrValor();

  $strNome = PaginaSEI::getInstance()->recuperarCampo('txtNome');
  if ($strNome!=='')
    $objPushConfiguracaoDTO->setStrNome($strNome);
  
  PaginaSEI::getInstance()->prepararPaginacao($objPushConfiguracaoDTO);
  PaginaSEI::getInstance()->prepararOrdenacao($objPushConfiguracaoDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);

  $objPushConfiguracaoRN = new PushConfiguracaoRN();
  $arrObjPushConfiguracaoDTO = $objPushConfiguracaoRN->pesquisar($objPushConfiguracaoDTO);

  PaginaSEI::getInstance()->processarPaginacao($objPushConfiguracaoDTO);
  $numRegistros = count($arrObjPushConfiguracaoDTO);

  if ($numRegistros > 0) {

    $bolCheck = false;
    $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('push_config_consultar');
    $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('push_config_alterar');
    $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('push_config_excluir');

    if ($bolAcaoExcluir) {
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_config_excluir&acao_origem='.$_GET['acao']);
    }

    $strResultado = '';

    $strSumarioTabela = 'Tabela de Configurações.';
    $strCaptionTabela = 'Configurações';

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n"; //80
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck)
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";

    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objPushConfiguracaoDTO,'Nome','Nome',$arrObjPushConfiguracaoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objPushConfiguracaoDTO,'Valor','Valor',$arrObjPushConfiguracaoDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck)
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjPushConfiguracaoDTO[$i]->getNumIdPushConfiguracao(),$arrObjPushConfiguracaoDTO[$i]->getStrNome()).'</td>';
      
      $strResultado .= '<td>'.$arrObjPushConfiguracaoDTO[$i]->getStrNome().'</td>';
      $strResultado .= '<td>'.$arrObjPushConfiguracaoDTO[$i]->getStrValor().'</td>';
      $strResultado .= '<td align="center">';

      if ($bolAcaoConsultar)
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_config_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_push_configuracao='.$arrObjPushConfiguracaoDTO[$i]->getNumIdPushConfiguracao())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Configuração" alt="Consultar Configuração" class="infraImg" /></a>&nbsp;';
      
      if ($bolAcaoAlterar)
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=push_config_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_push_configuracao='.$arrObjPushConfiguracaoDTO[$i]->getNumIdPushConfiguracao())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Configuração" alt="Alterar Configuração" class="infraImg" /></a>&nbsp;';

      if ($bolAcaoExcluir){
        $strId = $arrObjPushConfiguracaoDTO[$i]->getNumIdPushConfiguracao();
        $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjPushConfiguracaoDTO[$i]->getStrNome());
      }
      
      if ($bolAcaoExcluir)
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Configuração" alt="Excluir Configuração" class="infraImg" /></a>&nbsp;';

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }

  if ($_GET['acao'] == 'push_config_selecionar')
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  else
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  
}
catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: '.PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
  label {display:block}
  input.infraText, select.infraSelect, textarea.infraTextarea  {width: 30%}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='push_config_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }
  else
    document.getElementById('btnFechar').focus();

  infraEfeitoTabelas();
}

<? if ($bolAcaoExcluir){ ?>
  function acaoExcluir(id,desc){
    if (confirm("Confirma exclusão do Configuração \""+desc+"\"?")){
      document.getElementById('hdnInfraItemId').value=id;
      document.getElementById('frmPushConfiguracaoLista').action='<?=$strLinkExcluir?>';
      document.getElementById('frmPushConfiguracaoLista').submit();
    }
  }

  function acaoExclusaoMultipla(){
    if (document.getElementById('hdnInfraItensSelecionados').value==''){
      alert('Nenhuma Configuração selecionado.');
      return;
    }

    if (confirm("Confirma exclusão das Configurações selecionadas?")) {
      document.getElementById('hdnInfraItemId').value='';
      document.getElementById('frmPushConfiguracaoLista').action='<?=$strLinkExcluir?>';
      document.getElementById('frmPushConfiguracaoLista').submit();
    }
  }
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPushConfiguracaoLista" method="post" action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados();
?>

  <label id="lblNome" for="txtNome" accesskey="N" class="infraLabelOpcional"><span class="infraTeclaAtalho">N</span>ome:</label>
  <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=$strNome?>" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
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