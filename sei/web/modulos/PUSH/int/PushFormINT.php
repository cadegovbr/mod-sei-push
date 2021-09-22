<?

require_once dirname(__FILE__).'/../../../SEI.php';

class PushFormINT extends InfraINT {
  
  public static function insereLabel ($prefixoTipoCampo, $nomeCampo, $label, $obrigatorio, $style='') {
    return '<label id="lbl' . $nomeCampo . '" for="' . $prefixoTipoCampo . $nomeCampo . '" class="infraLabel' . ($obrigatorio ? 'Obrigatorio' : 'Opcional') . '" style='.$style.'>' . $label . ':</label>';
  }
  
  public static function insereValor ($valor, $obrigatorio, $style='') {
  	return '<label class="infraLabel' . ($obrigatorio ? 'Obrigatorio' : 'Opcional') . '" style='.$style.'>' . $valor . '</label>';
  }
  
  public static function insereEspacador () {
    return '<br clear="both" /><br clear="both" />';
  }

  public static function insereCampoEscondidoSimples ($nomeCampo, $valorPadrao, $classe='') {
    $ret = '<input type="hidden" id="hdn' . $nomeCampo . '" name="hdn' . $nomeCampo . '" value="' . $valorPadrao . '" class="infraHidden ' . $classe . '"/>';
    return $ret;
  }
  
  public static function insereCampoTextoSimples ($nomeCampo, $label, $valorPadrao, $maxlength, $obrigatorio = false, $style = '', $classe = '', $onchange = '', $readonly=false) {
    $ret = self::insereLabel ('txt', $nomeCampo, $label, $obrigatorio);
    if(!$readonly)
    	$ret .= '<input type="text" id="txt' . $nomeCampo . '" name="txt' . $nomeCampo . '" class="infraText ' . $classe . '" onchange="' . $onchange . '" onkeypress="return infraMascaraTexto(this,event,' . $maxlength . ');" maxlength="' . $maxlength . '" value="' . $valorPadrao . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="' . $style . '"/>';
    else
    	$ret .= '<input type="text" id="txt' . $nomeCampo . '" name="txt' . $nomeCampo . '" class="infraText ' . $classe . '" readonly onchange="' . $onchange . '" onkeypress="return infraMascaraTexto(this,event,' . $maxlength . ');" maxlength="' . $maxlength . '" value="' . $valorPadrao . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="' . $style . '"/>';
    return $ret;
  }
  
  public static function insereCampoSelecaoSimples ($nomeCampo, $label, $options, $obrigatorio = false, $style = '', $classe='', $disabled=false, $onchange = '') {
    $ret = self::insereLabel ('sel', $nomeCampo, $label, $obrigatorio);
    if($disabled)
    	$ret .= '<select id="sel' . $nomeCampo . '" disabled name="sel' . $nomeCampo . '" class="infraSelect ' . $classe . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="' . $style . ' onchange="' . $onchange .'" >';
    else
    	$ret .= '<select id="sel' . $nomeCampo . '" name="sel' . $nomeCampo . '" class="infraSelect ' . $classe . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="' . $style . '" onchange="' . $onchange .'">';
    $ret .= $options;
    $ret .= '</select>';
    return $ret;
  }
  
  public static function insereCampoSelecaoSimplesTeste ($nomeCampo, $label, $options, $obrigatorio = false, $style = '') {
    $ret = self::insereLabel ('sel', $nomeCampo, $label, $obrigatorio);
    $ret .= '<select id="sel' . $nomeCampo . '" name="sel' . $nomeCampo . '" class="infraSelect" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="' . $style . '" >';
    $ret .= $options;
    $ret .= '</select>';
    return $ret;
  }
  
  public static function insereCampoDataSimples ($nomeCampo, $label, $valorPadrao, $obrigatorio = false) {
    $ret = self::insereLabel ('txt', $nomeCampo, $label, $obrigatorio);
    $ret .= '<div style="display:block">';
    $ret .= '<input type="text" id="txt' . $nomeCampo . '" name="txt' . $nomeCampo . '" class="infraText"  onkeypress="return infraMascaraData(this, event)" value="' . $valorPadrao . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="display: inline-block; width: 85px;"/>';
    $ret .= '<img id="img' . $nomeCampo . '" src="/infra_css/imagens/calendario.gif" alt="Selecionar Data" onclick="infraCalendario(\'txt' . $nomeCampo . '\',this);" title="Selecionar Data" class="infraImg" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '" style="margin: 0 5px; vertical-align: sub;" />';
    $ret .= '</div>';
    return $ret;
  }
  
  public static function insereCampoCheckSimples ($nomeCampo, $label, $valorPadrao, $onclick='') {
    $ret = self::insereLabel ('chk', $nomeCampo, $label, false);
    $ret .= '<input type="checkbox" id="chk' . $nomeCampo . '" name="chk' . $nomeCampo . '" class="infraCheckbox" ' . PaginaSEI::getInstance()->setCheckbox($valorPadrao) . ' onclick="'. $onclick .'" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"/>';
    return $ret;
  }
  
  public static function insereCampoAreaDeTextoSimples ($nomeCampo, $label, $valorPadrao, $qtdLinhas, $obrigatorio = false, $style='font-family: Courier, \'Courier New\', monospace') {
    $ret = self::insereLabel ('txa', $nomeCampo, $label, $obrigatorio);
    $ret .= '<textarea id="txa' . $nomeCampo . '" name="txa' . $nomeCampo . '" rows="' . $qtdLinhas . '"  class="infraTextarea" style="' . $style . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '">';
    $ret .= $valorPadrao;
    $ret .= '</textarea>';
    return $ret;
  }
  
