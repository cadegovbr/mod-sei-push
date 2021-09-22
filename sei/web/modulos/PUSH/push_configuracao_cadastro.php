<?
try {
  require_once dirname(__FILE__) . '/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('push_config_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objPushConfiguracaoDTO = new PushConfiguracaoDTO();

  $arrComandos = array();

  switch($_GET['acao']){
    case 'push_config_cadastrar':

      $strTitulo = 'Nova Configuração';

      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarPushConfiguracao" id="sbmCadastrarPushConfiguracao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_push_configuracao='.$_GET['id_push_configuracao'].'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objPushConfiguracaoDTO->setNumIdPushConfiguracao(null);
      $objPushConfiguracaoDTO->setStrNome($_POST['txtNome']);
      $objPushConfiguracaoDTO->setStrValor($_POST['txtValor']);

      if (isset($_POST['sbmCadastrarPushConfiguracao'])) {
        try{
          $objPushConfiguracaoRN = new PushConfiguracaoRN();
          $objPushConfiguracaoDTO = $objPushConfiguracaoRN->cadastrar($objPushConfiguracaoDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_push_configuracao='.$objPushConfiguracaoDTO->getNumIdPushConfiguracao().PaginaSEI::getInstance()->montarAncora($objPushConfiguracaoDTO->getNumIdPushConfiguracao())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'push_config_alterar':
      $strTitulo = 'Alterar Configuração';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarPushConfiguracao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';

      if (isset($_GET['id_push_configuracao'])){
        $objPushConfiguracaoDTO->setNumIdPushConfiguracao($_GET['id_push_configuracao']);
        $objPushConfiguracaoDTO->retTodos(true);
        $objPushConfiguracaoRN = new PushConfiguracaoRN();
        $objPushConfiguracaoDTO = $objPushConfiguracaoRN->consultar($objPushConfiguracaoDTO);
        if ($objPushConfiguracaoDTO == null)
          throw new InfraException("Registro não encontrado.");
      }
      else {
        $objPushConfiguracaoDTO->setNumIdPushConfiguracao($_POST['hdnIdPushConfiguracao']);
        $objPushConfiguracaoDTO->setStrNome($_POST['txtNome']);
        $objPushConfiguracaoDTO->setStrValor($_POST['txtValor']);
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objPushConfiguracaoDTO->getNumIdPushConfiguracao()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarPushConfiguracao'])) {
        try{
          $objPushConfiguracaoRN = new PushConfiguracaoRN();
          $objPushConfiguracaoRN->alterar($objPushConfiguracaoDTO);
          PaginaSEI::getInstance()->setStrMensagem('Configuração "'.$objPushConfiguracaoDTO->getNumIdPushConfiguracao().'" alterado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objPushConfiguracaoDTO->getNumIdPushConfiguracao())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }

      }
      break;

    case 'push_config_consultar':
      $strTitulo = 'Consultar Configuração';
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_push_configuracao']))).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
      $objPushConfiguracaoDTO->setNumIdPushConfiguracao($_GET['id_push_configuracao']);
      $objPushConfiguracaoDTO->retTodos(true);
      $objPushConfiguracaoRN = new PushConfiguracaoRN();
      $objPushConfiguracaoDTO = $objPushConfiguracaoRN->consultar($objPushConfiguracaoDTO);
      if ($objPushConfiguracaoDTO === null)
        throw new InfraException("Registro não encontrado.");

      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
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
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPushConfiguracaoCadastro" method="post" onsubmit="return OnSubmitForm();"
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?&acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados();
?>
  <?=PushFormINT::insereCampoTextoSimples('Nome', 'Nome', $objPushConfiguracaoDTO->getStrNome(), 100, true); ?>
  <?=PushFormINT::insereEspacador(); ?>
  
  <?=PushFormINT::insereCampoTextoSimples('Valor', 'Valor', $objPushConfiguracaoDTO->getStrValor(), 255, true); ?>
  <?=PushFormINT::insereEspacador(); ?>

  <input type="hidden" id="hdnIdPushConfiguracao" name="hdnIdPushConfiguracao" value="<?=$objPushConfiguracaoDTO->getNumIdPushConfiguracao();?>" />
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
    if ('<?=$_GET['acao']?>'=='push_config_cadastrar')
      document.getElementById('txtNome').focus();
    else if ('<?=$_GET['acao']?>'=='push_config_consultar')
      infraDesabilitarCamposAreaDados();
    else
      document.getElementById('btnCancelar').focus();
  }

  function validarCadastro() {
    if (infraTrim(document.getElementById('txtNome').value)=='') {
      alert('Informe o Nome.');
      document.getElementById('txtNome').focus();
      return false;
    }

    if (infraTrim(document.getElementById('txtValor').value)=='') {
      alert('Informe o Valor.');
      document.getElementById('txtValor').focus();
      return false;
    }
    
    return true;
  }

  function OnSubmitForm() {
    return validarCadastro();
  }
</script>