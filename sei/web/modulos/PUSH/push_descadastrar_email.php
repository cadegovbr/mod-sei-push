<?
try {
	require_once dirname(__FILE__). '/../../SEI.php';
	
	SessaoSEIExterna::getInstance()->validarSessao();
	
	if (!isset($_POST ['sbmDescadastrarPush']))
		PUSHConverteURI::converterURI();
	
	PUSHUtil::validarLink();
	
	$objPushDTO = new PushDTO();
	$objPushRN = new PushRN();
	$objEmailSistemaRN = new EmailSistemaRN();

	switch($_GET['acao_externa']){
	
		case 'push_descadastrar':
			$strTitulo = 'Registro excluído do PUSH com sucesso';
				
			$objPushDTO->setStrChave($_GET['push_chave']);
			$objPushDTO->retTodos();
			$objPushDTO->retStrNumeroProtocoloFormatado();
			$objPushExcluidoDTO = $objPushRN->consultar($objPushDTO);
				
			try {
				$objPushDTO = $objPushRN->excluir(array($objPushDTO));
				PaginaSEIExterna::getInstance()->setStrMensagem('Os dados cadastrados foram excluídos com sucesso.');
				$strLocation = '/sei/modulos/pesquisa' . SessaoSEIExterna::getInstance()->assinarLink(SessaoSEIExterna::getInstance()->assinarLink('/md_pesq_processo_pesquisar.php?acao_externa=protocolo_pesquisar&acao_origem_externa=protocolo_pesquisar&id_orgao_acesso_externo=0'));

				$objPushConfiguracaoDTO = new PushConfiguracaoDTO();
		        $objPushConfiguracaoDTO->setStrNome('ID_TEMPLATE_DESCADASTRAMENTO_PROCESSO_PUSH');
		        $objPushConfiguracaoDTO->retTodos();
		        $objPushConfiguracaoRN = new PushConfiguracaoRN();
		        $objPushConfiguracaoDTO = $objPushConfiguracaoRN->consultar($objPushConfiguracaoDTO);
		        if($objPushConfiguracaoDTO != null){
		        	$objEmailSistemaDTO = new EmailSistemaDTO();
					$objEmailSistemaDTO->setNumIdEmailSistema($objPushConfiguracaoDTO->getStrValor());
					$objEmailSistemaDTO->retTodos();
					$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

					$strAssunto = $objPushRN->substituirVariaveisEmail($objEmailSistemaDTO->getStrAssunto(),$objPushExcluidoDTO);
	                $strDe = PUSHUtil::montarRementente($objEmailSistemaDTO->getStrDe(), '');
	                $strPara = $objPushRN->substituirVariaveisEmail($objEmailSistemaDTO->getStrPara(),$objPushExcluidoDTO);
	                $strCorpo = $objPushRN->substituirVariaveisEmail($objEmailSistemaDTO->getStrConteudo(),$objPushExcluidoDTO);

	                $objEmailDTO = new EmailDTO();
	                $objEmailDTO->setStrDe($strDe);
	                $objEmailDTO->setStrPara($strPara);
	                $objEmailDTO->setStrAssunto($strAssunto);
	                $objEmailDTO->setStrMensagem($strCorpo);
	                EmailRN::processar(array($objEmailDTO));
		        }

				alert("Registro excluído do PUSH com sucesso.", $strLocation);
				die();
			} catch(Exception $e){
				PaginaSEI::getInstance()->processarExcecao($e);
			}
			break;
		default:
			throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
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