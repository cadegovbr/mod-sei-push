<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	SessaoSEIExterna::getInstance()->validarSessao();

	if (!isset($_POST ['sbmDescadastrarPush']))
		PUSHConverteURI::converterURI();
	
	PUSHUtil::validarLink();
	
	switch($_GET['acao_externa']){
	
		case 'push_autenticar':
			$strTitulo = 'Email autenticado no PUSH com sucesso';

			try {
				$objPushDTO = new PushDTO();
				$objPushDTO->setStrChave($_GET['push_chave']);
				$objPushDTO->retTodos();
				$objPushRN = new PushRN();
				$objPushDTO = $objPushRN->consultar($objPushDTO);
				$objPushDTO->setDthUltimoEnvioEmail(InfraData::getStrDataHoraAtual());
				$objPushDTO = $objPushRN->alterar($objPushDTO);
				PaginaSEIExterna::getInstance()->setStrMensagem('Email autenticado no PUSH com sucesso.');
				$strLocation = '/sei/modulos/pesquisa' . SessaoSEIExterna::getInstance()->assinarLink(SessaoSEIExterna::getInstance()->assinarLink('/md_pesq_processo_pesquisar.php?acao_externa=protocolo_pesquisar&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo=0'));
				alert("Email autenticado no PUSH com sucesso.", $strLocation);
				die();
			} catch(Exception $e){
				PaginaSEI::getInstance()->processarExcecao($e);
			}
			break;
		default:
			throw new InfraException("Ação '".$_GET['acao_externa']."' não reconhecida.");
	}
	
} catch(Exception $e){
	PaginaSEIExterna::getInstance()->processarExcecao($e );
}
function alert($msg, $strLocation) {
	echo '<script>window.alert("'. $msg .'"); window.location.href = location.origin + "'.$strLocation.'";</script>';
}
PaginaSEIExterna::getInstance()->montarDocType();
PaginaSEIExterna::getInstance()->abrirHtml();
PaginaSEIExterna::getInstance()->abrirHead();
PaginaSEIExterna::getInstance()->montarMeta();
PaginaSEIExterna::getInstance()->montarTitle(':: ' . PaginaSEIExterna::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::' );
PaginaSEIExterna::getInstance()->montarStyle();
PaginaSEIExterna::getInstance()->abrirStyle();
?>
#sbmAssinar {position:absolute;left:43%;top:250%;width:10%;font-size: 1.2em}
label.infraLabelOpcional,
label.infraLabelObrigatorio,
label.infraLabelCheckbox,
label.infraLabelRadio{
color:black;
}
input.infraButton, button.infraButton{
border-color: #666 #666 #666 #666;
color:black;
}
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
	return true;
}

<?
PaginaSEIExterna::getInstance()->fecharJavaScript();
PaginaSEIExterna::getInstance()->fecharHead();
PaginaSEIExterna::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"' );
?>
<?  
PaginaSEIExterna::getInstance()->fecharBody();
PaginaSEIExterna::getInstance()->fecharHtml();
?>