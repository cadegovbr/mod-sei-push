<?
require_once dirname(__FILE__).'/../../../SEI.php';

class PushNotificacaoINT extends InfraINT {

  public static function montarSelectNome($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $objEmailSistemaDTO = new EmailSistemaDTO();
    $objEmailSistemaDTO->retNumIdEmailSistema();
    $objEmailSistemaDTO->retStrDescricao();

    $objEmailSistemaDTO->setOrdStrDescricao(InfraDTO::$TIPO_ORDENACAO_ASC);

    $objEmailSistemaRN = new EmailSistemaRN();
    $arrObjEmailSistemaDTO = $objEmailSistemaRN->listar($objEmailSistemaDTO);

    return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjEmailSistemaDTO, 'IdEmailSistema', 'Descricao');
  }
  
  public static function montarArrayTarefas($numIdNotificacao){
  	$objPushNotificacaoDTO = new PushNotificacaoDTO();
  	$objPushNotificacaoDTO->retTodos(true);
  	$objPushNotificacaoDTO->setNumIdNotificacao($numIdNotificacao);
  
  	$objPushNotificacaoRN = new PushNotificacaoRN();
  	$arrObjPushNotificacaoDTO = $objPushNotificacaoRN->listar($objPushNotificacaoDTO);
  
  	$arrObjTarefas = array();
  
  	for($i = 0; $i < count($arrObjPushNotificacaoDTO); $i++) {
  		$objTarefaDTO = new TarefaDTO();
  		$objTarefaDTO->retNumIdTarefa();
  		$objTarefaDTO->retStrNome();
  		$objTarefaDTO->setNumIdTarefa($arrObjPushNotificacaoDTO[$i]->getNumIdTarefa());
  		$objTarefaRN = new TarefaRN();
  		$objTarefaDTO = $objTarefaRN->consultar($objTarefaDTO);
  		$arrObjTarefas[$i] = $objTarefaDTO;
  	}
  
  	return parent::montarSelectArrInfraDTO(null, null, null, $arrObjTarefas, 'IdTarefa','Nome');
  }
  
  public static function autoCompletarTarefas($strPalavrasPesquisa,$strTipoHistorico){
  	$objPushNotificacaoDTO = new PushNotificacaoDTO();
  	$objPushNotificacaoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
  
  	$objPushNotificacaoRN = new PushNotificacaoRN();
  	$arrObjTarefasDTO = $objPushNotificacaoRN->pesquisarTarefasAjax(array($objPushNotificacaoDTO,$strTipoHistorico));
  
  	return array_values($arrObjTarefasDTO);
  }

  public static function autoCompletarTipoProcedimento($strPalavrasPesquisa){
    $objPushNotificacaoDTO = new PushNotificacaoDTO();
    $objPushNotificacaoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);
    $objPushNotificacaoRN = new PushNotificacaoRN();
    $arrObjTipoProcedimentoDTO = $objPushNotificacaoRN->pesquisarTiposProcedimentoAjax(array($objPushNotificacaoDTO));
    return array_values($arrObjTipoProcedimentoDTO);
  }

  public static function montarSelectBinario($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado){
    $arrValores = array('S' => 'Sim', 'N' => 'Não');

    return parent::montarSelectArray($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrValores);
  }
}
?>