  public static function insereCampoCheckComCamposDependentes ($nomeCampo, $label, $valorPadrao, $camposInternos = array()) {
  	$ret = self::insereLabel ('chk', $nomeCampo, $label, false);
  	$ret .= '<input type="checkbox" id="chk' . $nomeCampo . '" name="chk' . $nomeCampo . '" class="infraCheckbox" onchange="exibeCamposDependentes' . $nomeCampo . '(this.checked)" ' . PaginaSEI::getInstance()->setCheckbox($valorPadrao) . ' tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"/>';
    $ret .= '<br style="clear:both"/>';
    $ret .= '<div id="div' . $nomeCampo . '" style="display:none">';
    foreach($camposInternos as $campoInterno)
    	$ret .= $campoInterno;
    $ret .= '</div>';
    $ret .= '<script>';
    $ret .= self::insereFuncaoExibicaoCamposDependentes($nomeCampo);
    $ret .= 'exibeCamposDependentes' . $nomeCampo . '(' . ($valorPadrao == 'S' ? 'true' : 'false') . ');';
    $ret .= '</script>';
    
    return $ret;
  }
  
  private static function insereFuncaoExibicaoCamposDependentes($nomeCampo){
    return 'function exibeCamposDependentes' . $nomeCampo . '(checked) {
              if (checked)
                document.getElementById("div' . $nomeCampo . '").style.display = \'block\';
              else if (!checked) {
                document.getElementById("div' . $nomeCampo . '").style.display = \'none\';
      
                var camposTexto = document.getElementById("div' . $nomeCampo . '").getElementsByTagName("input");
                for (var i = 0; i < camposTexto.length; i++) {
					if(camposTexto[i].type=="checkbox")
						camposTexto[i].checked = false;
					else
						camposTexto[i].value = ""
				}
          
                var camposCaixaTexto = document.getElementById("div' . $nomeCampo . '").getElementsByTagName("textarea");
                for (var i = 0; i < camposCaixaTexto.length; i++) {
                	camposCaixaTexto[i].value = ""
  				}
                
                var camposSelect = document.getElementById("div' . $nomeCampo . '").getElementsByTagName("select");
                for (var i = 0; i < camposSelect.length; i++)
                  camposSelect[i].selectedIndex = 0;
              }
            };';
  }
  
  public static function montarSelectBinario($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
  	$arrValores = array('S' => 'Sim', 'N' => 'Não');
  
  	return parent::montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrValores);
  }

  public static function sinFormularioPushMostrarBotao($idProcedimento){
    //Obtem Mensagem de Aceitação
    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
    $strTextoAceiteTermosInclusao = $objInfraParametro->getValor('SEI_PUSH_MENSAGEM_ACEITE_TERMOS_INCLUSAO');

    $objPushRN = new PushRN();
    return $objPushRN->sinPermitidaAssinatura($idProcedimento) && $strTextoAceiteTermosInclusao != '';
  }

  public static function formularioPushPesquisa($idProcedimento){
      $strFormulario = '';

      //Obtem Mensagem de Aceitação
      $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
      $strTextoAceiteTermosInclusao = $objInfraParametro->getValor('SEI_PUSH_MENSAGEM_ACEITE_TERMOS_INCLUSAO');

      $objPushRN = new PushRN();
      
      if($objPushRN->sinPermitidaAssinatura($idProcedimento) && $strTextoAceiteTermosInclusao != ''){

        $objPushFormINT = new PushFormINT();
        $strFormulario .= $objPushFormINT->insereLabel('', '', 'Cadastrar E-mail PUSH', true, '');
        $strFormulario .= "<style>
          label {display:block}
        input.infraText, select.infraSelect, input.infraCheckbox, textarea.infraTextarea {display:block;}
        input.infraText, textarea.infraTextarea {width:95%;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;}
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
        </style>
        <script>
          function SubmitForm(){
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

            if(!document.getElementById('chkAceitarTermos').checked){
              alert('É necessário concordar com o Termo de Aceitação');
              return false;
            }

            return true;
          }
        </script>";

        $strFormulario .= '<form id="sbmCadastrarPush" name="sbmCadastrarPush" method="post" onsubmit="return SubmitForm();">
          <div id="divGeral" class="infraAreaDados">
                <div class="campos-form">
                    <div class="campos-container">';

                        $strFormulario .= PushFormINT::insereCampoTextoSimples('Nome', 'Nome', '', 50, true);
                        $strFormulario .= PushFormINT::insereEspacador();
                    $strFormulario .= '</div>
                    
                    <br style="clear: both;" />
                    
                    <div class="campos-container">';
                        $strFormulario .= PushFormINT::insereCampoTextoSimples('Email', 'E-mail', '', 100, true);
                        $strFormulario .= PushFormINT::insereEspacador();
                    $strFormulario .= '</div>
                </div>
                <div class="termo-aceitacao">';
              $strFormulario .= $strTextoAceiteTermosInclusao;
                    $strFormulario .= '<div class="botoes">
                      <div class="termo-aceite"><input type="checkbox" id="chkAceitarTermos" name="chkAceitarTermos"/><label id="lblAceitarTermos" for="chkAceitarTermos" class="termo-aceite">Li e aceito os termos acima.</label></div>
                      <div class="botao-container"><input type="submit" id="sbmAssinar" name="sbmCadastrarPush" value="Incluir" class="infraButton" /></div>
                </div>
            </div>
          </div>
        </form>';
      }
    return $strFormulario;
  }
}
?>