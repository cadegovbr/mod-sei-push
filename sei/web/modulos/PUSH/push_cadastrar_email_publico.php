<?
try {
	require_once dirname(__FILE__).'/../../SEI.php';

    SessaoSEIExterna::getInstance()->validarSessao();
	
	$objPushDTO = new PushDTO();
	
	$objInfraParametro = new InfraParametro(BancoSEI::getInstance());
	$strTextoAceiteTermosInclusao = $objInfraParametro->getValor('SEI_PUSH_MENSAGEM_ACEITE_TERMOS_INCLUSAO');
	
	switch($_GET['acao']){
	
		case 'push_cadastrar':
			$strTitulo = 'Incluir no PUSH';

			$objProtocoloDTO = new ProtocoloDTO();
            $objProtocoloDTO->retTodos();
            $objProtocoloDTO->setDblIdProtocolo($_GET['id_procedimento']);
            $objProtocoloRN = new ProtocoloRN();
            $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

			$objPushDTO->setDblIdProcedimento($_POST['hdnIdProcedimento']);
			$objPushDTO->setStrNome($_POST['txtNome']);
			$objPushDTO->setStrEmail($_POST['txtEmail']);
			
			if(isset($_POST['sbmCadastrarPush'])){
				try {
					$objPushRN = new PushRN();
                    if ($objPushRN->deveBloquearNotificacao($_GET['id_procedimento']))
                        alert("Não foi possível incluir o processo no PUSH pois está bloqueado para notificação.");
                    else {
                        $objPushDTO = $objPushRN->cadastrarPublico($objPushDTO);
                        alert("Registro incluído no PUSH. Um e-mail será enviado para o endereço informado, com o andamento do processo selecionado.");
                    }
				} catch(Exception $e){
                    PaginaSEIExterna::getInstance()->processarExcecao($e);
				}
			}
			break;
		default:
			throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
	}
}
catch(Exception $e){
	PaginaSEIExterna::getInstance()->processarExcecao($e);
}

function alert($msg) {
	echo '<script>window.alert("'. $msg .'");</script>';
}
PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: ' . PaginaSEIExterna::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEIExterna::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
?>
    label {display:block}
    input.infraText, select.infraSelect, input.infraCheckbox, textarea.infraTextarea {display:block;}
    input.infraText, select.infraSelect, textarea.infraTextarea {width:95%;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;}
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
    
<?
PaginaSEIExterna::getInstance()->fecharStyle();
PaginaSEIExterna::getInstance()->montarJavaScript();
PaginaSEIExterna::getInstance()->abrirJavaScript();
?>

function inicializar(){
	infraOcultarMenuSistemaEsquema();

	infraProcessarResize();
}

function onSubmitForm(){
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
	var aceitarTermos = document.getElementById('chkAceitarTermos').checked;
	if(!aceitarTermos){
		alert('Por favor, selecione a opção "Li e aceito os termos acima"');
		return false;
	}
	return true;
}

<?
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
PaginaSEIExterna::getInstance()->montarBarraComandosSuperior(array());
PaginaSEIExterna::getInstance()->montarAreaTabela($strResultado,$numProtocolos);
?>
<div style="width: 100%;">
    <form id="sbmCadastrarPush" name="sbmCadastrarPush" method="post" onsubmit="return onSubmitForm();" action="<?=$_SERVER['REQUEST_URI']?>">
    	<div id="divGeral" class="infraAreaDados">
            <div class="campos-form">
                <div class="campos-container">
                    <?=PushFormINT::insereCampoTextoSimples('NumeroProtocoloFormatado', 'Processo', $objProtocoloDTO->getStrProtocoloFormatado(), 50, true, '', '','',true)?>
                    <?=FormINT::insereEspacador(); ?>
                </div>
                <br style="clear: both;" />
                <div class="campos-container">
                    <?=PushFormINT::insereCampoTextoSimples('Nome', 'Nome', '', 50, true)?>
                    <?=FormINT::insereEspacador(); ?>
                </div>
                <br style="clear: both;" />
                <div class="campos-container">
                    <?=PushFormINT::insereCampoTextoSimples('Email', 'E-mail', '', 100, true)?>
                    <?=FormINT::insereEspacador(); ?>
                </div>
                <?=PushFormINT::insereCampoEscondidoSimples('IdProcedimento', $_GET['id_procedimento'])?>
                <br style="clear: both;" />
                <div class="texto-explicativo-container">
                    Pelo sistema PUSH é possível receber, por e-mail, informações sobre o andamento
                    de processos que tramitam no CRSFN.
        		</div>
        		<br style="clear: both;" />
                <div class="texto-explicativo-container">
                	A mensagens do PUSH são enviadas (*uma vez ao dia*) sempre que os processos
                    selecionados receberem um ou mais andamentos.
                </div>
                <br style="clear: both;" />
                <div class="texto-explicativo-container">
                	Certifique-se de que a sua caixa de correio eletrônico esteja apta a receber
                    mensagens do Push. Caixa de correio cheia, bloqueios antispan, et. Podem impedir o
                    recebimento das mensagens.
        		</div>
    		</div>
            <div class="termo-aceite">
                <input type="checkbox" id="chkAceitarTermos" name="chkAceitarTermos"/><label id="lblAceitarTermos" for="chkAceitarTermos" class="termo-aceite">Li e aceito os termos acima.</label>
            </div>
            <div class="botao-container">
                    <input type="submit" id="sbmAssinar" name="sbmCadastrarPush" value="Incluir" class="infraButton" />
            </div>
    	</div>
    </form>
</div>
<?  
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>