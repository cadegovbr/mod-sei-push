<?
class PUSHUtil {
	
	public static function prepararUrl($url) {
		$pos = strpos($url, 'controlador.php');
		if($pos !== false)
			$url = ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL') . substr($url, $pos);
		else {
			$pos = strpos($url, '/publicacoes/controlador_publicacoes.php');
			if($pos !== false)
				$url = ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL') . substr($url, $pos);
		}
		
		if(ConfiguracaoSEI::getInstance()->getValor('SessaoSEI', 'https'))
			$url = str_replace('http://', 'https://', $url);
		
		return $url;
	}
	
	public static function validarLink($strLink = null) {
		if($strLink == null)
			$strLink = $_SERVER ['REQUEST_URI'];
		
		$strLink = urldecode($strLink);
		
		if(trim($strLink == ''))
			return;
		
		$arrParametros =(array(
				'id_orgao_acesso_externo',
				'id_procedimento',
				'id_documento' 
		));
		
		foreach($arrParametros as $strParametros){
			if(isset($_GET [$strParametros])) {
				if(trim($_GET [$strParametros])== '')
					throw new InfraException('Link externo inválido.');
				if(!is_numeric($_GET [$strParametros]))
					throw new InfraException('Link externo inválido.');
			}
		}
		
		$arrScriptFileName =(array(
				'processo_exibir.php',
				'documento_consulta_externa.php' 
		));
		
		if(in_array(basename($_SERVER ['SCRIPT_FILENAME']), $arrScriptFileName)) {
			if(!isset($_GET ['acao_externa'])|| trim($_GET ['acao_externa'])== '')
				throw new InfraException('Link externo inválido.');
			
			if(!isset($_GET ['id_orgao_acesso_externo']))
				throw new InfraException('Link externo inválido.');
		}
		
		if(basename($_SERVER ['SCRIPT_FILENAME'])== 'controlador_ajax_externo.php') {
			if(!isset($_GET ['acao_ajax_externo'])|| trim($_GET ['acao_ajax_externo'])== '')
				throw new InfraException('Link externo inválido.');
			
			$arrAcaoAjaxExterno =(array(
					'contato_auto_completar_contexto_pesquisa',
					'unidade_auto_completar_todas' 
			));
			
			if(!in_array($_GET ['acao_ajax_externo'], $arrAcaoAjaxExterno))
				throw new InfraException('Link externo inválido.');
		}
	}
	
	public static function montarRementente($strRemetente, $strSiglaSistema) {
	    $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
	    
	    $strRemetente = str_replace('@sigla_sistema@', $strSiglaSistema, $strRemetente);
	    $strRemetente = str_replace('@email_sistema@', $objInfraParametro->getValor('SEI_EMAIL_SISTEMA'), $strRemetente);
	    
	    return $strRemetente;
	}
}
?>